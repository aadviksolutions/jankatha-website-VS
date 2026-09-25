<?php

namespace App\Services;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsFetchLog;
use App\Models\NewsSource;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsFetchService
{
    /**
     * Known Chhattisgarh districts for automatic location detection.
     */
    protected const CHHATTISGARH_DISTRICTS = [
        'Raipur', 'Bilaspur', 'Durg', 'Bhilai', 'Korba', 'Bastar', 'Jagdalpur',
        'Rajnandgaon', 'Ambikapur', 'Raigarh', 'Dhamtari', 'Mahasamund', 'Kanker',
        'Janjgir-Champa', 'Kawardha', 'Kabirdham', 'Balod', 'Bemetara', 'Gariaband',
        'Baloda Bazar', 'Mungeli', 'Surajpur', 'Balrampur', 'Jashpur', 'Surguja',
        'Sukma', 'Bijapur', 'Dantewada', 'Narayanpur', 'Kondagaon',
        'Gaurela-Pendra-Marwahi', 'Khairagarh', 'Mohla-Manpur', 'Sarangarh-Bilaigarh',
        'Sakti', 'Manendragarh',
    ];

    public function __construct(
        protected NewsAiProcessorService $aiProcessor = new NewsAiProcessorService
    ) {}

    /**
     * Fetch all active sources or a specific source.
     *
     * @return array{
     *     sources_processed: int,
     *     items_found: int,
     *     items_imported: int,
     *     items_skipped_duplicate: int,
     *     errors: array<int, string>
     * }
     */
    public function fetchActiveSources(?int $sourceId = null, bool $force = false): array
    {
        $query = NewsSource::query()->where('is_active', true)->orderByDesc('priority');

        if ($sourceId) {
            $query->where('id', $sourceId);
        }

        $sources = $query->get();

        $stats = [
            'sources_processed' => 0,
            'items_found' => 0,
            'items_imported' => 0,
            'items_skipped_duplicate' => 0,
            'errors' => [],
        ];

        foreach ($sources as $source) {
            if (! $force && ! $this->shouldFetchSource($source)) {
                continue;
            }

            $sourceStats = $this->fetchSingleSource($source);
            $stats['sources_processed']++;
            $stats['items_found'] += $sourceStats['items_found'];
            $stats['items_imported'] += $sourceStats['items_imported'];
            $stats['items_skipped_duplicate'] += $sourceStats['items_skipped_duplicate'];

            if ($sourceStats['error']) {
                $stats['errors'][$source->id] = "Source [{$source->name}]: {$sourceStats['error']}";
            }
        }

        return $stats;
    }

    /**
     * Check if enough time has passed based on source frequency.
     */
    protected function shouldFetchSource(NewsSource $source): bool
    {
        if (! $source->last_fetched_at) {
            return true;
        }

        $minutes = max(1, (int) $source->fetch_frequency_minutes);

        return $source->last_fetched_at->addMinutes($minutes)->isPast();
    }

    /**
     * Fetch news from a single configured source.
     *
     * @return array{
     *     items_found: int,
     *     items_imported: int,
     *     items_skipped_duplicate: int,
     *     error: ?string
     * }
     */
    public function fetchSingleSource(NewsSource $source): array
    {
        $startTime = microtime(true);
        $result = [
            'items_found' => 0,
            'items_imported' => 0,
            'items_skipped_duplicate' => 0,
            'error' => null,
        ];

        // Distributed lock to prevent duplicate concurrent fetch
        $lockKey = 'news_fetch_source_'.$source->id;
        $lock = Cache::lock($lockKey, 120);

        if (! $lock->get()) {
            return array_merge($result, ['error' => 'Source fetch is currently in progress.']);
        }

        $httpStatus = null;

        try {
            // Validate feed URL against SSRF
            $this->validateSafeUrl($source->feed_url);

            // Fetch HTTP response
            $response = Http::withHeaders([
                'User-Agent' => 'JankathaNewsBot/1.0 (+https://jankatha.com; newsroom@jankatha.com)',
                'Accept' => 'application/rss+xml, application/xml, application/json, text/xml, */*',
            ])->timeout(15)->get($source->feed_url);

            $httpStatus = $response->status();

            if (! $response->successful()) {
                throw new \RuntimeException("Feed request failed with HTTP {$httpStatus}");
            }

            $body = (string) $response->body();
            if (empty(trim($body))) {
                throw new \RuntimeException('Feed response body was empty');
            }

            // Parse items based on source type
            $items = $source->source_type === 'json_api'
                ? $this->parseJsonFeed($body)
                : $this->parseRssFeed($body);

            $result['items_found'] = count($items);

            // Fetch system settings
            $maxPerFetch = (int) Setting::where('key', 'auto_news_max_items_per_fetch')->value('value') ?: 20;
            $autoPublish = (string) Setting::where('key', 'auto_publish_enabled')->value('value') === '1';
            $aiEnabled = (string) Setting::where('key', 'auto_news_ai_processing')->value('value') === '1';

            $imported = 0;
            $skipped = 0;

            foreach (array_slice($items, 0, $maxPerFetch) as $item) {
                $processResult = $this->processAndStoreItem($item, $source, $autoPublish, $aiEnabled);
                if ($processResult === 'imported') {
                    $imported++;
                } elseif ($processResult === 'duplicate') {
                    $skipped++;
                }
            }

            $result['items_imported'] = $imported;
            $result['items_skipped_duplicate'] = $skipped;

            $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            // Update source
            $source->update([
                'last_fetched_at' => now(),
                'last_error' => null,
                'items_fetched_count' => $source->items_fetched_count + $imported,
            ]);

            // Create success fetch log
            NewsFetchLog::create([
                'news_source_id' => $source->id,
                'status' => 'success',
                'items_found' => $result['items_found'],
                'items_imported' => $imported,
                'items_skipped_duplicate' => $skipped,
                'http_status' => $httpStatus,
                'error_message' => null,
                'execution_time_ms' => $executionTimeMs,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $errorMessage = Str::limit($e->getMessage(), 1000);
            $result['error'] = $errorMessage;

            Log::error("Failed to fetch news source [{$source->id}] {$source->name}: {$errorMessage}");

            $source->update([
                'last_fetched_at' => now(),
                'last_error' => $errorMessage,
            ]);

            NewsFetchLog::create([
                'news_source_id' => $source->id,
                'status' => 'failed',
                'items_found' => 0,
                'items_imported' => 0,
                'items_skipped_duplicate' => 0,
                'http_status' => $httpStatus,
                'error_message' => $errorMessage,
                'execution_time_ms' => $executionTimeMs,
                'created_at' => now(),
            ]);
        } finally {
            $lock->release();
        }

        return $result;
    }

    /**
     * Process an individual feed item, detect duplicates, classify, and save.
     */
    protected function processAndStoreItem(array $rawItem, NewsSource $source, bool $autoPublish, bool $aiEnabled): string
    {
        $headline = $this->cleanText($rawItem['headline'] ?? '');
        if (mb_strlen($headline) < 5) {
            return 'invalid';
        }

        $sourceUrl = ! empty($rawItem['source_url']) ? trim($rawItem['source_url']) : null;
        $guid = ! empty($rawItem['guid']) ? trim((string) $rawItem['guid']) : ($sourceUrl ?: null);

        // Normalize headline for content hash deduplication
        $normalizedHeadline = Str::lower(preg_replace('/\s+/', ' ', trim($headline)));
        $contentHash = hash('sha256', $normalizedHeadline);

        // Deduplication check
        if ($this->isDuplicate($guid, $sourceUrl, $contentHash)) {
            return 'duplicate';
        }

        $rawContent = $rawItem['content'] ?? ($rawItem['summary'] ?? $headline);
        $summary = ! empty($rawItem['summary']) ? $this->cleanText($rawItem['summary']) : Str::limit(strip_tags($rawContent), 280);
        $content = $this->sanitizeContent($rawContent);

        // Resolve published_at
        $publishedAt = null;
        if (! empty($rawItem['published_at'])) {
            try {
                $publishedAt = Carbon::parse($rawItem['published_at']);
            } catch (\Throwable) {
                $publishedAt = now();
            }
        } else {
            $publishedAt = now();
        }

        // Detect Location
        $locationData = $this->detectLocation($headline, $summary, $source);

        // Detect or match Category
        $categoryId = $this->resolveCategory($rawItem['category'] ?? null, $headline, $summary, $source);

        // AI processing if enabled
        $itemData = [
            'headline' => $headline,
            'summary' => $summary,
            'content' => $content,
            'category' => (string) $categoryId,
            'district' => $locationData['district'],
        ];

        $tags = null;
        if ($aiEnabled && $this->aiProcessor->isConfigured()) {
            $processed = $this->aiProcessor->process($itemData);
            $headline = $processed['headline'] ?: $headline;
            $summary = $processed['summary'] ?: $summary;
            $tags = $processed['tags'] ?? null;
        }

        // Determine publication status
        // Default is AUTO PUBLISH = OFF -> goes to 'pending_review'
        $status = 'pending_review';
        $finalPublishedAt = null;

        if ($autoPublish) {
            // Apply validation rules before auto-publishing
            if (mb_strlen($headline) >= 10 && mb_strlen(strip_tags($content)) >= 25 && $categoryId) {
                $status = 'published';
                $finalPublishedAt = $publishedAt;
            }
        }

        // Generate unique slug
        $baseSlug = Str::slug($headline);
        if (empty($baseSlug)) {
            $baseSlug = 'news-'.Str::random(8);
        }
        $slug = $baseSlug.'-'.Str::random(6);

        // Featured Image
        $imageUrl = ! empty($rawItem['image_url']) ? $this->validateImageUrl($rawItem['image_url']) : null;

        News::create([
            'category_id' => $categoryId,
            'author_id' => null, // null author indicates automated ingestion
            'source_id' => $source->id,
            'source_name' => $source->name,
            'source_url' => $sourceUrl,
            'source_guid' => $guid ? Str::limit($guid, 190, '') : null,
            'content_hash' => $contentHash,
            'is_auto_fetched' => true,
            'headline' => Str::limit($headline, 255),
            'slug' => $slug,
            'short_description' => Str::limit($summary, 500),
            'content' => $content,
            'featured_image' => $imageUrl,
            'state' => $locationData['state'],
            'district' => $locationData['district'],
            'city' => $locationData['city'],
            'location' => $locationData['location'],
            'tags' => $tags,
            'status' => $status,
            'is_breaking' => false,
            'is_featured' => false,
            'published_at' => $finalPublishedAt,
            'seo_title' => Str::limit($headline, 65).' | Jankatha',
            'seo_description' => Str::limit(strip_tags($summary), 160),
            'attribution_text' => $source->attribution_text ?: ('Source: '.$source->name),
        ]);

        return 'imported';
    }

    /**
     * Check if an article already exists by GUID, source URL, or content hash.
     */
    protected function isDuplicate(?string $guid, ?string $sourceUrl, string $contentHash): bool
    {
        return News::query()
            ->where(function ($query) use ($guid, $sourceUrl, $contentHash): void {
                if ($guid) {
                    $query->orWhere('source_guid', $guid);
                }
                if ($sourceUrl) {
                    $query->orWhere('source_url', $sourceUrl);
                }
                $query->orWhere('content_hash', $contentHash);
            })
            ->exists();
    }

    /**
     * Parse RSS 2.0 or Atom feeds safely.
     *
     * @return array<int, array{
     *     headline: string,
     *     summary: ?string,
     *     content: string,
     *     source_url: ?string,
     *     guid: ?string,
     *     published_at: ?string,
     *     category: ?string,
     *     image_url: ?string
     * }>
     */
    public function parseRssFeed(string $xmlContent): array
    {
        $backupEntityLoader = libxml_disable_entity_loader(true);
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string(
            $xmlContent,
            'SimpleXMLElement',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA
        );

        libxml_disable_entity_loader($backupEntityLoader);

        if ($xml === false) {
            throw new \RuntimeException('Failed to parse XML RSS feed: invalid XML structure');
        }

        $items = [];

        // Check for RSS 2.0 <channel><item>
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $entry) {
                $items[] = $this->extractRss2Item($entry);
            }
        }
        // Check for Atom <entry>
        elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $items[] = $this->extractAtomItem($entry);
            }
        }
        // Check for root <item>
        elseif (isset($xml->item)) {
            foreach ($xml->item as $entry) {
                $items[] = $this->extractRss2Item($entry);
            }
        }

        return $items;
    }

    /**
     * Extract RSS 2.0 item.
     */
    protected function extractRss2Item(\SimpleXMLElement $entry): array
    {
        $namespaces = $entry->getNamespaces(true);

        $content = '';
        if (isset($namespaces['content'])) {
            $contentEncoded = $entry->children($namespaces['content'])->encoded;
            if ($contentEncoded) {
                $content = (string) $contentEncoded;
            }
        }
        if (empty($content)) {
            $content = (string) ($entry->description ?? '');
        }

        // Image extraction
        $imageUrl = null;
        if (isset($namespaces['media'])) {
            $media = $entry->children($namespaces['media']);
            if (isset($media->content)) {
                $imageUrl = (string) $media->content->attributes()->url;
            } elseif (isset($media->thumbnail)) {
                $imageUrl = (string) $media->thumbnail->attributes()->url;
            }
        }

        if (empty($imageUrl) && isset($entry->enclosure)) {
            $type = (string) $entry->enclosure->attributes()->type;
            if (str_starts_with($type, 'image/') || empty($type)) {
                $imageUrl = (string) $entry->enclosure->attributes()->url;
            }
        }

        if (empty($imageUrl)) {
            $imageUrl = $this->extractImageFromHtml($content);
        }

        $link = (string) ($entry->link ?? '');
        $guid = (string) ($entry->guid ?? $link);

        return [
            'headline' => (string) ($entry->title ?? ''),
            'summary' => (string) ($entry->description ?? ''),
            'content' => $content,
            'source_url' => $link,
            'guid' => $guid,
            'published_at' => (string) ($entry->pubDate ?? $entry->pubdate ?? null),
            'category' => (string) ($entry->category ?? null),
            'image_url' => $imageUrl,
        ];
    }

    /**
     * Extract Atom item.
     */
    protected function extractAtomItem(\SimpleXMLElement $entry): array
    {
        $link = '';
        if (isset($entry->link)) {
            foreach ($entry->link as $linkNode) {
                $rel = (string) $linkNode->attributes()->rel;
                if ($rel === 'alternate' || empty($rel)) {
                    $link = (string) $linkNode->attributes()->href;
                    break;
                }
            }
        }

        $content = (string) ($entry->content ?? $entry->summary ?? '');
        $imageUrl = $this->extractImageFromHtml($content);

        return [
            'headline' => (string) ($entry->title ?? ''),
            'summary' => (string) ($entry->summary ?? ''),
            'content' => $content,
            'source_url' => $link,
            'guid' => (string) ($entry->id ?? $link),
            'published_at' => (string) ($entry->published ?? $entry->updated ?? null),
            'category' => (string) ($entry->category->attributes()->term ?? null),
            'image_url' => $imageUrl,
        ];
    }

    /**
     * Parse JSON feed (NewsAPI, custom JSON endpoints, etc.)
     */
    public function parseJsonFeed(string $jsonContent): array
    {
        $data = json_decode($jsonContent, true);
        if (! is_array($data)) {
            throw new \RuntimeException('Failed to parse JSON feed: invalid JSON string');
        }

        $rawItems = [];
        if (isset($data['articles']) && is_array($data['articles'])) {
            $rawItems = $data['articles'];
        } elseif (isset($data['items']) && is_array($data['items'])) {
            $rawItems = $data['items'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $rawItems = $data['data'];
        } elseif (array_is_list($data)) {
            $rawItems = $data;
        }

        $items = [];
        foreach ($rawItems as $raw) {
            if (! is_array($raw)) {
                continue;
            }

            $headline = $raw['title'] ?? $raw['headline'] ?? $raw['name'] ?? '';
            $link = $raw['url'] ?? $raw['link'] ?? $raw['source_url'] ?? null;
            $content = $raw['content'] ?? $raw['description'] ?? $raw['body'] ?? '';
            $summary = $raw['description'] ?? $raw['summary'] ?? null;
            $imageUrl = $raw['urlToImage'] ?? $raw['image'] ?? $raw['imageUrl'] ?? $raw['image_url'] ?? null;
            $publishedAt = $raw['publishedAt'] ?? $raw['published_at'] ?? $raw['date'] ?? null;
            $category = $raw['category'] ?? null;
            $guid = $raw['id'] ?? $raw['guid'] ?? $link;

            $items[] = [
                'headline' => (string) $headline,
                'summary' => $summary ? (string) $summary : null,
                'content' => (string) $content,
                'source_url' => $link ? (string) $link : null,
                'guid' => $guid ? (string) $guid : null,
                'published_at' => $publishedAt ? (string) $publishedAt : null,
                'category' => $category ? (string) $category : null,
                'image_url' => $imageUrl ? (string) $imageUrl : null,
            ];
        }

        return $items;
    }

    /**
     * Extract first image src from HTML string.
     */
    protected function extractImageFromHtml(string $html): ?string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            $url = $matches[1];
            if (Str::startsWith($url, ['http://', 'https://'])) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Sanitize and strip unsafe tags from article content.
     */
    protected function sanitizeContent(string $html): string
    {
        // Strip dangerous tags completely including their content
        $cleaned = preg_replace('/<(script|style|iframe|object|embed)[^>]*>.*?<\/\\1>/si', '', $html);

        // Allow basic formatting tags
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><h4>';
        $stripped = strip_tags($cleaned, $allowedTags);

        return trim($stripped);
    }

    /**
     * Clean plaintext string from HTML tags and excessive whitespace.
     */
    protected function cleanText(string $text): string
    {
        $clean = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    /**
     * Validate image URL to prevent SSRF and unsafe resources.
     */
    protected function validateImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    /**
     * Resolve category based on source, feed metadata, or content analysis.
     */
    protected function resolveCategory(?string $feedCategory, string $headline, string $summary, NewsSource $source): int
    {
        // 1. If source has an explicit category assigned, use it
        if ($source->category_id) {
            return $source->category_id;
        }

        $categories = Category::query()->where('status', 'active')->get();

        // 2. Match from feed item's category tag
        if (! empty($feedCategory)) {
            $cleanFeedCat = Str::lower(trim($feedCategory));
            $matched = $categories->first(function ($cat) use ($cleanFeedCat) {
                return Str::lower($cat->name) === $cleanFeedCat || $cat->slug === Str::slug($cleanFeedCat);
            });
            if ($matched) {
                return $matched->id;
            }
        }

        // 3. Keyword matching in headline and summary
        $combinedText = Str::lower($headline.' '.$summary);

        $categoryKeywords = [
            'Sports' => ['cricket', 'football', 'hockey', 'match', 'ipl', 'tournament', 'medal', 'sports', 'खेल', 'क्रिकेट', 'मैच'],
            'Politics' => ['bjp', 'congress', 'election', 'minister', 'vote', 'mla', 'mp', 'government', 'rajya sabha', 'lok sabha', 'राजनीति', 'चुनाव', 'मंत्री', 'विधायक'],
            'Crime' => ['police', 'arrest', 'murder', 'theft', 'crime', 'court', 'jail', 'fraud', 'FIR', 'पुलिस', 'गिरफ्तार', 'हत्या', 'अपराध'],
            'Business' => ['market', 'sensex', 'nifty', 'bank', 'economy', 'rupee', 'stock', 'business', 'व्यापार', 'बाजार', 'बैंक', 'अर्थव्यवस्था'],
            'Education' => ['school', 'college', 'exam', 'student', 'result', 'university', 'teacher', 'शिक्षा', 'स्कूल', 'परीक्षा', 'छात्र'],
            'Health' => ['health', 'hospital', 'doctor', 'disease', 'medical', 'vaccine', 'patient', 'स्वास्थ्य', 'अस्पताल', 'डॉक्टर'],
            'Technology' => ['ai', 'mobile', 'smartphone', 'tech', 'software', 'google', 'apple', 'app', 'तकनीक'],
            'Entertainment' => ['movie', 'film', 'actor', 'actress', 'cinema', 'bollywood', 'box office', 'सिनेमा', 'फिल्म', 'मनोरंजन'],
            'Bastar' => ['bastar', 'jagdalpur', 'dantewada', 'sukma', 'kanker', 'बस्तर', 'दंतेवाड़ा', 'सुकमा'],
            'Raipur' => ['raipur', 'रायपुर'],
            'Bilaspur' => ['bilaspur', 'बिलासपुर'],
            'Durg' => ['durg', 'दुर्ग'],
            'Bhilai' => ['bhilai', 'भिलाई'],
            'Korba' => ['korba', 'कोरबा'],
            'Chhattisgarh' => ['chhattisgarh', 'cg', 'छत्तीसगढ़'],
        ];

        foreach ($categoryKeywords as $catName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($combinedText, Str::lower($keyword))) {
                    $matched = $categories->first(fn ($c) => Str::lower($c->name) === Str::lower($catName));
                    if ($matched) {
                        return $matched->id;
                    }
                }
            }
        }

        // 4. System default category setting
        $defaultCatId = Setting::where('key', 'auto_news_default_category_id')->value('value');
        if ($defaultCatId && $categories->contains('id', (int) $defaultCatId)) {
            return (int) $defaultCatId;
        }

        // 5. Fallback to 'Latest News' or first available category
        $latestCat = $categories->first(fn ($c) => in_array($c->slug, ['latest-news', 'chhattisgarh', 'breaking-news']));
        if ($latestCat) {
            return $latestCat->id;
        }

        return $categories->first()?->id ?: 1;
    }

    /**
     * Detect Chhattisgarh locations from text and source default.
     *
     * @return array{state: ?string, district: ?string, city: ?string, location: ?string}
     */
    protected function detectLocation(string $headline, string $summary, NewsSource $source): array
    {
        $state = $source->state ?: 'Chhattisgarh';
        $district = $source->district;
        $city = $source->city;
        $location = null;

        $text = $headline.' '.$summary;

        foreach (self::CHHATTISGARH_DISTRICTS as $dist) {
            if (stripos($text, $dist) !== false) {
                $district = $dist;
                $state = 'Chhattisgarh';
                break;
            }
        }

        if ($district && $city) {
            $location = "{$city}, {$district}";
        } elseif ($district) {
            $location = $district;
        } elseif ($city) {
            $location = $city;
        }

        return [
            'state' => $state,
            'district' => $district,
            'city' => $city,
            'location' => $location,
        ];
    }

    /**
     * Prevent SSRF attacks by validating URL and resolving IP address.
     */
    protected function validateSafeUrl(string $url): void
    {
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Feed URL is invalid');
        }

        $parsed = parse_url($url);
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException("Invalid feed scheme '{$scheme}'. Only http and https are allowed.");
        }

        $host = $parsed['host'] ?? '';
        if (empty($host)) {
            throw new \InvalidArgumentException('Feed URL has no host');
        }

        // Allow localhost only in local/testing environment
        if (app()->environment('local', 'testing') && in_array($host, ['localhost', '127.0.0.1'])) {
            return;
        }

        // Resolve host to IP
        $ip = gethostbyname($host);
        if ($ip === $host && ! filter_var($host, FILTER_VALIDATE_IP)) {
            throw new \RuntimeException("Could not resolve host: {$host}");
        }

        // Reject private/reserved IP ranges
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new \InvalidArgumentException("Feed URL resolves to private or reserved IP address: {$ip}");
        }
    }
}

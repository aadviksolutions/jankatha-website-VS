<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsSource;
use App\Models\Setting;
use App\Models\User;
use App\Services\NewsFetchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutomaticNewsAggregationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->category = Category::create([
            'name' => 'Chhattisgarh',
            'slug' => 'chhattisgarh',
            'status' => 'active',
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_create_and_manage_news_sources(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.sources.store'), [
            'name' => 'PIB Raipur',
            'url' => 'https://pib.gov.in',
            'feed_url' => 'https://pib.gov.in/RssMain.aspx?ModId=6',
            'source_type' => 'rss',
            'category_id' => $this->category->id,
            'state' => 'Chhattisgarh',
            'district' => 'Raipur',
            'city' => 'Raipur',
            'language' => 'hi',
            'is_active' => '1',
            'fetch_frequency_minutes' => 10,
            'priority' => 10,
            'attribution_text' => 'Source: Press Information Bureau',
        ]);

        $response->assertRedirect(route('admin.sources.index'));
        $this->assertDatabaseHas('news_sources', [
            'name' => 'PIB Raipur',
            'feed_url' => 'https://pib.gov.in/RssMain.aspx?ModId=6',
            'is_active' => true,
        ]);

        $source = NewsSource::where('name', 'PIB Raipur')->firstOrFail();

        // Toggle active status
        $this->actingAs($this->admin)->patch(route('admin.sources.toggle-status', $source));
        $this->assertFalse($source->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('admin.sources.toggle-status', $source));
        $this->assertTrue($source->fresh()->is_active);
    }

    public function test_news_fetch_service_ingests_rss_and_defaults_to_pending_review(): void
    {
        $sampleXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Chhattisgarh News Feed</title>
    <link>https://example.com/news</link>
    <description>Local news feed</description>
    <item>
      <title>Raipur Smart City New Initiative Launched</title>
      <link>https://example.com/news/raipur-smart-city-101</link>
      <guid>raipur-smart-city-101</guid>
      <description>The state capital of Raipur launched an innovative municipal project today.</description>
      <pubDate>Fri, 25 Sep 2026 10:00:00 GMT</pubDate>
      <category>Chhattisgarh</category>
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'https://example.com/rss.xml*' => Http::response($sampleXml, 200),
        ]);

        $source = NewsSource::create([
            'name' => 'State Feed',
            'feed_url' => 'https://example.com/rss.xml',
            'source_type' => 'rss',
            'category_id' => $this->category->id,
            'state' => 'Chhattisgarh',
            'district' => 'Raipur',
            'is_active' => true,
            'fetch_frequency_minutes' => 10,
            'priority' => 5,
        ]);

        // AUTO_PUBLISH = 0 (Default OFF)
        Setting::updateOrCreate(
            ['key' => 'auto_publish_enabled'],
            ['value' => '0', 'group' => 'auto_news']
        );

        $service = app(NewsFetchService::class);
        $result = $service->fetchSingleSource($source);

        $this->assertEquals(1, $result['items_found']);
        $this->assertEquals(1, $result['items_imported']);
        $this->assertEquals(0, $result['items_skipped_duplicate']);

        $article = News::where('source_guid', 'raipur-smart-city-101')->firstOrFail();
        $this->assertEquals('pending_review', $article->status);
        $this->assertTrue($article->is_auto_fetched);
        $this->assertEquals('State Feed', $article->source_name);
        $this->assertEquals('Raipur', $article->district);

        // Fetch logs created
        $this->assertDatabaseHas('news_fetch_logs', [
            'news_source_id' => $source->id,
            'status' => 'success',
            'items_imported' => 1,
        ]);
    }

    public function test_duplicate_news_items_are_skipped(): void
    {
        $sampleXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Daily Updates</title>
    <item>
      <title>Bilaspur High Court Important Ruling Today</title>
      <link>https://example.com/news/bilaspur-court-ruling</link>
      <guid>guid-bilaspur-court-1</guid>
      <description>The Bilaspur High Court issued directions on urban governance.</description>
      <pubDate>Fri, 25 Sep 2026 11:00:00 GMT</pubDate>
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'https://example.com/bilaspur.xml*' => Http::response($sampleXml, 200),
        ]);

        $source = NewsSource::create([
            'name' => 'Court Dispatch',
            'feed_url' => 'https://example.com/bilaspur.xml',
            'source_type' => 'rss',
            'category_id' => $this->category->id,
            'is_active' => true,
            'fetch_frequency_minutes' => 10,
        ]);

        $service = app(NewsFetchService::class);

        // First fetch
        $firstResult = $service->fetchSingleSource($source);
        $this->assertEquals(1, $firstResult['items_imported']);
        $this->assertEquals(0, $firstResult['items_skipped_duplicate']);

        // Second fetch of the exact same feed
        $secondResult = $service->fetchSingleSource($source);
        $this->assertEquals(0, $secondResult['items_imported']);
        $this->assertEquals(1, $secondResult['items_skipped_duplicate']);

        // Ensure database only contains 1 copy
        $this->assertEquals(1, News::where('source_guid', 'guid-bilaspur-court-1')->count());
    }

    public function test_auto_publish_publishes_valid_articles_when_enabled(): void
    {
        $sampleXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>National Feed</title>
    <item>
      <title>Bastar Tribal Handicrafts Exhibition Opens in Raipur</title>
      <link>https://example.com/news/bastar-exhibition</link>
      <guid>bastar-exhibit-2026</guid>
      <description>Artisans from Bastar are showcasing traditional bell metal and terracotta art in Raipur this weekend.</description>
      <pubDate>Fri, 25 Sep 2026 12:00:00 GMT</pubDate>
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'https://example.com/auto.xml*' => Http::response($sampleXml, 200),
        ]);

        $source = NewsSource::create([
            'name' => 'Culture Wire',
            'feed_url' => 'https://example.com/auto.xml',
            'source_type' => 'rss',
            'category_id' => $this->category->id,
            'is_active' => true,
            'fetch_frequency_minutes' => 10,
        ]);

        // Enable AUTO_PUBLISH = 1
        Setting::updateOrCreate(
            ['key' => 'auto_publish_enabled'],
            ['value' => '1', 'group' => 'auto_news']
        );

        $service = app(NewsFetchService::class);
        $result = $service->fetchSingleSource($source);

        $this->assertEquals(1, $result['items_imported']);

        $article = News::where('source_guid', 'bastar-exhibit-2026')->firstOrFail();
        $this->assertEquals('published', $article->status);
        $this->assertNotNull($article->published_at);
    }

    public function test_failed_source_does_not_crash_fetch_and_records_failure_log(): void
    {
        Http::fake([
            'https://example.com/bad-feed.xml*' => Http::response('Server Internal Error', 500),
        ]);

        $source = NewsSource::create([
            'name' => 'Failing Source',
            'feed_url' => 'https://example.com/bad-feed.xml',
            'source_type' => 'rss',
            'is_active' => true,
            'fetch_frequency_minutes' => 10,
        ]);

        $service = app(NewsFetchService::class);
        $result = $service->fetchSingleSource($source);

        $this->assertNotNull($result['error']);
        $this->assertDatabaseHas('news_fetch_logs', [
            'news_source_id' => $source->id,
            'status' => 'failed',
        ]);
        $this->assertNotNull($source->fresh()->last_error);
    }

    public function test_secure_cron_endpoint_requires_valid_secret(): void
    {
        putenv('CRON_SECRET=super_secret_token_123');

        // Request with no token -> 401
        $responseNoAuth = $this->getJson(route('api.cron.fetch-news'));
        $responseNoAuth->assertStatus(401);

        // Request with invalid token -> 401
        $responseBadAuth = $this->getJson(route('api.cron.fetch-news'), [
            'Authorization' => 'Bearer wrong_token',
        ]);
        $responseBadAuth->assertStatus(401);

        // Request with valid Bearer token -> 200
        $responseValid = $this->getJson(route('api.cron.fetch-news'), [
            'Authorization' => 'Bearer super_secret_token_123',
        ]);
        $responseValid->assertStatus(200)->assertJson(['success' => true]);

        putenv('CRON_SECRET');
    }

    public function test_admin_can_toggle_breaking_news(): void
    {
        $article = News::create([
            'category_id' => $this->category->id,
            'headline' => 'Critical Weather Alert for Central Districts',
            'slug' => 'critical-weather-alert-'.uniqid(),
            'content' => 'Meteorological department issued rainfall warnings.',
            'status' => 'published',
            'is_breaking' => false,
            'published_at' => now(),
        ]);

        $this->actingAs($this->admin)->patch(route('admin.news.toggle-breaking', $article));
        $this->assertTrue($article->fresh()->is_breaking);

        $this->actingAs($this->admin)->patch(route('admin.news.toggle-breaking', $article));
        $this->assertFalse($article->fresh()->is_breaking);
    }

    public function test_imported_article_shows_attribution_on_public_page(): void
    {
        $article = News::create([
            'category_id' => $this->category->id,
            'headline' => 'State Level Sports Championship Begins',
            'slug' => 'sports-championship-begins-'.uniqid(),
            'short_description' => 'Over 500 athletes assemble in Raipur for state games.',
            'content' => '<p>The championship was inaugurated this morning by the sports minister.</p>',
            'status' => 'published',
            'is_auto_fetched' => true,
            'source_name' => 'Sports Bureau Agency',
            'source_url' => 'https://sportsagency.example.com/report-1',
            'attribution_text' => 'Source: Sports Bureau Agency exclusive coverage',
            'published_at' => now(),
        ]);

        $response = $this->get(route('news.show', $article->slug));
        $response->assertStatus(200);
        $response->assertSee('Sports Bureau Agency');
        $response->assertSee('https://sportsagency.example.com/report-1');
        $response->assertSee('NewsArticle'); // JSON-LD schema
    }

    public function test_sitemap_xml_renders_correctly(): void
    {
        News::create([
            'category_id' => $this->category->id,
            'headline' => 'Sitemap Indexable News Item',
            'slug' => 'sitemap-indexable-item',
            'content' => 'Test content for sitemap verification.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('sitemap'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type') ?? '');
        $response->assertSee('sitemap-indexable-item');
    }

    public function test_manual_news_posting_still_works(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.news.store'), [
            'category_id' => $this->category->id,
            'headline' => 'Exclusive Investigative Story by Jankatha Team',
            'short_description' => 'In-depth ground investigation into rural road developments.',
            'content' => '<p>This is an authentic on-the-ground report conducted by our reporters.</p>',
            'state' => 'Chhattisgarh',
            'district' => 'Korba',
            'city' => 'Korba',
            'status' => 'published',
            'is_breaking' => '1',
            'is_featured' => '1',
            'tags' => 'investigation, roads, ground report',
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', [
            'headline' => 'Exclusive Investigative Story by Jankatha Team',
            'is_auto_fetched' => false,
            'is_breaking' => true,
            'district' => 'Korba',
        ]);
    }
}

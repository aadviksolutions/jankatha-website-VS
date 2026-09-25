<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicNewsController extends Controller
{
    public function home(): View
    {
        $query = $this->publishedQuery();
        $hero = (clone $query)->where('is_featured', true)->first() ?: (clone $query)->latest('published_at')->first();
        $latest = (clone $query)->when($hero, fn (Builder $builder) => $builder->whereKeyNot($hero->getKey()))->latest('published_at')->limit(6)->get();

        return $this->page('public.home', [
            'hero' => $hero,
            'latest' => $latest,
            'topStories' => (clone $query)->latest('published_at')->limit(5)->get(),
            'categories' => $this->activeCategories(),
            'breakingNews' => (clone $query)->where('is_breaking', true)->latest('published_at')->limit(6)->get(),
            'chhattisgarhNews' => $this->byCategoryOrLocation(['chhattisgarh'], 'state', 'Chhattisgarh', 4),
            'raipurNews' => $this->byCategoryOrLocation(['raipur'], 'district', 'Raipur', 4),
            'localNews' => $this->localQuery()->limit(6)->get(),
            'indiaNews' => $this->byCategoryOrLocation(['india', 'bharat', 'desh'], null, null, 4),
            'worldNews' => $this->byCategoryOrLocation(['world', 'videsh', 'international'], null, null, 4),
            'businessNews' => $this->byCategoryOrLocation(['business', 'vyapar', 'market'], null, null, 4),
            'sportsNews' => $this->byCategoryOrLocation(['sports', 'khel'], null, null, 4),
            'entertainmentNews' => $this->byCategoryOrLocation(['entertainment', 'manoranjan'], null, null, 4),
            'videoNews' => $this->videoQuery()->limit(4)->get(),
            'photoNews' => (clone $query)->whereNotNull('featured_image')->latest('published_at')->limit(6)->get(),
            'seoTitle' => 'Jankatha.com | Real Stories. Real People.',
            'seoDescription' => 'Jankatha brings trusted local, regional and citizen-driven news from Chhattisgarh and beyond.',
        ]);
    }

    public function sitemap(): Response
    {
        $news = News::query()->published()->latest('published_at')->limit(500)->get();
        $categories = Category::query()->where('status', 'active')->get();

        return response()->view('public.sitemap', compact('news', 'categories'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function news(): View
    {
        return $this->page('public.news.index', [
            'news' => $this->publishedQuery()->latest('published_at')->paginate(12)->withQueryString(),
            'pageTitle' => 'Latest News',
            'seoTitle' => 'Latest News | Jankatha.com',
        ]);
    }

    public function show(string $slug): View
    {
        $news = $this->publishedQuery()->where('slug', $slug)->firstOrFail();
        $related = $this->publishedQuery()->where('category_id', $news->category_id)->whereKeyNot($news->id)->latest('published_at')->limit(4)->get();

        return $this->page('public.news.show', [
            'news' => $news,
            'related' => $related,
            'safeVideoUrl' => $this->safeVideoUrl($news->video_url),
            'seoTitle' => ($news->seo_title ?: $news->headline).' | Jankatha.com',
            'seoDescription' => Str::limit(strip_tags($news->seo_description ?: $news->short_description ?: $news->content), 160),
            'seoImage' => $this->mediaUrl($news->featured_image),
            'canonical' => route('news.show', $news->slug),
        ]);
    }

    public function category(string $slug): View
    {
        $category = Category::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $news = $this->publishedQuery()->where('category_id', $category->id)->latest('published_at')->paginate(12)->withQueryString();
        $featured = $this->publishedQuery()->where('category_id', $category->id)->latest('published_at')->first();

        return $this->page('public.category.show', [
            'category' => $category,
            'news' => $news,
            'featured' => $featured,
            'sidebarNews' => $this->publishedQuery()->latest('published_at')->limit(5)->get(),
            'seoTitle' => $category->name.' News | Jankatha.com',
            'seoDescription' => $category->description ?: 'Latest '.$category->name.' news from Jankatha.',
        ]);
    }

    public function local(Request $request): View
    {
        $query = $this->localQuery()
            ->when($request->filled('state'), fn (Builder $builder) => $builder->where('state', $request->string('state')->toString()))
            ->when($request->filled('district'), fn (Builder $builder) => $builder->where('district', $request->string('district')->toString()))
            ->when($request->filled('location'), fn (Builder $builder) => $builder->where('location', 'like', '%'.$request->string('location')->toString().'%'));

        return $this->page('public.local', [
            'news' => $query->latest('published_at')->paginate(12)->withQueryString(),
            'states' => News::query()->published()->whereNotNull('state')->distinct()->orderBy('state')->pluck('state'),
            'districts' => News::query()->published()->whereNotNull('district')->distinct()->orderBy('district')->pluck('district'),
            'seoTitle' => 'Local News | Jankatha.com',
            'seoDescription' => 'Local news from cities, districts and communities across Chhattisgarh.',
        ]);
    }

    public function photo(): View
    {
        return $this->page('public.media', [
            'mediaTitle' => 'Photo News',
            'mediaDescription' => 'Stories told through photographs from across our communities.',
            'mediaType' => 'photo',
            'news' => $this->publishedQuery()->whereNotNull('featured_image')->latest('published_at')->paginate(12)->withQueryString(),
            'seoTitle' => 'Photo News | Jankatha.com',
        ]);
    }

    public function video(): View
    {
        $news = $this->videoQuery()->latest('published_at')->paginate(12)->withQueryString();

        return $this->page('public.media', [
            'mediaTitle' => 'Video News',
            'mediaDescription' => 'Watch the latest video reports from Jankatha.',
            'mediaType' => 'video',
            'news' => $news,
            'seoTitle' => 'Video News | Jankatha.com',
        ]);
    }

    public function breaking(): View
    {
        return $this->page('public.news.index', [
            'news' => $this->publishedQuery()->where('is_breaking', true)->latest('published_at')->paginate(12)->withQueryString(),
            'pageTitle' => 'Breaking News',
            'seoTitle' => 'Breaking News | Jankatha.com',
        ]);
    }

    public function search(Request $request): View
    {
        $term = Str::of($request->string('q')->toString())->trim()->limit(100)->toString();
        $news = $this->publishedQuery()->when($term !== '', function (Builder $builder) use ($term): void {
            $builder->where(function (Builder $search) use ($term): void {
                foreach (['headline', 'content', 'short_description', 'location', 'tags'] as $column) {
                    $search->orWhere($column, 'like', '%'.$term.'%');
                }
            });
        })->latest('published_at')->paginate(12)->withQueryString();

        return $this->page('public.search', [
            'news' => $news,
            'term' => $term,
            'seoTitle' => $term ? 'Search: '.$term.' | Jankatha.com' : 'Search | Jankatha.com',
        ]);
    }

    public function about(): View
    {
        return $this->page('public.static', ['pageTitle' => 'About Jankatha', 'pageKey' => 'about', 'seoTitle' => 'About Jankatha.com']);
    }

    public function contact(): View
    {
        return $this->page('public.static', ['pageTitle' => 'Contact Jankatha', 'pageKey' => 'contact', 'seoTitle' => 'Contact Jankatha.com']);
    }

    public function privacyPolicy(): View
    {
        return $this->page('public.static', ['pageTitle' => 'Privacy Policy', 'pageKey' => 'privacy', 'seoTitle' => 'Privacy Policy | Jankatha.com']);
    }

    public function terms(): View
    {
        return $this->page('public.static', ['pageTitle' => 'Terms & Conditions', 'pageKey' => 'terms', 'seoTitle' => 'Terms & Conditions | Jankatha.com']);
    }

    public function disclaimer(): View
    {
        return $this->page('public.static', ['pageTitle' => 'Editorial Disclaimer', 'pageKey' => 'disclaimer', 'seoTitle' => 'Editorial Disclaimer | Jankatha.com']);
    }

    public function submitNews()
    {
        if (auth()->check() && auth()->user()->hasRole('citizen', 'contributor')) {
            return redirect()->route('my-submissions.create');
        }

        return redirect()->route('login');
    }

    private function publishedQuery(): Builder
    {
        return News::query()
            ->published()
            ->with(['category', 'author', 'source', 'publishedSubmission.media']);
    }

    private function byCategoryOrLocation(array $slugs, ?string $column = null, ?string $value = null, int $limit = 4): Collection
    {
        return $this->publishedQuery()
            ->where(function (Builder $query) use ($slugs, $column, $value): void {
                $query->whereHas('category', function (Builder $catQuery) use ($slugs): void {
                    $catQuery->whereIn('slug', $slugs);
                });

                if ($column && $value) {
                    $query->orWhere($column, $value);
                }
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    private function localQuery(): Builder
    {
        return $this->publishedQuery()->where(function (Builder $query): void {
            $query->whereNotNull('location')->orWhereNotNull('district')->orWhereNotNull('state');
        });
    }

    private function videoQuery(): Builder
    {
        return $this->publishedQuery()->where(function (Builder $query): void {
            $query->where('video_url', 'like', 'https://%')->orWhere('video_url', 'like', '/storage/%');
        });
    }

    private function activeCategories(): Collection
    {
        return Category::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
    }

    private function page(string $view, array $data = []): View
    {
        return view($view, array_merge([
            'navCategories' => $this->activeCategories(),
            'breakingHeadlines' => $this->publishedQuery()->where('is_breaking', true)->latest('published_at')->limit(3)->get(),
            'seoTitle' => 'Jankatha.com | Real Stories. Real People.',
            'seoDescription' => 'Real stories. Real people. Trusted local-first news from Jankatha.',
            'seoImage' => null,
            'canonical' => url()->current(),
        ], $data));
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset('storage/'.$path);
    }

    private function safeVideoUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (Str::startsWith($url, '/storage/')) {
            return $url;
        }

        $parsed = parse_url($url);
        if (($parsed['scheme'] ?? null) !== 'https' || empty($parsed['host'])) {
            return null;
        }

        return $url;
    }
}

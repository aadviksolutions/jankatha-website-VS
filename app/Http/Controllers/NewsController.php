<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', News::class);

        $news = News::query()->with(['category', 'author', 'source'])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where('headline', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%");
            }))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('is_auto_fetched'), fn ($query) => $query->where('is_auto_fetched', $request->boolean('is_auto_fetched')))
            ->when($request->filled('is_breaking'), fn ($query) => $query->where('is_breaking', $request->boolean('is_breaking')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.news.index', [
            'news' => $news,
            'categories' => Category::query()->orderBy('name')->get(),
            'counts' => [
                'all' => News::count(),
                'pending' => News::where('status', 'pending_review')->count(),
                'published' => News::where('status', 'published')->count(),
                'draft' => News::where('status', 'draft')->count(),
                'scheduled' => News::where('status', 'scheduled')->count(),
                'auto' => News::where('is_auto_fetched', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', News::class);

        return view('admin.news.form', [
            'news' => new News(['status' => 'draft', 'state' => 'Chhattisgarh']),
            'categories' => Category::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', News::class);
        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['headline']);
        $validated['author_id'] = $request->user()->id;
        $validated['content'] = $this->sanitizeContent($validated['content']);
        $validated['featured_image'] = $this->storeImage($request);
        $this->normalizePublication($validated);

        News::create($validated);

        return redirect()->route('admin.news.index')->with('status', 'News saved.');
    }

    public function show(News $news): View
    {
        $this->authorize('view', $news);

        return view('admin.news.show', compact('news'));
    }

    public function edit(News $news): View
    {
        $this->authorize('update', $news);

        return view('admin.news.form', [
            'news' => $news,
            'categories' => Category::query()->where('status', 'active')->orWhereKey($news->category_id)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $this->authorize('update', $news);
        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['headline'], $news);
        $validated['content'] = $this->sanitizeContent($validated['content']);
        $this->normalizePublication($validated);

        if ($request->hasFile('featured_image')) {
            if ($news->featured_image && ! Str::startsWith($news->featured_image, ['http://', 'https://'])) {
                Storage::disk('public')->delete($news->featured_image);
            }
            $validated['featured_image'] = $this->storeImage($request);
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('status', 'News updated.');
    }

    public function updateStatus(Request $request, News $news): RedirectResponse
    {
        $this->authorize('publish', $news);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published', 'archived', 'pending_review'])],
        ]);

        $news->update([
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? ($news->published_at ?: now()) : ($validated['status'] === 'scheduled' ? $news->published_at : null),
        ]);

        return back()->with('status', 'News status updated to '.$validated['status'].'.');
    }

    public function toggleBreaking(News $news): RedirectResponse
    {
        $this->authorize('update', $news);
        $news->update([
            'is_breaking' => ! $news->is_breaking,
        ]);

        $statusText = $news->is_breaking ? 'marked as Breaking News' : 'removed from Breaking News';

        return back()->with('status', "Article {$statusText}.");
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        if ($news->featured_image && ! Str::startsWith($news->featured_image, ['http://', 'https://'])) {
            Storage::disk('public')->delete($news->featured_image);
        }
        $news->delete();

        return redirect()->route('admin.news.index')->with('status', 'News deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'headline' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:5000'],
            'content' => ['required', 'string', 'max:100000'],
            'featured_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'location' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published', 'archived', 'pending_review'])],
            'is_breaking' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:5000'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'attribution_text' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === 'scheduled' && (empty($validated['published_at']) || now()->gte($validated['published_at']))) {
            throw ValidationException::withMessages([
                'published_at' => 'Scheduled news must have a future publish date and time.',
            ]);
        }

        return $validated;
    }

    private function uniqueSlug(string $headline, ?News $news = null): string
    {
        $base = Str::slug($headline) ?: 'news';
        $slug = $base;
        $counter = 2;

        while (News::query()->where('slug', $slug)->when($news, fn ($query) => $query->whereKeyNot($news->getKey()))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function storeImage(Request $request): ?string
    {
        return $request->hasFile('featured_image') ? $request->file('featured_image')->store('news', 'public') : null;
    }

    private function sanitizeContent(string $content): string
    {
        $content = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $content) ?? $content;
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><a><blockquote>');
        $content = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? $content;

        return preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $content) ?? $content;
    }

    private function normalizePublication(array &$validated): void
    {
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if ($validated['status'] !== 'scheduled' && $validated['status'] !== 'published') {
            $validated['published_at'] = null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NewsSource;
use App\Services\NewsFetchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsSourceController extends Controller
{
    public function index(): View
    {
        $sources = NewsSource::query()
            ->with('category')
            ->withCount('news')
            ->orderByDesc('priority')
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => NewsSource::count(),
            'active' => NewsSource::where('is_active', true)->count(),
            'inactive' => NewsSource::where('is_active', false)->count(),
            'total_items_fetched' => NewsSource::sum('items_fetched_count'),
        ];

        return view('admin.sources.index', compact('sources', 'stats'));
    }

    public function create(): View
    {
        return view('admin.sources.form', [
            'source' => new NewsSource([
                'is_active' => true,
                'source_type' => 'rss',
                'fetch_frequency_minutes' => 10,
                'language' => 'hi',
                'state' => 'Chhattisgarh',
            ]),
            'categories' => Category::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSource($request);

        $source = NewsSource::create($validated);

        return redirect()->route('admin.sources.index')->with('status', "Source '{$source->name}' created successfully.");
    }

    public function edit(NewsSource $source): View
    {
        return view('admin.sources.form', [
            'source' => $source,
            'categories' => Category::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, NewsSource $source): RedirectResponse
    {
        $validated = $this->validateSource($request, $source);

        $source->update($validated);

        return redirect()->route('admin.sources.index')->with('status', "Source '{$source->name}' updated successfully.");
    }

    public function destroy(NewsSource $source): RedirectResponse
    {
        $name = $source->name;
        $source->delete();

        return redirect()->route('admin.sources.index')->with('status', "Source '{$name}' deleted.");
    }

    public function toggleStatus(NewsSource $source): RedirectResponse
    {
        $source->update([
            'is_active' => ! $source->is_active,
        ]);

        $state = $source->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Source '{$source->name}' {$state}.");
    }

    public function fetchNow(NewsSource $source, NewsFetchService $service): RedirectResponse
    {
        $result = $service->fetchSingleSource($source);

        if ($result['error']) {
            return back()->with('status', "Fetch completed with warning for '{$source->name}': {$result['error']}");
        }

        return back()->with('status', "Fetch successful for '{$source->name}': {$result['items_found']} items found, {$result['items_imported']} imported, {$result['items_skipped_duplicate']} skipped (duplicates).");
    }

    private function validateSource(Request $request, ?NewsSource $source = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'feed_url' => ['required', 'url', 'max:2048'],
            'source_type' => ['required', Rule::in(['rss', 'json_api'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'state' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'fetch_frequency_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'priority' => ['required', 'integer', 'min:0', 'max:100'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'attribution_text' => ['nullable', 'string', 'max:500'],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\NewsAiProcessorService;
use App\Services\NewsFetchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsSettingsController extends Controller
{
    public function index(NewsAiProcessorService $aiProcessor): View
    {
        $settings = Setting::query()->where('group', 'auto_news')->pluck('value', 'key');
        $categories = Category::query()->where('status', 'active')->orderBy('name')->get();

        return view('admin.settings.news', [
            'settings' => $settings,
            'categories' => $categories,
            'aiConfigured' => $aiProcessor->isConfigured(),
            'cronSecretSet' => ! empty(env('CRON_SECRET')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_news_enabled' => ['required', 'in:0,1'],
            'auto_publish_enabled' => ['required', 'in:0,1'],
            'auto_news_fetch_frequency' => ['required', 'in:5,10,15,30,60'],
            'auto_news_default_category_id' => ['nullable', 'exists:categories,id'],
            'auto_news_default_language' => ['required', 'string', 'max:10'],
            'auto_news_max_items_per_fetch' => ['required', 'integer', 'min:1', 'max:100'],
            'auto_news_require_editorial_approval' => ['required', 'in:0,1'],
            'auto_news_ai_processing' => ['required', 'in:0,1'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'group' => 'auto_news']
            );
        }

        return back()->with('status', 'News settings saved successfully.');
    }

    public function fetchAllNow(Request $request, NewsFetchService $service): RedirectResponse
    {
        $stats = $service->fetchActiveSources(null, true);

        $msg = "Fetch completed: {$stats['sources_processed']} sources processed, {$stats['items_imported']} new items imported, {$stats['items_skipped_duplicate']} duplicates skipped.";

        if (! empty($stats['errors'])) {
            $msg .= ' ('.count($stats['errors']).' source errors occurred).';
        }

        return back()->with('status', $msg);
    }
}

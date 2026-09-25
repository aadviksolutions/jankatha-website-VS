<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsFetchLog;
use App\Models\NewsSource;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $stats = [
            'total_sources' => NewsSource::count(),
            'active_sources' => NewsSource::where('is_active', true)->count(),
            'news_fetched_today' => (int) NewsFetchLog::whereDate('created_at', $today)->sum('items_imported'),
            'pending_review' => News::where('status', 'pending_review')->count(),
            'published_today' => News::where('status', 'published')->whereDate('published_at', $today)->count(),
            'failed_fetches' => NewsFetchLog::where('status', 'failed')->count(),
            'duplicate_items' => (int) NewsFetchLog::sum('items_skipped_duplicate'),
            'total_news' => News::count(),
            'published_news' => News::where('status', 'published')->count(),
            'draft_news' => News::where('status', 'draft')->count(),
            'scheduled_news' => News::where('status', 'scheduled')->count(),
            'auto_news' => News::where('is_auto_fetched', true)->count(),
            'breaking' => News::where('is_breaking', true)->count(),
            'categories' => Category::count(),
        ];

        $recentPending = News::where('status', 'pending_review')
            ->with(['category', 'source'])
            ->latest()
            ->limit(5)
            ->get();

        $recentLogs = NewsFetchLog::with('source')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'area' => 'Dashboard',
            'stats' => $stats,
            'recentPending' => $recentPending,
            'recentLogs' => $recentLogs,
        ]);
    }

    public function admin(): View
    {
        return $this->index();
    }

    public function editor(): View
    {
        return $this->index();
    }
}

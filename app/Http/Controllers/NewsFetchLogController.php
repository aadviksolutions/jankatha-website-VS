<?php

namespace App\Http\Controllers;

use App\Models\NewsFetchLog;
use App\Models\NewsSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsFetchLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsFetchLog::query()->with('source');

        if ($request->filled('source_id')) {
            $query->where('news_source_id', $request->integer('source_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total' => NewsFetchLog::count(),
            'success' => NewsFetchLog::where('status', 'success')->count(),
            'failed' => NewsFetchLog::where('status', 'failed')->count(),
        ];

        $sources = NewsSource::query()->orderBy('name')->get();

        return view('admin.logs.index', compact('logs', 'stats', 'sources'));
    }

    public function clear(): RedirectResponse
    {
        NewsFetchLog::truncate();

        return back()->with('status', 'All fetch logs have been cleared.');
    }
}

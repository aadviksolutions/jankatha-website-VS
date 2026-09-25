@extends('admin.layout')

@section('title', $area.' Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $area }} Dashboard</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">
            Welcome back, <strong>{{ auth()->user()->name }}</strong> (Role: <span class="badge badge-info">{{ auth()->user()->role }}</span>)
        </p>
    </div>
    @if (auth()->user()->hasRole('super_admin', 'admin', 'editor'))
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <form method="POST" action="{{ route('admin.settings.news.fetch-all') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('Trigger feed fetch for all active sources right now?')">
                ⚡ FETCH NOW
            </button>
        </form>
        <a href="{{ route('admin.news.create') }}" class="btn btn-secondary">+ Publish Story</a>
    </div>
    @endif
</div>

@if (auth()->user()->hasRole('citizen', 'contributor'))
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="margin-top: 0; font-size: 1.1rem;">Citizen Newsroom Submissions</h2>
        <p>Report grassroots news, events, and issues from your locality.</p>
        <a href="{{ route('my-submissions.create') }}" class="btn btn-primary">अपनी खबर भेजें (Submit News)</a>
        <a href="{{ route('my-submissions.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">मेरी खबरें (My Submissions)</a>
    </div>
@endif

@isset($stats)
    <h2 style="font-size: 1.15rem; margin-bottom: 0.75rem;">Automatic News & Source Metrics</h2>
    <div class="grid-4" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-val">{{ $stats['total_sources'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.sources.index') }}" style="color: inherit; text-decoration: none;">Total Sources</a></div>
            <div style="font-size: 0.8rem; color: var(--admin-success); margin-top: 0.25rem;">{{ $stats['active_sources'] }} Active</div>
        </div>

        <div class="stat-card">
            <div class="stat-val" style="color: var(--admin-success);">+{{ $stats['news_fetched_today'] }}</div>
            <div class="stat-lbl">News Fetched Today</div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">Automatic ingestion</div>
        </div>

        <div class="stat-card" style="{{ $stats['pending_review'] > 0 ? 'border-color: #fde68a; background: #fffbeb;' : '' }}">
            <div class="stat-val" style="color: var(--admin-warning);">
                {{ $stats['pending_review'] }}
            </div>
            <div class="stat-lbl">
                <a href="{{ route('admin.news.index', ['status' => 'pending_review']) }}" style="color: inherit; text-decoration: underline;">
                    Pending Review
                </a>
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">Awaiting editor approval</div>
        </div>

        <div class="stat-card">
            <div class="stat-val" style="color: #2563eb;">{{ $stats['published_today'] }}</div>
            <div class="stat-lbl">Published Today</div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">Live on portal</div>
        </div>
    </div>

    <div class="grid-3" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-val" style="color: var(--admin-danger);">{{ $stats['failed_fetches'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.logs.index', ['status' => 'failed']) }}" style="color: inherit; text-decoration: none;">Failed Fetches</a></div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">Feed errors recorded</div>
        </div>

        <div class="stat-card">
            <div class="stat-val" style="color: var(--admin-muted);">{{ number_format($stats['duplicate_items']) }}</div>
            <div class="stat-lbl">Duplicate Items Skipped</div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">Deduplication guardrail</div>
        </div>

        <div class="stat-card">
            <div class="stat-val">{{ $stats['auto_news'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.news.index', ['is_auto_fetched' => '1']) }}" style="color: inherit; text-decoration: none;">Total Auto Stories</a></div>
            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.25rem;">In database</div>
        </div>
    </div>

    <h2 style="font-size: 1.15rem; margin-bottom: 0.75rem;">Editorial CMS Stats</h2>
    <div class="grid-4" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-val">{{ $stats['total_news'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.news.index') }}" style="color: inherit; text-decoration: none;">Total News</a></div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--admin-success);">{{ $stats['published_news'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.news.index', ['status' => 'published']) }}" style="color: inherit; text-decoration: none;">Published</a></div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $stats['draft_news'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.news.index', ['status' => 'draft']) }}" style="color: inherit; text-decoration: none;">Drafts</a></div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: #0284c7;">{{ $stats['scheduled_news'] }}</div>
            <div class="stat-lbl"><a href="{{ route('admin.news.index', ['status' => 'scheduled']) }}" style="color: inherit; text-decoration: none;">Scheduled</a></div>
        </div>
    </div>

    @if ($recentPending->isNotEmpty())
        <div class="card" style="border-left: 4px solid var(--admin-warning);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1.1rem; color: #92400e;">⚡ Stories Awaiting Review ({{ $stats['pending_review'] }})</h3>
                <a href="{{ route('admin.news.index', ['status' => 'pending_review']) }}" class="btn btn-sm btn-secondary">View All Pending →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Headline</th>
                        <th>Category</th>
                        <th>Source</th>
                        <th>Ingested</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recentPending as $pending)
                    <tr>
                        <td><strong>{{ $pending->headline }}</strong></td>
                        <td><span class="badge badge-secondary">{{ $pending->category?->name }}</span></td>
                        <td>{{ $pending->source_name ?? 'External' }}</td>
                        <td>{{ $pending->created_at?->diffForHumans() }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.news.status', $pending) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-sm btn-success">Approve & Publish</button>
                            </form>
                            <a href="{{ route('admin.news.edit', $pending) }}" class="btn btn-sm btn-secondary">Review</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($recentLogs->isNotEmpty())
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1.1rem;">Recent Feed Fetch Activity</h3>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-secondary">All Logs →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Imported</th>
                        <th>Duplicates</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at?->diffForHumans() }}</td>
                        <td><strong>{{ $log->source?->name ?? 'Unknown' }}</strong></td>
                        <td>
                            <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td><strong style="color: var(--admin-success);">+{{ $log->items_imported }}</strong></td>
                        <td>{{ $log->items_skipped_duplicate }}</td>
                        <td style="font-size: 0.8rem; color: var(--admin-muted);">
                            {{ $log->error_message ? Str::limit($log->error_message, 50) : $log->execution_time_ms.'ms' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endisset
@endsection

@extends('admin.layout')

@section('title', 'News Sources')

@section('content')
<div class="page-header">
    <div>
        <h1>News Sources Management</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Configure external RSS feeds and JSON APIs for automatic news aggregation.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <form method="POST" action="{{ route('admin.settings.news.fetch-all') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('Trigger fetch on all active sources now?')">
                ⚡ Fetch All Sources Now
            </button>
        </form>
        <a href="{{ route('admin.sources.create') }}" class="btn btn-secondary">+ Add News Source</a>
    </div>
</div>

<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-val">{{ $stats['total'] }}</div>
        <div class="stat-lbl">Total Sources</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color: var(--admin-success);">{{ $stats['active'] }}</div>
        <div class="stat-lbl">Active Sources</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color: var(--admin-warning);">{{ $stats['inactive'] }}</div>
        <div class="stat-lbl">Inactive Sources</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color: #2563eb;">{{ number_format($stats['total_items_fetched']) }}</div>
        <div class="stat-lbl">News Items Fetched</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Priority</th>
            <th>Source Name</th>
            <th>Type</th>
            <th>Category / Location</th>
            <th>Frequency</th>
            <th>Status</th>
            <th>Last Fetched</th>
            <th>Items</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($sources as $source)
        <tr>
            <td><strong>#{{ $source->priority }}</strong></td>
            <td>
                <strong>{{ $source->name }}</strong>
                @if ($source->url)
                    <br><a href="{{ $source->url }}" target="_blank" rel="noopener" style="font-size: 0.8rem; color: var(--admin-muted);">Website ↗</a>
                @endif
                <div style="font-size: 0.75rem; color: #64748b; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $source->feed_url }}
                </div>
            </td>
            <td><span class="badge badge-info">{{ strtoupper($source->source_type) }}</span></td>
            <td>
                <div>{{ $source->category?->name ?? 'Default' }}</div>
                @if ($source->district || $source->city)
                    <div style="font-size: 0.78rem; color: var(--admin-muted);">{{ $source->city ?: $source->district }}, {{ $source->state }}</div>
                @endif
            </td>
            <td>Every {{ $source->fetch_frequency_minutes }}m</td>
            <td>
                @if ($source->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </td>
            <td>
                @if ($source->last_fetched_at)
                    {{ $source->last_fetched_at->diffForHumans() }}
                @else
                    <span style="color: var(--admin-muted);">Never</span>
                @endif
                @if ($source->last_error)
                    <br><span title="{{ $source->last_error }}" style="color: var(--admin-danger); font-size: 0.75rem; cursor: pointer;">⚠️ Error</span>
                @endif
            </td>
            <td><strong>{{ $source->news_count }}</strong></td>
            <td>
                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                    <form method="POST" action="{{ route('admin.sources.fetch-now', $source) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" title="Fetch news right now">Fetch</button>
                    </form>
                    <a href="{{ route('admin.sources.edit', $source) }}" class="btn btn-sm btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('admin.sources.toggle-status', $source) }}" style="display: inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $source->is_active ? 'btn-danger' : 'btn-success' }}">
                            {{ $source->is_active ? 'Pause' : 'Enable' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.sources.destroy', $source) }}" style="display: inline;" onsubmit="return confirm('Delete news source \'{{ $source->name }}\'?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-secondary" style="color: var(--admin-danger);">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" style="text-align: center; padding: 2rem;">
                <p style="margin: 0; color: var(--admin-muted);">No news sources configured yet.</p>
                <a href="{{ route('admin.sources.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Add First News Source</a>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $sources->links() }}
@endsection

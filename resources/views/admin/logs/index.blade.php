@extends('admin.layout')

@section('title', 'News Fetch Logs')

@section('content')
<div class="page-header">
    <div>
        <h1>News Fetch Logs & Monitoring</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Real-time monitoring of automated feed executions, imported stories, and HTTP errors.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <form method="POST" action="{{ route('admin.logs.clear') }}" onsubmit="return confirm('Clear all fetch logs?')">
            @csrf
            <button type="submit" class="btn btn-secondary">Clear Old Logs</button>
        </form>
    </div>
</div>

<div class="grid-3" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-val">{{ number_format($stats['total']) }}</div>
        <div class="stat-lbl">Total Executions</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color: var(--admin-success);">{{ number_format($stats['success']) }}</div>
        <div class="stat-lbl">Successful Fetches</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color: var(--admin-danger);">{{ number_format($stats['failed']) }}</div>
        <div class="stat-lbl">Failed Fetches</div>
    </div>
</div>

<form method="GET" action="{{ route('admin.logs.index') }}" class="filter-bar">
    <select name="source_id">
        <option value="">All News Sources</option>
        @foreach ($sources as $source)
            <option value="{{ $source->id }}" @selected((string) request('source_id') === (string) $source->id)>
                {{ $source->name }}
            </option>
        @endforeach
    </select>

    <select name="status">
        <option value="">All Statuses</option>
        <option value="success" @selected(request('status') === 'success')>Success Only</option>
        <option value="failed" @selected(request('status') === 'failed')>Failed Only</option>
    </select>

    <button type="submit" class="btn btn-primary btn-sm">Filter Logs</button>
    @if (request()->hasAny(['source_id', 'status']))
        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    @endif
</form>

<table>
    <thead>
        <tr>
            <th>Time</th>
            <th>Source</th>
            <th>Status</th>
            <th>Found</th>
            <th>Imported</th>
            <th>Duplicates</th>
            <th>HTTP Status</th>
            <th>Execution Time</th>
            <th>Error / Details</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($logs as $log)
        <tr>
            <td><time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->format('d M, H:i:s') }}</time></td>
            <td><strong>{{ $log->source?->name ?? 'Deleted Source' }}</strong></td>
            <td>
                @if ($log->status === 'success')
                    <span class="badge badge-success">Success</span>
                @else
                    <span class="badge badge-danger">Failed</span>
                @endif
            </td>
            <td>{{ $log->items_found }}</td>
            <td><strong style="color: var(--admin-success);">+{{ $log->items_imported }}</strong></td>
            <td>{{ $log->items_skipped_duplicate }}</td>
            <td>{{ $log->http_status ?: '-' }}</td>
            <td>{{ $log->execution_time_ms }}ms</td>
            <td>
                @if ($log->error_message)
                    <span style="color: var(--admin-danger); font-size: 0.82rem; font-family: monospace;" title="{{ $log->error_message }}">
                        {{ Str::limit($log->error_message, 80) }}
                    </span>
                @else
                    <span style="color: var(--admin-muted); font-size: 0.85rem;">—</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" style="text-align: center; padding: 2rem; color: var(--admin-muted);">
                No fetch logs recorded yet.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $logs->links() }}
@endsection

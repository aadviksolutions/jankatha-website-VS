@extends('admin.layout')

@section('title', 'News Management')

@section('content')
<div class="page-header">
    <div>
        <h1>News Management</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Manage editorial stories, breaking news alerts, and review automatically ingested feeds.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <form method="POST" action="{{ route('admin.settings.news.fetch-all') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">⚡ Fetch Feeds Now</button>
        </form>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">+ Add News Story</a>
    </div>
</div>

<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
    <a href="{{ route('admin.news.index') }}" class="btn {{ !request('status') && !request('is_auto_fetched') ? 'btn-primary' : 'btn-secondary' }} btn-sm">
        All ({{ $counts['all'] }})
    </a>
    <a href="{{ route('admin.news.index', ['status' => 'pending_review']) }}" class="btn {{ request('status') === 'pending_review' ? 'btn-primary' : 'btn-secondary' }} btn-sm" style="{{ $counts['pending'] > 0 && request('status') !== 'pending_review' ? 'background: #fef3c7; color: #92400e; border-color: #fde68a;' : '' }}">
        Pending Review ({{ $counts['pending'] }})
    </a>
    <a href="{{ route('admin.news.index', ['status' => 'published']) }}" class="btn {{ request('status') === 'published' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
        Published ({{ $counts['published'] }})
    </a>
    <a href="{{ route('admin.news.index', ['status' => 'draft']) }}" class="btn {{ request('status') === 'draft' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
        Drafts ({{ $counts['draft'] }})
    </a>
    <a href="{{ route('admin.news.index', ['status' => 'scheduled']) }}" class="btn {{ request('status') === 'scheduled' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
        Scheduled ({{ $counts['scheduled'] }})
    </a>
    <a href="{{ route('admin.news.index', ['is_auto_fetched' => '1']) }}" class="btn {{ request('is_auto_fetched') === '1' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
        Auto Fetched ({{ $counts['auto'] }})
    </a>
</div>

<form method="GET" action="{{ route('admin.news.index') }}" class="filter-bar">
    <input type="search" name="search" placeholder="Search headline or description" value="{{ request('search') }}" style="min-width: 240px;">

    <select name="category_id">
        <option value="">All categories</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <select name="status">
        <option value="">All statuses</option>
        @foreach (['pending_review' => 'Pending Review', 'published' => 'Published', 'draft' => 'Draft', 'scheduled' => 'Scheduled', 'archived' => 'Archived'] as $statusVal => $statusLabel)
            <option value="{{ $statusVal }}" @selected(request('status') === $statusVal)>
                {{ $statusLabel }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if (request()->hasAny(['search', 'category_id', 'status', 'is_auto_fetched', 'is_breaking']))
        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    @endif
</form>

<table>
    <thead>
        <tr>
            <th>Headline</th>
            <th>Category</th>
            <th>Source / Author</th>
            <th>Status</th>
            <th>Breaking</th>
            <th>Published</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($news as $item)
        <tr>
            <td>
                <strong>{{ $item->headline }}</strong>
                @if ($item->is_auto_fetched)
                    <span class="badge badge-info" style="font-size: 0.68rem; margin-left: 0.25rem;">AUTO</span>
                @endif
                @if ($item->district)
                    <div style="font-size: 0.78rem; color: var(--admin-muted);">📍 {{ $item->district }}</div>
                @endif
            </td>
            <td><span class="badge badge-secondary">{{ $item->category->name }}</span></td>
            <td>
                @if ($item->source_name)
                    <span style="font-size: 0.85rem; font-weight: 500;">{{ $item->source_name }}</span>
                    @if ($item->source_url)
                        <br><a href="{{ $item->source_url }}" target="_blank" rel="noopener" style="font-size: 0.75rem; color: var(--admin-muted);">Original ↗</a>
                    @endif
                @else
                    <span style="font-size: 0.85rem;">{{ $item->author?->name ?? 'Jankatha Desk' }}</span>
                @endif
            </td>
            <td>
                @if ($item->status === 'published')
                    <span class="badge badge-success">Published</span>
                @elseif ($item->status === 'pending_review')
                    <span class="badge badge-warning">Pending Review</span>
                @elseif ($item->status === 'scheduled')
                    <span class="badge badge-info">Scheduled</span>
                @else
                    <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                @endif
            </td>
            <td>
                <form method="POST" action="{{ route('admin.news.toggle-breaking', $item) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $item->is_breaking ? 'btn-danger' : 'btn-secondary' }}" style="font-size: 0.75rem;" title="Toggle Breaking News status">
                        {{ $item->is_breaking ? '★ BREAKING' : '☆ Normal' }}
                    </button>
                </form>
            </td>
            <td>{{ $item->published_at?->format('d M Y, H:i') ?? '—' }}</td>
            <td>
                <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('news.show', $item->slug) }}" target="_blank" class="btn btn-sm btn-secondary" title="View live article">View ↗</a>
                    <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-secondary">Edit</a>

                    @if ($item->status === 'pending_review')
                        <form method="POST" action="{{ route('admin.news.status', $item) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-sm btn-success" title="Approve and publish story to live portal">Approve & Publish</button>
                        </form>
                    @elseif ($item->status === 'published')
                        <form method="POST" action="{{ route('admin.news.status', $item) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="btn btn-sm btn-secondary">Unpublish</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.news.status', $item) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-sm btn-success">Publish</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.news.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this news item?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--admin-muted);">
                No news items found.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $news->links() }}
@endsection

@extends('admin.layout')

@section('title', 'News Management')

@section('content')
<h1>News Management</h1>
<p><a href="{{ route('admin.news.create') }}">Add news</a></p>
<form method="GET" action="{{ route('admin.news.index') }}">
    <input type="search" name="search" placeholder="Search headline" value="{{ request('search') }}">
    <select name="category_id"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
    <select name="status"><option value="">All statuses</option>@foreach (['draft', 'scheduled', 'published', 'archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
    <button type="submit">Filter</button>
</form>
<table>
    <thead><tr><th>Headline</th><th>Category</th><th>Author</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse ($news as $item)
        <tr>
            <td>{{ $item->headline }}</td><td>{{ $item->category->name }}</td><td>{{ $item->author?->name ?? 'Unknown' }}</td><td>{{ $item->status }}</td><td>{{ $item->published_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td>
                <a href="{{ route('admin.news.show', $item) }}">Preview</a> <a href="{{ route('admin.news.edit', $item) }}">Edit</a>
                @foreach (['published' => 'Publish', 'scheduled' => 'Schedule', 'archived' => 'Archive'] as $status => $label)
                    <form method="POST" action="{{ route('admin.news.status', $item) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button type="submit">{{ $label }}</button></form>
                @endforeach
                <form method="POST" action="{{ route('admin.news.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Delete this news item?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6">No news found.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $news->links() }}
@endsection

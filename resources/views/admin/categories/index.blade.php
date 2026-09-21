@extends('admin.layout')

@section('title', 'Categories')

@section('content')
<h1>Categories</h1>
<p><a href="{{ route('admin.categories.create') }}">Create category</a></p>
<table>
    <thead><tr><th>Order</th><th>Name</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse ($categories as $category)
        <tr>
            <td>{{ $category->sort_order }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->slug }}</td>
            <td>{{ $category->status }}</td>
            <td>
                <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit">{{ $category->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('Delete this category?')">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">No categories found.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $categories->links() }}
@endsection

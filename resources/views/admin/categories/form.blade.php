@extends('admin.layout')

@section('title', $category->exists ? 'Edit Category' : 'Create Category')

@section('content')
<h1>{{ $category->exists ? 'Edit Category' : 'Create Category' }}</h1>
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <label>Name <input type="text" name="name" value="{{ old('name', $category->name) }}" required></label>
    <label>Description <textarea name="description">{{ old('description', $category->description) }}</textarea></label>
    <label>Image <input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
    @if ($category->image)<p><img src="{{ asset('storage/'.$category->image) }}" alt="" width="120"></p>@endif
    <label>Status <select name="status" required><option value="active" @selected(old('status', $category->status ?: 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option></select></label>
    <label>Display order <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" required></label>
    <button type="submit">Save category</button>
</form>
@endsection

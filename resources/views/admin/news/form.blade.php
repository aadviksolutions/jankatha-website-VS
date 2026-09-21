@extends('admin.layout')

@section('title', $news->exists ? 'Edit News' : 'Add News')

@section('content')
<h1>{{ $news->exists ? 'Edit News' : 'Add News' }}</h1>
<form method="POST" action="{{ $news->exists ? route('admin.news.update', $news) : route('admin.news.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($news->exists) @method('PUT') @endif
    <label>Headline <input type="text" name="headline" value="{{ old('headline', $news->headline) }}" required></label>
    <label>Category <select name="category_id" required><option value="">Choose category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $news->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
    <label>Short description <textarea name="short_description">{{ old('short_description', $news->short_description) }}</textarea></label>
    <label>Full content <textarea name="content" rows="12" required>{{ old('content', $news->content) }}</textarea></label>
    <label>Featured image <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"></label>
    @if ($news->featured_image)<p><img src="{{ asset('storage/'.$news->featured_image) }}" alt="" width="180"></p>@endif
    <label>Location <input type="text" name="location" value="{{ old('location', $news->location) }}"></label>
    <label>District <input type="text" name="district" value="{{ old('district', $news->district) }}"></label>
    <label>State <input type="text" name="state" value="{{ old('state', $news->state) }}"></label>
    <label>Tags <input type="text" name="tags" value="{{ old('tags', $news->tags) }}"></label>
    <label>Video URL <input type="url" name="video_url" value="{{ old('video_url', $news->video_url) }}"></label>
    <label><input type="checkbox" name="is_breaking" value="1" @checked(old('is_breaking', $news->is_breaking))> Breaking News</label>
    <label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $news->is_featured))> Featured News</label>
    <label>SEO title <input type="text" name="seo_title" value="{{ old('seo_title', $news->seo_title) }}"></label>
    <label>SEO description <textarea name="seo_description">{{ old('seo_description', $news->seo_description) }}</textarea></label>
    <label>Status <select name="status" required>@foreach (['draft', 'scheduled', 'published', 'archived'] as $status)<option value="{{ $status }}" @selected(old('status', $news->status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
    <label>Publish date/time <input type="datetime-local" name="published_at" value="{{ old('published_at', $news->published_at?->format('Y-m-d\\TH:i')) }}"></label>
    <button type="submit">Save news</button>
</form>
@endsection

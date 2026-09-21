@extends('admin.layout')

@section('title', 'Preview News')

@section('content')
<p><a href="{{ route('admin.news.index') }}">Back to news</a></p>
<article>
    <h1>{{ $news->headline }}</h1>
    <p>{{ $news->short_description }}</p>
    @if ($news->featured_image)<img src="{{ asset('storage/'.$news->featured_image) }}" alt="{{ $news->headline }}" width="480">@endif
    <p>Category: {{ $news->category->name }} | Status: {{ $news->status }}</p>
    <div>{!! $news->content !!}</div>
</article>
@endsection

@extends('admin.layout')

@section('title', 'Preview News')

@section('content')
<p><a href="{{ route('admin.news.index') }}">Back to news</a></p>
<article>
    <h1>{{ $news->headline }}</h1>
    <p>{{ $news->short_description }}</p>
    @if ($news->featured_image)<p><img src="{{ $news->display_image }}" alt="{{ $news->headline }}" width="480" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';"></p>@endif
    <p>Category: {{ $news->category->name }} | Status: {{ $news->status }}</p>
    <div>{!! $news->content !!}</div>
</article>
@endsection

@extends('public.layout')
@section('content')
<section class="page-hero"><div class="container"><p class="section-kicker">JANKATHA / MEDIA</p><h1>{{ $mediaTitle }}</h1><p>{{ $mediaDescription }}</p></div></section>
<div class="container content-section">@if($news->isEmpty())<div class="empty-state">No {{ strtolower($mediaTitle) }} stories are published yet.</div>@elseif($mediaType === 'photo')<div class="news-grid">@foreach($news as $item)<x-public.news-card :news="$item" />@endforeach</div>@else<div class="video-strip">@foreach($news as $item)<a class="video-card" href="{{ route('news.show', $item->slug) }}">@if($item->featured_image)<img src="{{ asset('storage/'.$item->featured_image) }}" alt="">@endif<h3>{{ $item->headline }}</h3></a>@endforeach</div>@endif{{ $news->links() }}</div>
@endsection

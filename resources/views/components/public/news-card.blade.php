@props(['news', 'featured' => false])
@php
    $image = $news->display_image;
@endphp
<article class="news-card {{ $featured ? 'news-card-featured' : '' }}">
    <a href="{{ route('news.show', $news->slug) }}" class="news-card-media">
        <img src="{{ $image }}" alt="{{ $news->headline }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
    </a>
    <div class="news-card-body"><div class="eyebrow"><span>{{ $news->category?->name ?? 'News' }}</span>@if($news->location)<span>{{ $news->location }}</span>@endif</div><h3><a href="{{ route('news.show', $news->slug) }}">{{ $news->headline }}</a></h3>@if($news->short_description)<p>{{ Str::limit(strip_tags($news->short_description), 120) }}</p>@endif<div class="card-meta"><time datetime="{{ $news->published_at?->toIso8601String() }}">{{ $news->published_at?->diffForHumans() ?? 'Recently' }}</time>@if($news->is_breaking)<b class="breaking-label">Breaking</b>@endif</div></div>
</article>

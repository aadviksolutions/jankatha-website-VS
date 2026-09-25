@extends('public.layout')

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $news->headline,
    'description' => Str::limit(strip_tags($news->short_description ?: $news->content), 200),
    'image' => [$news->display_image],
    'datePublished' => ($news->published_at ?: $news->created_at)?->toIso8601String(),
    'dateModified' => $news->updated_at?->toIso8601String(),
    'author' => [[
        '@type' => $news->source_name ? 'Organization' : 'Person',
        'name' => $news->source_name ?: ($news->author?->name ?? 'Jankatha Desk'),
    ]],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Jankatha.com',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('images/placeholder.svg'),
        ],
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('news.show', $news->slug),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<article class="article-wrap">
    <header class="article-header">
        <div class="eyebrow">
            <span>{{ $news->category?->name ?? 'News' }}</span>
            @if ($news->is_breaking)
                <span class="badge-breaking">Breaking</span>
            @endif
        </div>
        <h1>{{ $news->headline }}</h1>
        @if ($news->short_description)
            <p class="article-dek">{{ $news->short_description }}</p>
        @endif
        <div class="article-meta">
            @if ($news->source_name)
                <span class="meta-source">स्रोत: <strong>{{ $news->source_name }}</strong></span>
            @else
                <span>By {{ $news->author?->name ?? 'Jankatha Desk' }}</span>
            @endif
            <time datetime="{{ $news->published_at?->toIso8601String() }}">{{ $news->published_at?->format('d M Y, h:i A') }}</time>
            @if ($news->location || $news->district)
                <span>{{ collect([$news->location, $news->district, $news->state])->filter()->unique()->join(' · ') }}</span>
            @endif
        </div>
    </header>

    <img class="article-image" src="{{ $news->display_image }}" alt="{{ $news->headline }}" loading="eager" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">

    @if ($safeVideoUrl)
        <div class="article-media">
            <p class="section-kicker">VIDEO REPORT</p>
            @if (str_starts_with($safeVideoUrl, '/storage/'))
                <video class="article-video" controls preload="metadata"><source src="{{ $safeVideoUrl }}"></video>
            @else
                <iframe class="article-video" src="{{ $safeVideoUrl }}" title="{{ $news->headline }}" loading="lazy" allowfullscreen></iframe>
            @endif
        </div>
    @endif

    <div class="article-content">
        {!! $news->content !!}
    </div>

    @if ($news->is_auto_fetched || $news->source_url || $news->source_name)
        <aside class="source-attribution-card" style="margin: 2rem 0; padding: 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #b91c1c; border-radius: 6px;">
            <p style="margin: 0 0 0.4rem 0; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                स्रोत एवं आभार (News Source Attribution)
            </p>
            <p style="margin: 0; font-size: 0.95rem; color: #1e293b; line-height: 1.5;">
                {{ $news->attribution_text ?: ("यह समाचार {$news->source_name} द्वारा प्रदान किया गया है।") }}
                @if ($news->source_url)
                    <br>
                    <a href="{{ $news->source_url }}" target="_blank" rel="nofollow noopener noreferrer" style="display: inline-block; margin-top: 0.5rem; color: #b91c1c; font-weight: 600; text-decoration: underline;">
                        मूल समाचार देखें (Read Original Story at {{ $news->source_name ?? 'Source' }}) ↗
                    </a>
                @endif
            </p>
        </aside>
    @endif

    @if ($news->tags)
        <div class="tag-list">
            @foreach (explode(',', $news->tags) as $tag)
                @if (trim($tag))
                    <span class="tag">#{{ trim($tag) }}</span>
                @endif
            @endforeach
        </div>
    @endif

    <div class="article-share">
        <p class="section-kicker">SHARE THIS STORY</p>
        <a class="text-link" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">Facebook ↗</a>
        <a class="text-link" href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($news->headline) }}" target="_blank" rel="noopener">X ↗</a>
        <a class="text-link" href="https://wa.me/?text={{ urlencode($news->headline.' '.url()->current()) }}" target="_blank" rel="noopener">WhatsApp ↗</a>
    </div>

    @if ($related->isNotEmpty())
        <section class="content-section" style="margin-top: 3rem;">
            <x-public.section-heading title="Related News" />
            <div class="news-grid">
                @foreach ($related as $item)
                    <x-public.news-card :news="$item" />
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection

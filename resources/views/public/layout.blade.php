<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? 'Jankatha.com | Real Stories. Real People.' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Real stories. Real people. Trusted local-first news from Jankatha.' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:type" content="{{ isset($news) ? 'article' : 'website' }}">
    <meta property="og:title" content="{{ $seoTitle ?? 'Jankatha.com' }}">
    <meta property="og:description" content="{{ $seoDescription ?? 'Real stories. Real people.' }}">
    @if (!empty($seoImage))<meta property="og:image" content="{{ $seoImage }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="Jankatha.com home">
            <span class="brand-j">J</span><span class="brand-name">ankatha</span><span class="brand-dot"></span><span class="brand-com">com</span>
            <small>REAL STORIES. REAL PEOPLE.</small>
        </a>
        <nav class="desktop-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}">Home</a><a href="{{ route('news.index') }}">Latest News</a><a href="{{ route('local-news') }}">Local News</a><a href="{{ route('photo-news') }}">Photo News</a><a href="{{ route('video-news') }}">Video News</a>
            <details class="category-menu"><summary>Categories</summary><div class="category-dropdown">@foreach ($navCategories as $category)<a href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>@endforeach</div></details>
            <a class="search-link" href="{{ route('search') }}" aria-label="Search news">Search</a>
            <a class="submit-button" href="{{ route('submit-news') }}">अपनी खबर भेजें</a>
        </nav>
        <div class="mobile-actions"><a href="{{ route('search') }}" class="icon-button" aria-label="Search">⌕</a><button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-menu-toggle><span></span><span></span><span></span></button></div>
    </div>
    <div class="mobile-drawer" data-mobile-drawer aria-hidden="true"><div class="drawer-backdrop" data-menu-close></div><aside class="drawer-panel"><button class="drawer-close" type="button" aria-label="Close menu" data-menu-close>Close</button><nav aria-label="Mobile navigation"><a href="{{ route('home') }}">Home</a><a href="{{ route('news.index') }}">Latest News</a><a href="{{ route('local-news') }}">Local News</a><a href="{{ route('photo-news') }}">Photo News</a><a href="{{ route('video-news') }}">Video News</a><a href="{{ route('breaking-news') }}">Breaking News</a><strong>Categories</strong>@foreach ($navCategories as $category)<a href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>@endforeach<a class="submit-button" href="{{ route('submit-news') }}">अपनी खबर भेजें</a></nav></aside></div>
</header>
@if ($breakingHeadlines->isNotEmpty())<div class="breaking-strip"><div class="container breaking-inner"><strong>BREAKING NEWS</strong><div class="breaking-track">@foreach ($breakingHeadlines as $breaking)<a href="{{ route('news.show', $breaking->slug) }}">{{ $breaking->headline }}</a>@endforeach</div></div></div>@endif
<main>@yield('content')</main>
<footer class="site-footer"><div class="container footer-grid"><div><a class="brand brand-light" href="{{ route('home') }}"><span class="brand-j">J</span><span class="brand-name">ankatha</span><span class="brand-dot"></span><span class="brand-com">com</span><small>REAL STORIES. REAL PEOPLE.</small></a><p>Local-first journalism for real communities.</p></div><div><h3>Explore</h3><a href="{{ route('about') }}">About</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('submit-news') }}">Submit News</a></div><div><h3>Information</h3><a href="#">Privacy Policy</a><a href="#">Terms</a><a href="#">Disclaimer</a></div><div><h3>Follow</h3><p class="social-placeholders"><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="Instagram">ig</a><a href="#" aria-label="YouTube">yt</a></p></div></div><div class="container footer-bottom"><span>© {{ date('Y') }} Jankatha.com</span><span>Built around real people and real places.</span></div></footer>
</body>
</html>

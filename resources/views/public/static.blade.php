@extends('public.layout')
@section('content')
<section class="page-hero"><div class="container"><p class="section-kicker">JANKATHA.COM</p><h1>{{ $pageTitle }}</h1><p>Real stories. Real people.</p></div></section>
<section class="content-section"><div class="container article-wrap"><div class="article-content">@if($pageKey === 'about')<p>Jankatha is a local-first news portal built around the people and places that shape everyday life. We make room for verified reporting, community voices and the context behind the headline.</p><p>Our newsroom works to keep reporting clear, useful and accountable.</p>@else<h2>Talk to the Jankatha team.</h2><p>For story tips, corrections and newsroom queries, reach us through the Jankatha editorial desk.</p><p><strong>Email:</strong> newsroom@jankatha.com</p><p>We do not publish private contributor information without consent.</p>@endif</div></div></section>
@endsection

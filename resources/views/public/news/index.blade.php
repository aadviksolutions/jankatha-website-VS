@extends('public.layout')
@section('content')
<section class="page-hero"><div class="container"><p class="section-kicker">JANKATHA / NEWSROOM</p><h1>{{ $pageTitle }}</h1><p>Clear, useful reporting from the places and people that matter.</p></div></section>
<div class="container listing-layout"><section><div class="listing-stack">@forelse($news as $item)<x-public.news-card :news="$item" />@empty<div class="empty-state"><h2>No published stories yet.</h2><p>Check back soon for verified reporting from Jankatha.</p></div>@endforelse</div>{{ $news->links() }}</section><aside class="sidebar"><h2>Latest updates</h2>@foreach($navCategories->take(8) as $category)<a class="text-link" href="{{ route('category.show', $category->slug) }}">{{ $category->name }} <span>→</span></a>@endforeach</aside></div>
@endsection

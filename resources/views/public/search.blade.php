@extends('public.layout')
@section('content')
<section class="page-hero"><div class="container"><p class="section-kicker">JANKATHA / DISCOVER</p><h1>Search News</h1><form class="filter-bar" method="GET"><input name="q" value="{{ $term }}" placeholder="Search headlines, places, topics" autofocus><button type="submit">Search</button></form></div></section>
<div class="container content-section"><p class="section-kicker">{{ $term ? $news->total().' RESULTS FOR “'.$term.'”' : 'SEARCH THE NEWSROOM' }}</p><div class="listing-stack">@forelse($news as $item)<x-public.news-card :news="$item" />@empty<div class="empty-state"><h2>No results found.</h2><p>Try another headline, place or topic.</p></div>@endforelse</div>{{ $news->links() }}</div>
@endsection

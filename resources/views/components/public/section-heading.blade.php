@props(['title', 'link' => null, 'linkText' => 'View all'])
<div class="section-heading"><div><p class="section-kicker">JANKATHA / {{ strtoupper($title) }}</p><h2>{{ $title }}</h2></div>@if($link)<a href="{{ $link }}" class="text-link">{{ $linkText }} <span>→</span></a>@endif</div>

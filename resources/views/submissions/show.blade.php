@extends('submissions.layout')
@section('title', $submission->headline)
@section('content')
<div class="panel"><a href="{{ route('my-submissions.index') }}">मेरी खबरों पर वापस जाएं</a><h1>{{ $submission->headline }}</h1><p class="status">Status: {{ $submission->status }}</p><p>{{ $submission->description }}</p><p>Category: {{ $submission->category->name }} | Location: {{ $submission->location }}</p><p>Submitted: {{ $submission->created_at->format('Y-m-d H:i') }}</p>@if($submission->editor_note)<p class="notice">Editor note: {{ $submission->editor_note }}</p>@endif @if($submission->status === 'needs_more_information')<a class="button" href="{{ route('my-submissions.edit', $submission) }}">अधिक जानकारी दें</a>@endif @if($submission->published_news_id)<p>यह खबर प्रकाशित हो चुकी है।</p>@endif</div>
<div class="panel"><h2>Media</h2>@forelse($submission->media as $media)<p><a href="{{ asset('storage/'.$media->file_path) }}" target="_blank" rel="noopener">{{ $media->file_name }}</a> ({{ $media->media_type }})</p>@empty<p>No media attached.</p>@endforelse</div>
<div class="panel"><h2>Status history</h2><ul>@foreach($submission->statusHistory as $history)<li>{{ $history->created_at?->format('Y-m-d H:i') }}: {{ $history->old_status ?: 'new' }} -> {{ $history->new_status }} @if($history->note)({{ $history->note }})@endif</li>@endforeach</ul></div>
@endsection

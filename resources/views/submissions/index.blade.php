@extends('submissions.layout')
@section('title', 'मेरी खबरें')
@section('content')
<div class="panel"><h1>मेरी खबरें</h1><p>अपनी भेजी गई खबरों की स्थिति और संपादकीय प्रतिक्रिया देखें।</p><a class="button" href="{{ route('my-submissions.create') }}">अपनी खबर भेजें</a></div>
<div class="grid">@foreach ($stats as $label => $value)<div class="panel"><strong>{{ ucwords(str_replace('_', ' ', $label)) }}</strong><div><big>{{ $value }}</big></div></div>@endforeach</div>
<div class="panel"><h2>सबमिशन सूची</h2><table><thead><tr><th>Headline</th><th>Category</th><th>Submitted</th><th>Status</th><th>Updated</th><th>Editor note</th></tr></thead><tbody>
@forelse ($submissions as $submission)<tr><td><a href="{{ route('my-submissions.show', $submission) }}">{{ $submission->headline }}</a></td><td>{{ $submission->category->name }}</td><td>{{ $submission->created_at->format('Y-m-d') }}</td><td class="status">{{ $submission->status }}</td><td>{{ $submission->updated_at->format('Y-m-d H:i') }}</td><td>{{ $submission->editor_note ?: '-' }}</td></tr>@empty<tr><td colspan="6">अभी कोई सबमिशन नहीं है।</td></tr>@endforelse
</tbody></table>{{ $submissions->links() }}</div>
@endsection

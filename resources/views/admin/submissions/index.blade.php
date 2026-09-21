@extends('admin.layout')
@section('title', 'Submission Moderation')
@section('content')
<h1>Submission Moderation</h1>
<form method="GET"><select name="status"><option value="">All statuses</option>@foreach(['pending','under_review','needs_more_information','verified','approved','rejected','published'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select><button type="submit">Filter</button></form>
<table><thead><tr><th>Headline</th><th>Contributor</th><th>Category</th><th>Location</th><th>Submitted</th><th>Status</th></tr></thead><tbody>@forelse($submissions as $submission)<tr><td><a href="{{ route('admin.submissions.show', $submission) }}">{{ $submission->headline }}</a></td><td>{{ $submission->user->name }}</td><td>{{ $submission->category->name }}</td><td>{{ $submission->location }}</td><td>{{ $submission->created_at->format('Y-m-d') }}</td><td>{{ $submission->status }}</td></tr>@empty<tr><td colspan="6">No submissions found.</td></tr>@endforelse</tbody></table>{{ $submissions->links() }}
@endsection

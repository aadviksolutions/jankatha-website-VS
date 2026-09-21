@extends('submissions.layout')
@section('title', $submission->exists ? 'अतिरिक्त जानकारी भेजें' : 'अपनी खबर भेजें')
@section('content')
<div class="panel"><h1>{{ $submission->exists ? 'अतिरिक्त जानकारी भेजें' : 'अपनी खबर भेजें' }}</h1><p class="notice">आपके द्वारा भेजी गई जानकारी संपादकीय समीक्षा और आवश्यक सत्यापन के बाद ही प्रकाशित की जाएगी।</p></div>
<form class="panel grid" method="POST" action="{{ $submission->exists ? route('my-submissions.update', $submission) : route('my-submissions.store') }}" enctype="multipart/form-data">
@csrf @if($submission->exists) @method('PUT') @endif
<label class="full">News Headline <input name="headline" value="{{ old('headline', $submission->headline) }}" required></label>
<label class="full">Full News Details <textarea name="description" required>{{ old('description', $submission->description) }}</textarea></label>
<label>Category <select name="category_id" required><option value="">Select</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $submission->category_id)===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></label>
<label>Location <input name="location" value="{{ old('location', $submission->location) }}" required></label>
<label>District <input name="district" value="{{ old('district', $submission->district) }}"></label>
<label>State <input name="state" value="{{ old('state', $submission->state) }}"></label>
<label>Event Date <input type="date" name="event_date" value="{{ old('event_date', $submission->event_date?->format('Y-m-d')) }}"></label>
<label>Event Time <input type="time" name="event_time" value="{{ old('event_time', $submission->event_time?->format('H:i')) }}"></label>
<label class="full">Photos <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>
<label class="full">Video files <input type="file" name="videos[]" accept="video/mp4,video/quicktime,video/webm" multiple></label>
<label class="full">Documents <input type="file" name="documents[]" accept="application/pdf" multiple></label>
<label class="full">YouTube/Video URL <input type="url" name="video_url" value="{{ old('video_url', $submission->video_url) }}"></label>
<label class="full">Source/Reference Information <textarea name="source_information">{{ old('source_information', $submission->source_information) }}</textarea></label>
<label>Contributor Name <input name="contributor_name" value="{{ old('contributor_name', $submission->contributor_name ?: auth()->user()->name) }}" required></label>
<label>Mobile <input name="mobile" value="{{ old('mobile', $submission->mobile ?: auth()->user()->mobile) }}" required></label>
<label>Email <input type="email" name="email" value="{{ old('email', $submission->email ?: auth()->user()->email) }}"></label>
<label class="full"><input type="checkbox" name="consent" value="1" required> मैं पुष्टि करता/करती हूं कि दी गई जानकारी सही है और संपादकीय समीक्षा के लिए सहमत हूं।</label>
<button type="submit">{{ $submission->exists ? 'जानकारी अपडेट करें' : 'खबर भेजें' }}</button>
</form>
@endsection

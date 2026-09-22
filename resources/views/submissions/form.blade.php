@extends('submissions.layout')
@section('title', $submission->exists ? 'अतिरिक्त जानकारी भेजें' : 'अपनी खबर भेजें')
@section('content')
<div class="panel"><h1>{{ $submission->exists ? 'अतिरिक्त जानकारी भेजें' : 'अपनी खबर भेजें' }}</h1><p class="notice">आपके द्वारा भेजी गई जानकारी संपादकीय समीक्षा और आवश्यक सत्यापन के बाद ही प्रकाशित की जाएगी।</p></div>
<form class="panel grid" method="POST" action="{{ $submission->exists ? route('my-submissions.update', $submission) : (auth()->check() ? route('my-submissions.store') : route('submit-news.store')) }}" enctype="multipart/form-data">
@csrf @if($submission->exists) @method('PUT') @endif
<label class="full">News Headline <input name="headline" value="{{ old('headline', $submission->headline) }}" placeholder="उदा. बिलासपुर में नई सड़क का लोकार्पण संपन्न" required></label>
<label class="full">Full News Details <textarea name="description" placeholder="पूरी खबर का विवरण यहाँ विस्तार से लिखें..." required>{{ old('description', $submission->description) }}</textarea></label>
<label>Category <select name="category_id" required><option value="">Select Category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $submission->category_id)===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></label>
<label>Location / स्थान <input name="location" value="{{ old('location', $submission->location) }}" placeholder="उदा. मंगला चौक / गोलबाजार" required></label>
<label>District / ज़िला <input name="district" value="{{ old('district', $submission->district) }}" placeholder="उदा. बिलासपुर / रायपुर"></label>
<label>State / राज्य <input name="state" value="{{ old('state', $submission->state ?? 'छत्तीसगढ़') }}" placeholder="उदा. छत्तीसगढ़"></label>
<label>Event Date / घटना की तारीख <input type="date" name="event_date" value="{{ old('event_date', $submission->event_date?->format('Y-m-d')) }}"></label>
<label>Event Time / घटना का समय <input type="time" name="event_time" value="{{ old('event_time', $submission->event_time?->format('H:i')) }}"></label>
<label class="full">Photos (तस्वीरें) <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>
<label class="full">Video files (वीडियो फ़ाइल) <input type="file" name="videos[]" accept="video/mp4,video/quicktime,video/webm" multiple></label>
<label class="full">Documents (दस्तावेज़ / प्रेस नोट) <input type="file" name="documents[]" accept="application/pdf" multiple></label>
<label class="full">YouTube / Video URL <input type="url" name="video_url" value="{{ old('video_url', $submission->video_url) }}" placeholder="https://youtube.com/watch?v=..."></label>
<label class="full">Source / Reference Information (स्रोत / संदर्भ) <textarea name="source_information" placeholder="घटना के प्रत्यक्षदर्शी, संदर्भ या आधिकारिक स्रोत की जानकारी...">{{ old('source_information', $submission->source_information) }}</textarea></label>
<label>Contributor Name / संवाददाता का नाम <input name="contributor_name" value="{{ old('contributor_name', $submission->contributor_name ?: auth()->user()?->name) }}" placeholder="आपका नाम" required></label>
<label>Mobile Number / मोबाइल नंबर <input name="mobile" value="{{ old('mobile', $submission->mobile ?: auth()->user()?->mobile) }}" placeholder="उदा. 9876543210" required></label>
<label>Email Address / ईमेल <input type="email" name="email" value="{{ old('email', $submission->email ?: auth()->user()?->email) }}" placeholder="name@example.com"></label>
<label class="full"><input type="checkbox" name="consent" value="1" required> मैं पुष्टि करता/करती हूं कि दी गई जानकारी सत्य एवं प्रामाणिक है और Jankatha संपादकीय समीक्षा व प्रकाशन के नियमों से सहमत हूं।</label>
<button type="submit">{{ $submission->exists ? 'जानकारी अपडेट करें' : 'खबर सबमिट करें (Submit News)' }}</button>
</form>
@endsection

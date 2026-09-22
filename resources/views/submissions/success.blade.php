@extends('submissions.layout')
@section('title', 'खबर सफलतापूर्वक प्राप्त हुई')
@section('content')
<div class="panel" style="text-align: center; padding: 3rem 1.5rem;">
    <div style="font-size: 3rem; color: #16a34a; margin-bottom: 1rem;">✓</div>
    <h1 style="color: var(--navy); margin-bottom: 0.5rem;">आपकी खबर सफलतापूर्वक प्राप्त हो गई है!</h1>
    <p style="font-size: 1.1rem; color: #475569; max-width: 650px; margin: 0 auto 1.5rem;">
        धन्यवाद! आपकी खबर <strong>समीक्षा के लिए Jankatha संपादकीय डेस्क</strong> को भेज दी गई है।
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; max-width: 500px; margin: 0 auto 2rem; text-align: left;">
        <p style="margin: 0.4rem 0;"><strong>संदर्भ संख्या (Reference ID):</strong> <span style="font-family: monospace; font-size: 1.1rem; color: var(--accent);">JKC-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</span></p>
        <p style="margin: 0.4rem 0;"><strong>शीर्षक (Headline):</strong> {{ $submission->headline }}</p>
        <p style="margin: 0.4rem 0;"><strong>स्थान (Location):</strong> {{ $submission->location }}</p>
        <p style="margin: 0.4rem 0;"><strong>स्थिति (Status):</strong> <span class="status" style="background: #fff3ed; padding: 0.2rem 0.6rem; border-radius: 4px;">संपादकीय समीक्षाधीन (Under Review)</span></p>
    </div>

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}" class="button secondary">मुख्य पृष्ठ पर जाएँ (Go to Homepage)</a>
        <a href="{{ route('submit-news') }}" class="button">दूसरी खबर भेजें (Submit Another News)</a>
    </div>
</div>
@endsection

@extends('admin.layout')

@section('title', $source->exists ? 'Edit News Source' : 'Add News Source')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $source->exists ? 'Edit News Source: '.$source->name : 'Add New News Source' }}</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Configure feed details, fetch frequency, priority, and location mappings.</p>
    </div>
    <a href="{{ route('admin.sources.index') }}" class="btn btn-secondary">← Back to Sources</a>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ $source->exists ? route('admin.sources.update', $source) : route('admin.sources.store') }}">
        @csrf
        @if ($source->exists)
            @method('PUT')
        @endif

        <div class="grid-2">
            <div class="form-group">
                <label for="name">Source Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $source->name) }}" required placeholder="e.g. Navbharat Times, Patrika, PIB">
            </div>

            <div class="form-group">
                <label for="source_type">Source Type *</label>
                <select id="source_type" name="source_type" required>
                    <option value="rss" @selected(old('source_type', $source->source_type) === 'rss')>RSS Feed (XML / Atom)</option>
                    <option value="json_api" @selected(old('source_type', $source->source_type) === 'json_api')>JSON API Feed</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="feed_url">Feed URL (RSS or JSON Endpoint) *</label>
            <input type="url" id="feed_url" name="feed_url" value="{{ old('feed_url', $source->feed_url) }}" required placeholder="https://example.com/rss/news.xml">
            <small style="color: var(--admin-muted); display: block; margin-top: 0.25rem;">Must be an approved public RSS or JSON feed URL.</small>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="url">Source Website URL</label>
                <input type="url" id="url" name="url" value="{{ old('url', $source->url) }}" placeholder="https://example.com">
            </div>

            <div class="form-group">
                <label for="category_id">Default Category</label>
                <select id="category_id" name="category_id">
                    <option value="">-- Auto-detect from content / tags --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('category_id', $source->category_id) === (string) $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="{{ old('state', $source->state ?: 'Chhattisgarh') }}" placeholder="Chhattisgarh">
            </div>

            <div class="form-group">
                <label for="district">District</label>
                <input type="text" id="district" name="district" value="{{ old('district', $source->district) }}" placeholder="e.g. Raipur, Bilaspur, Bastar">
            </div>

            <div class="form-group">
                <label for="city">City / Locality</label>
                <input type="text" id="city" name="city" value="{{ old('city', $source->city) }}" placeholder="e.g. Raipur, Bhilai">
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label for="language">Language</label>
                <select id="language" name="language" required>
                    <option value="hi" @selected(old('language', $source->language) === 'hi')>Hindi (hi)</option>
                    <option value="en" @selected(old('language', $source->language) === 'en')>English (en)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fetch_frequency_minutes">Fetch Frequency (Minutes)</label>
                <select id="fetch_frequency_minutes" name="fetch_frequency_minutes" required>
                    <option value="5" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 5)>Every 5 minutes</option>
                    <option value="10" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 10)>Every 10 minutes</option>
                    <option value="15" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 15)>Every 15 minutes</option>
                    <option value="30" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 30)>Every 30 minutes</option>
                    <option value="60" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 60)>Every 1 hour</option>
                    <option value="120" @selected((int) old('fetch_frequency_minutes', $source->fetch_frequency_minutes) === 120)>Every 2 hours</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Priority (0 - 100)</label>
                <input type="number" id="priority" name="priority" value="{{ old('priority', $source->priority ?: 0) }}" min="0" max="100" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="logo_url">Source Logo URL (Optional)</label>
                <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', $source->logo_url) }}" placeholder="https://example.com/logo.png">
            </div>

            <div class="form-group">
                <label for="attribution_text">Attribution Text</label>
                <input type="text" id="attribution_text" name="attribution_text" value="{{ old('attribution_text', $source->attribution_text) }}" placeholder="Source: Agency / Publication Name">
            </div>
        </div>

        <div class="form-group" style="margin-top: 0.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $source->is_active ?? true))>
                <span>Enable automatic fetching for this source (Active)</span>
            </label>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ $source->exists ? 'Update News Source' : 'Save News Source' }}</button>
            <a href="{{ route('admin.sources.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

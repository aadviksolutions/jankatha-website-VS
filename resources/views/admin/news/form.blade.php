@extends('admin.layout')

@section('title', $news->exists ? 'Edit News Story' : 'Add News Story')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $news->exists ? 'Edit News Story' : 'Add News Story' }}</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Manual editorial publishing with full control over categories, locations, media, and attribution.</p>
    </div>
    <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">← Back to News</a>
</div>

<div class="card" style="max-width: 900px;">
    <form method="POST" action="{{ $news->exists ? route('admin.news.update', $news) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($news->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="headline">Headline *</label>
            <input type="text" id="headline" name="headline" value="{{ old('headline', $news->headline) }}" required placeholder="Enter compelling headline">
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Choose category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $news->category_id) === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="status">Publication Status *</label>
                <select id="status" name="status" required>
                    @foreach (['draft' => 'Draft', 'pending_review' => 'Pending Review', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('status', $news->status) === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description (Dek / Lead Summary)</label>
            <textarea id="short_description" name="short_description" rows="3" placeholder="Brief summary for social media and card excerpts">{{ old('short_description', $news->short_description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="content">Full Article Content *</label>
            <textarea id="content" name="content" rows="12" required placeholder="Write or paste full article body...">{{ old('content', $news->content) }}</textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="featured_image">Featured Image</label>
                <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/webp">
                @if ($news->featured_image)
                    <div style="margin-top: 0.5rem;">
                        <img src="{{ $news->display_image }}" alt="" width="160" style="border-radius: 4px; border: 1px solid var(--admin-border);" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="published_at">Publish Date & Time</label>
                <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $news->published_at?->format('Y-m-d\TH:i')) }}">
                <small style="color: var(--admin-muted);">Required if status is 'Scheduled'. Leave blank to publish immediately on status 'Published'.</small>
            </div>
        </div>

        <h3 style="margin-top: 1.5rem; font-size: 1.05rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem;">
            Location Tagging
        </h3>
        <div class="grid-4">
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="{{ old('state', $news->state ?: 'Chhattisgarh') }}" placeholder="Chhattisgarh">
            </div>
            <div class="form-group">
                <label for="district">District</label>
                <input type="text" id="district" name="district" value="{{ old('district', $news->district) }}" placeholder="e.g. Raipur, Bilaspur">
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="{{ old('city', $news->city) }}" placeholder="e.g. Raipur, Durg">
            </div>
            <div class="form-group">
                <label for="location">Locality / Landmark</label>
                <input type="text" id="location" name="location" value="{{ old('location', $news->location) }}" placeholder="e.g. Civil Lines">
            </div>
        </div>

        <h3 style="margin-top: 1.5rem; font-size: 1.05rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem;">
            Source & Attribution
        </h3>
        <div class="grid-3">
            <div class="form-group">
                <label for="source_name">Source Name</label>
                <input type="text" id="source_name" name="source_name" value="{{ old('source_name', $news->source_name) }}" placeholder="e.g. Navbharat Times, PIB">
            </div>
            <div class="form-group">
                <label for="source_url">Original Article URL</label>
                <input type="url" id="source_url" name="source_url" value="{{ old('source_url', $news->source_url) }}" placeholder="https://example.com/article">
            </div>
            <div class="form-group">
                <label for="attribution_text">Attribution Note</label>
                <input type="text" id="attribution_text" name="attribution_text" value="{{ old('attribution_text', $news->attribution_text) }}" placeholder="Source: Agency report">
            </div>
        </div>

        <h3 style="margin-top: 1.5rem; font-size: 1.05rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem;">
            Visibility & Media
        </h3>
        <div class="grid-3">
            <div class="form-group">
                <label for="video_url">Video Report URL (YouTube / Video)</label>
                <input type="url" id="video_url" name="video_url" value="{{ old('video_url', $news->video_url) }}" placeholder="https://youtube.com/...">
            </div>
            <div class="form-group">
                <label for="tags">Tags (Comma-separated)</label>
                <input type="text" id="tags" name="tags" value="{{ old('tags', $news->tags) }}" placeholder="chhattisgarh, politics, breaking">
            </div>
            <div class="form-group" style="display: flex; gap: 1.5rem; align-items: center; padding-top: 1.5rem;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="checkbox" name="is_breaking" value="1" @checked(old('is_breaking', $news->is_breaking))>
                    <strong>★ Breaking News</strong>
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $news->is_featured))>
                    <span>Hero Featured</span>
                </label>
            </div>
        </div>

        <h3 style="margin-top: 1.5rem; font-size: 1.05rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem;">
            SEO Settings
        </h3>
        <div class="grid-2">
            <div class="form-group">
                <label for="seo_title">SEO Meta Title</label>
                <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $news->seo_title) }}" placeholder="Custom meta title for Google search">
            </div>
            <div class="form-group">
                <label for="seo_description">SEO Meta Description</label>
                <textarea id="seo_description" name="seo_description" rows="2" placeholder="Custom meta description (max 160 chars)">{{ old('seo_description', $news->seo_description) }}</textarea>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ $news->exists ? 'Update Story' : 'Save Story' }}</button>
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

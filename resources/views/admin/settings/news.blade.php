@extends('admin.layout')

@section('title', 'Automatic News Settings')

@section('content')
<div class="page-header">
    <div>
        <h1>Automatic News Settings</h1>
        <p style="margin: 0.25rem 0 0; color: var(--admin-muted);">Control feed ingestion behavior, publication modes, editorial approval gates, and AI assistance.</p>
    </div>
    <div>
        <form method="POST" action="{{ route('admin.settings.news.fetch-all') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('Trigger news fetch across all active sources immediately?')">
                ⚡ Fetch All Sources Now
            </button>
        </form>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2 style="margin-top: 0; font-size: 1.2rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
            Ingestion & Publication Rules
        </h2>

        <form method="POST" action="{{ route('admin.settings.news.update') }}">
            @csrf

            <div class="form-group">
                <label for="auto_news_enabled">Automatic News Ingestion</label>
                <select id="auto_news_enabled" name="auto_news_enabled" required>
                    <option value="1" @selected(($settings['auto_news_enabled'] ?? '1') === '1')>ON — Periodically fetch active sources</option>
                    <option value="0" @selected(($settings['auto_news_enabled'] ?? '1') === '0')>OFF — Disable automatic fetching</option>
                </select>
                <small style="color: var(--admin-muted);">When OFF, scheduled fetchers will skip ingesting new feed items.</small>
            </div>

            <div class="form-group" style="background: #fffbeb; padding: 1rem; border-radius: 6px; border: 1px solid #fef3c7;">
                <label for="auto_publish_enabled" style="color: #92400e;">
                    Auto-Publish Mode (Default: OFF)
                </label>
                <select id="auto_publish_enabled" name="auto_publish_enabled" required>
                    <option value="0" @selected(($settings['auto_publish_enabled'] ?? '0') === '0')>OFF — Ingest to 'Pending Review' for editorial approval (Recommended)</option>
                    <option value="1" @selected(($settings['auto_publish_enabled'] ?? '0') === '1')>ON — Automatically publish valid articles to live website</option>
                </select>
                <small style="color: #b45309; display: block; margin-top: 0.25rem;">
                    When OFF, all fetched external news must be reviewed and approved by an editor before appearing publicly.
                </small>
            </div>

            <div class="form-group">
                <label for="auto_news_require_editorial_approval">Editorial Approval Requirement</label>
                <select id="auto_news_require_editorial_approval" name="auto_news_require_editorial_approval" required>
                    <option value="1" @selected(($settings['auto_news_require_editorial_approval'] ?? '1') === '1')>YES — Require human editor sign-off before publishing</option>
                    <option value="0" @selected(($settings['auto_news_require_editorial_approval'] ?? '1') === '0')>NO — Allow direct auto-publish if validation rules pass</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="auto_news_fetch_frequency">Default Fetch Frequency</label>
                    <select id="auto_news_fetch_frequency" name="auto_news_fetch_frequency" required>
                        <option value="5" @selected(($settings['auto_news_fetch_frequency'] ?? '10') === '5')>5 Minutes</option>
                        <option value="10" @selected(($settings['auto_news_fetch_frequency'] ?? '10') === '10')>10 Minutes (Default)</option>
                        <option value="15" @selected(($settings['auto_news_fetch_frequency'] ?? '10') === '15')>15 Minutes</option>
                        <option value="30" @selected(($settings['auto_news_fetch_frequency'] ?? '10') === '30')>30 Minutes</option>
                        <option value="60" @selected(($settings['auto_news_fetch_frequency'] ?? '10') === '60')>1 Hour</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="auto_news_max_items_per_fetch">Max Articles Per Source / Fetch</label>
                    <input type="number" id="auto_news_max_items_per_fetch" name="auto_news_max_items_per_fetch" value="{{ $settings['auto_news_max_items_per_fetch'] ?? 20 }}" min="1" max="100" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="auto_news_default_category_id">Default Category</label>
                    <select id="auto_news_default_category_id" name="auto_news_default_category_id">
                        <option value="">-- Auto-detect --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($settings['auto_news_default_category_id'] ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="auto_news_default_language">Default Language</label>
                    <select id="auto_news_default_language" name="auto_news_default_language" required>
                        <option value="hi" @selected(($settings['auto_news_default_language'] ?? 'hi') === 'hi')>Hindi (hi)</option>
                        <option value="en" @selected(($settings['auto_news_default_language'] ?? 'hi') === 'en')>English (en)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                <label for="auto_news_ai_processing">AI Processing Assistance</label>
                <select id="auto_news_ai_processing" name="auto_news_ai_processing" required>
                    <option value="0" @selected(($settings['auto_news_ai_processing'] ?? '0') === '0')>OFF — Use standard sanitization and rule-based extraction</option>
                    <option value="1" @selected(($settings['auto_news_ai_processing'] ?? '0') === '1')>ON — Refine headlines & summaries with AI (requires API key in env)</option>
                </select>
                <small style="color: var(--admin-muted); display: block; margin-top: 0.25rem;">
                    Status: @if ($aiConfigured) <strong style="color: var(--admin-success);">AI API Key Configured</strong> @else <strong style="color: var(--admin-muted);">No AI API Key Set (Fallback active)</strong> @endif
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Save Settings</button>
        </form>
    </div>

    <div>
        <div class="card">
            <h2 style="margin-top: 0; font-size: 1.2rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                Vercel Scheduler & Serverless Cron
            </h2>
            <p style="font-size: 0.9rem; color: #334155;">
                Because Vercel runs ephemeral serverless containers, background worker daemons (like supervisor) do not run permanently. Automatic news fetching is powered by Vercel Cron.
            </p>

            <div style="background: #f1f5f9; padding: 1rem; border-radius: 6px; font-size: 0.88rem; margin: 1rem 0;">
                <div style="margin-bottom: 0.5rem;"><strong>Endpoint:</strong> <code>GET /api/cron/fetch-news</code></div>
                <div style="margin-bottom: 0.5rem;"><strong>Schedule:</strong> <code>Every 10 minutes (*/10 * * * *)</code> in <code>vercel.json</code></div>
                <div><strong>Authentication:</strong>
                    @if ($cronSecretSet)
                        <span class="badge badge-success">CRON_SECRET Active</span>
                    @else
                        <span class="badge badge-warning">CRON_SECRET not set in .env</span>
                    @endif
                </div>
            </div>

            <p style="font-size: 0.85rem; color: var(--admin-muted);">
                When configured, Vercel automatically sends the secret via <code>Authorization: Bearer &lt;CRON_SECRET&gt;</code>. Unauthorized requests are rejected with 401 Unauthorized.
            </p>
        </div>

        <div class="card">
            <h2 style="margin-top: 0; font-size: 1.2rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                Duplicate Detection Guardrails
            </h2>
            <ul style="font-size: 0.88rem; color: #334155; padding-left: 1.2rem; line-height: 1.6;">
                <li>Stable <strong>source_guid</strong> / entry ID indexing.</li>
                <li>Canonical <strong>source_url</strong> match protection.</li>
                <li>Normalized <strong>headline SHA-256 hash</strong> deduplication to catch identical stories across re-fetches.</li>
                <li>Automatic attribution retention on all imported stories.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

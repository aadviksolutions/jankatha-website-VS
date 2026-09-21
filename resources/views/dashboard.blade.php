<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $area }} | Jankatha</title></head>
<body>
    <main>
        <h1>Jankatha</h1>
        <h2>{{ $area }}</h2>
        <p>Welcome, {{ auth()->user()->name }}</p>
        <p>Current Role: <strong>{{ auth()->user()->role }}</strong></p>
        @if (auth()->user()->hasRole('citizen', 'contributor'))
            <p><a href="{{ route('my-submissions.create') }}">अपनी खबर भेजें</a> <a href="{{ route('my-submissions.index') }}">मेरी खबरें</a></p>
        @endif
        @isset($stats)
            <section aria-label="News CMS statistics">
                <h3>News CMS</h3>
                <ul>
                    <li>Total News: {{ $stats['total'] }}</li>
                    <li>Published: {{ $stats['published'] }}</li>
                    <li>Drafts: {{ $stats['drafts'] }}</li>
                    <li>Scheduled: {{ $stats['scheduled'] }}</li>
                    <li>Breaking News: {{ $stats['breaking'] }}</li>
                    <li>Categories: {{ $stats['categories'] }}</li>
                </ul>
                @if (auth()->user()->hasRole('super_admin', 'admin', 'editor'))
                    <a href="{{ route('admin.news.index') }}">Manage news</a>
                @endif
                @if (auth()->user()->hasRole('super_admin', 'admin'))
                    <a href="{{ route('admin.categories.index') }}">Manage categories</a>
                @endif
            </section>
        @endisset
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin CMS') | Jankatha</title>
    <style>
        :root {
            --admin-bg: #f8fafc;
            --admin-surface: #ffffff;
            --admin-border: #e2e8f0;
            --admin-text: #0f172a;
            --admin-muted: #64748b;
            --admin-primary: #b91c1c;
            --admin-primary-hover: #991b1b;
            --admin-success: #15803d;
            --admin-warning: #b45309;
            --admin-danger: #dc2626;
        }
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--admin-bg); color: var(--admin-text); margin: 0; line-height: 1.5; }
        header { background: #1e293b; color: #fff; padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
        header a { color: #f1f5f9; text-decoration: none; font-size: 0.95rem; }
        header a:hover { color: #fff; text-decoration: underline; }
        .brand-logo { font-size: 1.2rem; font-weight: 700; color: #fff !important; }
        .admin-nav { display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; }
        .admin-nav a.active { font-weight: 700; border-bottom: 2px solid #ef4444; padding-bottom: 2px; }
        main { max-width: 1300px; margin: 2rem auto; padding: 0 1.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        h1 { margin: 0; font-size: 1.6rem; font-weight: 700; }
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; font-size: 0.9rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all 0.15s; }
        .btn-primary { background: var(--admin-primary); color: #fff; }
        .btn-primary:hover { background: var(--admin-primary-hover); color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-danger { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .btn-danger:hover { background: #fecaca; }
        .btn-success { background: #dcfce7; color: #166534; border-color: #86efac; }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.8rem; }
        .card { background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .status-alert { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 500; }
        .error-alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; background: var(--admin-surface); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 1.5rem; }
        th, td { padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--admin-border); font-size: 0.9rem; }
        th { background: #f1f5f9; font-weight: 600; color: #475569; }
        tr:hover { background: #f8fafc; }
        .badge { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.35rem; font-weight: 600; font-size: 0.88rem; color: #334155; }
        input[type="text"], input[type="url"], input[type="number"], input[type="search"], select, textarea {
            width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--admin-border); border-radius: 6px; font-size: 0.92rem; font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .stat-card { background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.25rem; }
        .stat-val { font-size: 1.8rem; font-weight: 700; color: var(--admin-text); }
        .stat-lbl { font-size: 0.85rem; color: var(--admin-muted); font-weight: 500; text-transform: uppercase; }
        .filter-bar { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.25rem; background: var(--admin-surface); padding: 1rem; border-radius: 8px; border: 1px solid var(--admin-border); }
        .filter-bar input, .filter-bar select { width: auto; min-width: 160px; }
    </style>
</head>
<body>
    <header>
        <a class="brand-logo" href="{{ route('dashboard') }}">Jankatha CMS</a>
        <nav class="admin-nav">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.news.index') }}">All News</a>
            <a href="{{ route('admin.news.index', ['status' => 'pending_review']) }}">Pending Review</a>
            @if (auth()->user()->hasRole('super_admin', 'admin'))
                <a href="{{ route('admin.sources.index') }}">News Sources</a>
                <a href="{{ route('admin.logs.index') }}">Fetch Logs</a>
                <a href="{{ route('admin.categories.index') }}">Categories</a>
                <a href="{{ route('admin.settings.news') }}">News Settings</a>
            @endif
            <a href="{{ route('home') }}" target="_blank">View Site ↗</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline; margin: 0;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 0;">Logout</button>
            </form>
        </nav>
    </header>
    <main>
        @if (session('status'))
            <div class="status-alert" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error-alert" role="alert">
                <strong>Please correct the following errors:</strong>
                <ul style="margin: 0.5rem 0 0 1.2rem; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>

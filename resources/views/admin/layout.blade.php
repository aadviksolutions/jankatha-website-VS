<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CMS') | Jankatha</title>
</head>
<body>
    <header>
        <a href="{{ route('dashboard') }}"><strong>Jankatha</strong></a>
        <nav>
            <a href="{{ route('admin.news.index') }}">News</a>
            @if (auth()->user()->hasRole('super_admin', 'admin'))
                <a href="{{ route('admin.categories.index') }}">Categories</a>
            @endif
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    </header>
    <main>
        @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<div role="alert"><strong>Please correct the errors.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</body>
</html>

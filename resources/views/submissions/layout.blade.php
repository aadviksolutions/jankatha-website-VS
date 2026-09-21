<!doctype html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'अपनी खबर भेजें') | Jankatha</title>
    <style>
        :root { --navy:#10243e; --accent:#ef5b2a; --paper:#f7f9fc; --ink:#172235; }
        * { box-sizing:border-box; } body { margin:0; background:var(--paper); color:var(--ink); font:16px/1.5 system-ui,sans-serif; }
        header { background:var(--navy); color:#fff; padding:1rem 5vw; display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
        header a { color:#fff; } nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; } main { max-width:1100px; margin:2rem auto; padding:0 1rem; }
        .panel { background:#fff; border:1px solid #dce3ed; border-radius:8px; padding:1.25rem; margin-bottom:1rem; box-shadow:0 5px 18px #10243e0b; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; } label { display:grid; gap:.35rem; font-weight:600; } input, textarea, select { width:100%; padding:.7rem; border:1px solid #bdc9d8; border-radius:5px; font:inherit; } textarea { min-height:130px; resize:vertical; }
        .full { grid-column:1/-1; } button, .button { background:var(--accent); color:#fff; border:0; border-radius:5px; padding:.7rem 1rem; cursor:pointer; text-decoration:none; display:inline-block; } .secondary { background:var(--navy); }
        .notice { border-left:4px solid var(--accent); padding:.8rem 1rem; background:#fff3ed; } table { width:100%; border-collapse:collapse; background:#fff; } th,td { text-align:left; padding:.7rem; border-bottom:1px solid #e2e7ef; } .status { font-weight:700; color:var(--accent); }
        @media (max-width:650px) { table { display:block; overflow:auto; white-space:nowrap; } }
    </style>
</head>
<body>
<header><a href="{{ route('dashboard') }}"><strong>Jankatha</strong></a><nav><a href="{{ route('my-submissions.index') }}">मेरी खबरें</a><a href="{{ route('dashboard') }}">डैशबोर्ड</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form></nav></header>
<main>
@if (session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if ($errors->any())<div class="notice" role="alert"><strong>कृपया जानकारी जांचें।</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
</body>
</html>

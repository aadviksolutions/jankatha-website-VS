<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login | Jankatha</title></head>
<body>
    <main>
        <h1>Jankatha</h1>
        <h2>Login</h2>
        @if ($errors->any())<div role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
            <label>Password <input type="password" name="password" required></label>
            <label><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit">Login</button>
        </form>
        <p><a href="{{ route('register') }}">Create an account</a></p>
    </main>
</body>
</html>

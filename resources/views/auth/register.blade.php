<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register | Jankatha</title></head>
<body>
    <main>
        <h1>Jankatha</h1>
        <h2>Register</h2>
        @if ($errors->any())<div role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label>Name <input type="text" name="name" value="{{ old('name') }}" required autofocus></label>
            <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>Mobile <input type="text" name="mobile" value="{{ old('mobile') }}"></label>
            <label>Password <input type="password" name="password" required></label>
            <label>Confirm password <input type="password" name="password_confirmation" required></label>
            <button type="submit">Register</button>
        </form>
        <p><a href="{{ route('login') }}">Already registered? Login</a></p>
    </main>
</body>
</html>

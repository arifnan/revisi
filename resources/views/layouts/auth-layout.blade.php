<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img"> <!-- Menambahkan logo -->
        </div>
        <div class="auth-right">
            @yield('content')
        </div>
    </div>
</body>
</html>

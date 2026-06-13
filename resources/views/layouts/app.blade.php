<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
    @auth
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="{{ url('/dashboard') }}" class="font-bold text-lg">{{ config('app.name') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Logout</button>
            </form>
        </div>
    </nav>
    @endauth

    <main>
        @yield('content')
    </main>
</body>
</html>

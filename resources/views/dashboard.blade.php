@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}</h1>
    <p class="text-gray-600 mb-8">Ini adalah halaman dashboard untuk testing aplikasi CuacaTani.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-lg mb-2">User Info</h2>
            <p class="text-sm text-gray-600">Email: {{ auth()->user()->email }}</p>
            <p class="text-sm text-gray-600">Kota: {{ auth()->user()->city ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-lg mb-2">WeatherService</h2>
            <p class="text-sm text-gray-600">Gunakan <code>WeatherService</code> untuk fetch forecast.</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-lg mb-2">RecommendationService</h2>
            <p class="text-sm text-gray-600">Gunakan <code>RecommendationService</code> untuk rekomendasi aktivitas.</p>
        </div>
    </div>
</div>
@endsection

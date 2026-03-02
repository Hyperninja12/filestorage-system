<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Data Import') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style> body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; } </style>
    {{-- Page-specific styles (e.g. records table design) --}}
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <nav class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ route('records.index') }}" class="text-lg font-semibold text-gray-800">Data Import</a>
            <div class="flex gap-4">
                <a href="{{ route('records.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Records</a>
                <a href="{{ route('import.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Import CSV/Excel</a>
            </div>
        </div>
    </nav>
    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>

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
<body class="app-body text-gray-900 min-h-screen">
    <nav class="app-nav">
        <div class="app-nav-inner">
            <a href="{{ route('records.index') }}" class="app-nav-brand">Data Import</a>
            <div class="app-nav-links">
                <a href="{{ route('records.index') }}" class="app-nav-link app-nav-link-records">Records</a>
                <a href="{{ route('import.create') }}" class="app-nav-link app-nav-link-import">Import CSV/Excel</a>
            </div>
        </div>
    </nav>
    <main class="app-main">
        @if (session('success'))
            <div class="app-alert app-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="app-alert app-alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    <style>
        .app-body { background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%); min-height: 100vh; }
        .app-nav { background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); border-bottom: 1px solid rgba(226,232,240,0.8); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .app-nav-inner { max-width: 72rem; margin: 0 auto; padding: 0.875rem 1rem; display: flex; align-items: center; justify-content: space-between; }
        .app-nav-brand { font-size: 1.125rem; font-weight: 600; color: #1e293b; }
        .app-nav-links { display: flex; gap: 0.75rem; align-items: center; }
        .app-nav-link {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background 0.15s, color 0.15s, border-color 0.15s, box-shadow 0.15s;
        }
        .app-nav-link-records {
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: 1px solid #0284c7;
            box-shadow: 0 1px 2px rgba(14, 165, 233, 0.3);
        }
        .app-nav-link-records:hover { background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); box-shadow: 0 2px 4px rgba(14, 165, 233, 0.4); color: #fff; }
        .app-nav-link-import {
            color: #fff;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: 1px solid #4f46e5;
            box-shadow: 0 1px 2px rgba(99, 102, 241, 0.3);
        }
        .app-nav-link-import:hover { background: linear-gradient(135deg, #5558e3 0%, #4338ca 100%); box-shadow: 0 2px 4px rgba(99, 102, 241, 0.4); }
        .app-main { max-width: 72rem; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
        .app-alert { margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; }
        .app-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .app-alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .app-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4f46e5;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 0.5rem;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .app-back-btn:hover { background: #e0e7ff; border-color: #a5b4fc; color: #3730a3; }
        .app-back-btn svg { flex-shrink: 0; }
    </style>
</body>
</html>

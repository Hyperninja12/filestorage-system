<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unlock — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh; margin: 0;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(160deg, #E8F6F3 0%, #d4ede8 40%, #b8e4dc 100%);
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(14, 165, 233, 0.12), transparent),
                        radial-gradient(ellipse 60% 40% at 100% 100%, rgba(6, 182, 212, 0.08), transparent),
                        radial-gradient(ellipse 50% 30% at 0% 80%, rgba(14, 165, 233, 0.06), transparent);
            pointer-events: none;
        }
        .unlock-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 24rem;
            padding: 1.5rem;
            transition: opacity 0.25s ease-in-out, transform 0.25s ease-in-out, filter 0.25s ease-in-out;
        }
        .unlock-wrap.unlock-fadeout {
            opacity: 0;
            transform: scale(0.985);
            filter: blur(1px);
        }
        .unlock-card-ring {
            position: relative;
            border-radius: 1.25rem;
            padding: 3px;
            overflow: hidden;
        }
        .unlock-card-ring::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            left: -50%;
            top: -50%;
            background: conic-gradient(from 0deg, transparent 0deg 200deg, rgba(14, 165, 233, 0.5) 230deg, #0ea5e9 260deg, #06b6d4 290deg, transparent 320deg);
            animation: unlock-ring-spin 2.5s linear infinite;
        }
        .unlock-card-ring::after {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            left: -50%;
            top: -50%;
            background: conic-gradient(from 180deg, transparent 0deg 160deg, rgba(6, 182, 212, 0.6) 190deg, #06b6d4 220deg, #0ea5e9 250deg, transparent 280deg);
            animation: unlock-ring-spin-reverse 3s linear infinite;
        }
        .unlock-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: calc(1.25rem - 3px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            padding: 2.25rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        @keyframes unlock-ring-spin {
            to { transform: rotate(360deg); }
        }
        @keyframes unlock-ring-spin-reverse {
            to { transform: rotate(-360deg); }
        }
        .unlock-icon-wrap {
            width: 3.5rem; height: 3.5rem;
            margin: 0 auto 1.25rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            border-radius: 1rem;
            box-shadow: 0 8px 20px -4px rgba(14, 165, 233, 0.35);
        }
        .unlock-icon-wrap svg { width: 1.75rem; height: 1.75rem; color: #fff; }
        .unlock-title { font-size: 1.375rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem; text-align: center; letter-spacing: -0.02em; }
        .unlock-subtitle { font-size: 0.875rem; color: #64748b; margin: 0 0 1.75rem; text-align: center; }
        .unlock-input-wrap { position: relative; margin-bottom: 0.25rem; }
        .unlock-input {
            width: 100%; padding: 0.75rem 3rem 0.75rem 1rem;
            font-size: 1rem; border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .unlock-input::placeholder { color: #94a3b8; }
        .unlock-input:hover { background: #fff; border-color: #cbd5e1; }
        .unlock-input:focus {
            outline: none; background: #fff;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }
        .unlock-toggle-eye {
            position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);
            padding: 0.375rem; border: none; background: none; cursor: pointer;
            color: #64748b; border-radius: 0.5rem;
            transition: color 0.2s, background 0.2s;
        }
        .unlock-toggle-eye:hover { color: #0ea5e9; background: rgba(14, 165, 233, 0.08); }
        .unlock-toggle-eye svg { width: 1.25rem; height: 1.25rem; display: block; }
        .unlock-btn {
            width: 100%; margin-top: 1.25rem; padding: 0.75rem 1rem;
            font-size: 1rem; font-weight: 600; color: #fff;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none; border-radius: 0.75rem; cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(14, 165, 233, 0.4);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .unlock-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px -4px rgba(14, 165, 233, 0.45); }
        .unlock-btn:active { transform: translateY(0); }
        .unlock-btn:disabled { opacity: 0.85; cursor: not-allowed; transform: none; }
        .unlock-error {
            margin-top: 0.75rem; padding: 0.625rem 0.875rem;
            font-size: 0.8125rem; color: #b91c1c;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca; border-radius: 0.5rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .unlock-error::before {
            content: ''; width: 1rem; height: 1rem; flex-shrink: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23b91c1c'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E") center/contain no-repeat;
        }
        .unlock-loading {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(232, 246, 243, 0.92);
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
            opacity: 1;
            pointer-events: auto;
            transition: opacity 0.25s ease-in-out;
        }
        .unlock-loading.hidden { opacity: 0; pointer-events: none; }
        .unlock-loading-content { text-align: center; }
        .unlock-loading-spinner {
            width: 2.75rem; height: 2.75rem;
            border: 3px solid rgba(14, 165, 233, 0.2);
            border-top-color: #0ea5e9;
            border-radius: 50%;
            animation: unlock-spin 0.7s linear infinite;
            margin: 0 auto 1rem;
        }
        .unlock-loading-text { font-size: 0.9375rem; font-weight: 600; color: #334155; }
        @keyframes unlock-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="unlock-loading hidden" id="unlock-loading">
        <div class="unlock-loading-content">
            <div class="unlock-loading-spinner"></div>
            <div class="unlock-loading-text" id="unlock-loading-text">Unlocking...</div>
        </div>
    </div>
    <div class="unlock-wrap">
    <div class="unlock-card-ring">
    <div class="unlock-card">
        <div class="unlock-icon-wrap" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
        </div>
        <h1 class="unlock-title">Welcome back, Ma'am Jesty!
        <p class="unlock-subtitle">Enter your password to continue</p>
        <form id="unlock-form" action="{{ route('unlock.submit') }}" method="POST">
            @csrf
            <div class="unlock-input-wrap">
                <input type="password" name="password" id="unlock-password" class="unlock-input" placeholder="Enter password" required autofocus autocomplete="current-password">
                <button type="button" class="unlock-toggle-eye" id="unlock-toggle-eye" title="Show password" aria-label="Show password">
                    <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg id="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 15 3m0 0 18-18M6.228 6.228 21 3 21"/></svg>
                </button>
            </div>
            @error('password')
                <div class="unlock-error" role="alert">{{ $message }}</div>
            @enderror
            <button type="submit" class="unlock-btn" id="unlock-submit-btn">Unlock</button>
        </form>
    </div>
    </div>
    </div>
    <script>
        (function() {
            var input = document.getElementById('unlock-password');
            var btn = document.getElementById('unlock-toggle-eye');
            var iconEye = document.getElementById('icon-eye');
            var iconEyeSlash = document.getElementById('icon-eye-slash');
            if (!input || !btn) return;
            btn.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    iconEye.style.display = 'none';
                    iconEyeSlash.style.display = 'block';
                    btn.setAttribute('title', 'Hide password');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    iconEye.style.display = 'block';
                    iconEyeSlash.style.display = 'none';
                    btn.setAttribute('title', 'Show password');
                    btn.setAttribute('aria-label', 'Show password');
                }
            });

            var form = document.getElementById('unlock-form');
            var loadingEl = document.getElementById('unlock-loading');
            var wrap = document.querySelector('.unlock-wrap');
            var submitBtn = document.getElementById('unlock-submit-btn');
            if (form && loadingEl && submitBtn && wrap) {
                form.addEventListener('submit', function() {
                    wrap.classList.add('unlock-fadeout');
                    loadingEl.classList.remove('hidden');
                    submitBtn.disabled = true;
                });
            }
        })();
    </script>
</body>
</html>

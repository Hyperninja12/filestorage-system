<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Records') — {{ config('app.name') }}</title>
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
        }
    </style>
    {{-- Mga style nga specific sa page (pananglitan records table design) --}}
    @stack('styles')
</head>

<body class="app-body text-gray-900 min-h-screen">
    <div class="app-loading hidden" id="app-loading">
        <div class="app-loading-content">
            <div class="app-loading-spinner"></div>
            <div class="app-loading-text">Loading...</div>
        </div>
    </div>
    <nav class="app-nav">
        <div class="app-nav-inner">
            <a href="{{ route('records.index') }}" class="app-nav-brand">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="app-nav-logo">
                <span>Data Import</span>
            </a>
            <div class="app-nav-links">
                <a href="{{ route('records.index') }}" class="app-nav-icon-link {{ request()->routeIs('records.index') ? 'app-nav-icon-link-active' : '' }}" title="Records">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </a>
                <a href="{{ route('import.create') }}" class="app-nav-icon-link {{ request()->routeIs('import.create') ? 'app-nav-icon-link-active' : '' }}" title="Import CSV/Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                </a>
                <a href="{{ route('module-two.index') }}" class="app-nav-icon-link {{ request()->routeIs('module-two.index') ? 'app-nav-icon-link-active' : '' }}" title="New System">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                    </svg>
                </a>
                <form action="{{ route('lock') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="app-nav-icon-link" title="Lock System">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="app-main {{ session('just_unlocked') ? 'app-main-transition-in' : '' }}">
        @if (session('success'))
            <div class="app-alert app-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="app-alert app-alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    {{-- Global confirm dialog (replaces window.confirm for delete / remove-image forms) --}}
    <div id="app-confirm-modal" class="app-confirm-modal app-confirm-hidden" aria-hidden="true">
        <div class="app-confirm-backdrop" data-app-confirm-dismiss tabindex="-1"></div>
        <div id="app-confirm-panel" class="app-confirm-panel" role="alertdialog" aria-modal="true"
            aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message">
            <h2 id="app-confirm-title" class="app-confirm-title"></h2>
            <p id="app-confirm-message" class="app-confirm-message"></p>
            <div class="app-confirm-actions">
                <button type="button" class="app-confirm-btn app-confirm-btn-cancel"
                    data-app-confirm-dismiss>Cancel</button>
                <button type="button" class="app-confirm-btn app-confirm-btn-ok app-confirm-btn-ok-danger"
                    id="app-confirm-ok">Confirm</button>
            </div>
        </div>
    </div>

    <style>
        .app-body {
            background: #E8F6F3;
            min-height: 100vh;
        }

        .app-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .app-nav-inner {
            max-width: 72rem;
            margin: 0 auto;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .app-nav-brand {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .app-nav-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .app-nav-links {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .app-nav-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            color: #334155;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: rgba(241, 245, 249, 0.5);
            border: 1px solid rgba(226, 232, 240, 0.8);
            cursor: pointer;
            text-decoration: none;
        }

        .app-nav-icon-link:hover {
            background: #f1f5f9;
            color: #0ea5e9;
            transform: translateY(-1.5px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-color: #cbd5e1;
        }

        .app-nav-icon-link-active {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        .app-nav-icon-link svg {
            width: 1.5rem;
            height: 1.5rem;
            stroke-width: 1.75;
        }

        .app-main {
            max-width: 72rem;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
        }

        .app-alert {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: opacity 0.5s ease;
            opacity: 1;
        }

        .app-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .app-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }

        .app-back-btn:hover {
            background: #e0e7ff;
            border-color: #a5b4fc;
            color: #3730a3;
        }

        .app-back-btn svg {
            flex-shrink: 0;
        }

        .app-loading {
            position: fixed;
            inset: 0;
            background: rgba(232, 246, 243, 0.92);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .app-loading.hidden {
            display: none;
        }

        .app-loading-content {
            text-align: center;
        }

        .app-loading-spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 3px solid #e2e8f0;
            border-top-color: #0ea5e9;
            border-radius: 50%;
            animation: app-spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }

        .app-loading-text {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #334155;
        }

        /* Premium staggered entry animations */
        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-staggered {
            animation: pageEnter 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        @keyframes app-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .app-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 10080;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .app-confirm-hidden {
            display: none !important;
        }

        .app-confirm-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
        }

        .app-confirm-panel {
            position: relative;
            width: 100%;
            max-width: 22rem;
            padding: 1.5rem;
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
        }

        .app-confirm-panel--danger {
            border-top: 4px solid #dc2626;
        }

        .app-confirm-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem;
            line-height: 1.3;
        }

        .app-confirm-message {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0 0 1.25rem;
            line-height: 1.5;
            text-align: center;
            white-space: pre-line;
        }

        .app-confirm-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .app-confirm-btn {
            padding: 0.5rem 1.15rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .app-confirm-btn-cancel {
            background: #fff;
            color: #475569;
            border-color: #cbd5e1;
        }

        .app-confirm-btn-cancel:hover {
            background: #f8fafc;
        }

        .app-confirm-btn-ok-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #fff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
        }

        .app-confirm-btn-ok-danger:hover {
            opacity: 0.95;
        }

        .app-confirm-btn-ok-neutral {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
        }

        .app-confirm-btn-ok-neutral:hover {
            opacity: 0.95;
        }
    </style>
    <script>
        (function () {
            var loading = document.getElementById('app-loading');
            window.hideAppGlobalLoading = function () {
                if (loading) loading.classList.add('hidden');
            };
            var show = function () {
                if (loading) loading.classList.remove('hidden');
            };
            document.body.addEventListener('click', function (e) {
                var a = e.target.closest('a[href]');
                if (a && a.href && a.target !== '_blank' && !a.href.startsWith('javascript:') && a.getAttribute('data-no-app-loading') === null) {
                    try {
                        var url = new URL(a.href);
                        if (url.origin === location.origin) show();
                    } catch (_) { }
                }
            });
            document.body.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || form.tagName !== 'FORM' || form.target) return;
                // Import / add-record use modals; global loader would cover confirm dialogs (low z-index vs 9999).
                if (form.id === 'import-form' || form.id === 'record-create-form' || form.getAttribute('data-no-app-loading') !== null) return;
                show();
            });
        })();
    </script>
    <script>
        (function () {
            var modal = document.getElementById('app-confirm-modal');
            var panel = document.getElementById('app-confirm-panel');
            var titleEl = document.getElementById('app-confirm-title');
            var msgEl = document.getElementById('app-confirm-message');
            var okBtn = document.getElementById('app-confirm-ok');
            if (!modal || !panel || !titleEl || !msgEl || !okBtn) return;

            var pendingForm = null;

            function closeConfirmModal() {
                modal.classList.add('app-confirm-hidden');
                modal.setAttribute('aria-hidden', 'true');
                pendingForm = null;
            }

            function openConfirmModal() {
                if (typeof window.hideAppGlobalLoading === 'function') {
                    window.hideAppGlobalLoading();
                }
                modal.classList.remove('app-confirm-hidden');
                modal.setAttribute('aria-hidden', 'false');
                okBtn.focus();
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                if (form.getAttribute('data-app-confirm') !== '1') return;
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                pendingForm = form;
                titleEl.textContent = form.getAttribute('data-app-confirm-title') || 'Confirm';
                msgEl.textContent = form.getAttribute('data-app-confirm-message') || '';
                okBtn.textContent = form.getAttribute('data-app-confirm-ok') || 'Confirm';
                var variant = form.getAttribute('data-app-confirm-variant') || 'danger';
                okBtn.classList.remove('app-confirm-btn-ok-danger', 'app-confirm-btn-ok-neutral');
                panel.classList.toggle('app-confirm-panel--danger', variant === 'danger');
                if (variant === 'danger') {
                    okBtn.classList.add('app-confirm-btn-ok-danger');
                } else {
                    okBtn.classList.add('app-confirm-btn-ok-neutral');
                }
                openConfirmModal();
            }, true);

            okBtn.addEventListener('click', function () {
                if (!pendingForm) return;
                var f = pendingForm;
                pendingForm = null;
                closeConfirmModal();
                HTMLFormElement.prototype.submit.call(f);
            });

            modal.querySelectorAll('[data-app-confirm-dismiss]').forEach(function (el) {
                el.addEventListener('click', closeConfirmModal);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                if (modal.classList.contains('app-confirm-hidden')) return;
                closeConfirmModal();
            });
        })();
    </script>
    <script>
        // Auto-dismiss alerts after 3.5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            var alerts = document.querySelectorAll('.app-alert');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500); // Wait for transition to finish
                }, 3500);
            });
        });
    </script>
</body>

</html>
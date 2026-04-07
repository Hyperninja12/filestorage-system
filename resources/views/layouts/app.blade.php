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
    <style> body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; } </style>
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
            <a href="{{ route('records.index') }}" class="app-nav-brand">Data Import</a>
            <div class="app-nav-links">
                <a href="{{ route('records.index') }}" class="app-nav-link app-nav-link-records">Records</a>
                <a href="{{ route('import.create') }}" class="app-nav-link app-nav-link-import">Import CSV/Excel</a>
                <a href="{{ route('module-two.index') }}" class="app-nav-link app-nav-link-module">New System</a>
                <form action="{{ route('lock') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="app-nav-link app-nav-link-lock">Lock system</button>
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
        <div id="app-confirm-panel" class="app-confirm-panel" role="alertdialog" aria-modal="true" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message">
            <h2 id="app-confirm-title" class="app-confirm-title"></h2>
            <p id="app-confirm-message" class="app-confirm-message"></p>
            <div class="app-confirm-actions">
                <button type="button" class="app-confirm-btn app-confirm-btn-cancel" data-app-confirm-dismiss>Cancel</button>
                <button type="button" class="app-confirm-btn app-confirm-btn-ok app-confirm-btn-ok-danger" id="app-confirm-ok">Confirm</button>
            </div>
        </div>
    </div>

    <style>
        .app-body { background: #E8F6F3; min-height: 100vh; }
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
        .app-nav-link-module { background: #fff; color: #334155; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .app-nav-link-module:hover { background: #f8fafc; border-color: #94a3b8; color: #0ea5e9; }
        .app-nav-link-lock { background: #64748b; color: #fff; border: 1px solid #475569; }
        .app-nav-link-lock:hover { background: #475569; color: #fff; }
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
        .app-loading { position: fixed; inset: 0; background: rgba(232, 246, 243, 0.92); display: flex; align-items: center; justify-content: center; z-index: 9999; }
        .app-loading.hidden { display: none; }
        .app-loading-content { text-align: center; }
        .app-loading-spinner { width: 2.5rem; height: 2.5rem; border: 3px solid #e2e8f0; border-top-color: #0ea5e9; border-radius: 50%; animation: app-spin 0.8s linear infinite; margin: 0 auto 1rem; }
        .app-loading-text { font-size: 0.9375rem; font-weight: 600; color: #334155; }
        /* Fade + slight scale (dashboard pop-in) after unlock/login */
        .app-main-transition-in {
            animation: appPageIn 0.25s ease-in-out both;
            will-change: transform, opacity;
        }
        @keyframes appPageIn {
            from { opacity: 0; transform: scale(0.985); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes app-spin { to { transform: rotate(360deg); } }

        .app-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 10080;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .app-confirm-hidden { display: none !important; }
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
        .app-confirm-panel--danger { border-top: 4px solid #dc2626; }
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
        .app-confirm-btn-cancel:hover { background: #f8fafc; }
        .app-confirm-btn-ok-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #fff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
        }
        .app-confirm-btn-ok-danger:hover { opacity: 0.95; }
        .app-confirm-btn-ok-neutral {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
        }
        .app-confirm-btn-ok-neutral:hover { opacity: 0.95; }
    </style>
    <script>
        (function() {
            var loading = document.getElementById('app-loading');
            window.hideAppGlobalLoading = function() {
                if (loading) loading.classList.add('hidden');
            };
            var show = function() {
                if (loading) loading.classList.remove('hidden');
            };
            document.body.addEventListener('click', function(e) {
                var a = e.target.closest('a[href]');
                if (a && a.href && a.target !== '_blank' && !a.href.startsWith('javascript:') && a.getAttribute('data-no-app-loading') === null) {
                    try {
                        var url = new URL(a.href);
                        if (url.origin === location.origin) show();
                    } catch (_) {}
                }
            });
            document.body.addEventListener('submit', function(e) {
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
</body>
</html>

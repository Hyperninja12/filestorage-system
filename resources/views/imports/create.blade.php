{{-- Form sa pag-upload ug import: usa ka file input ug submit; loading state kung mag-submit. --}}
@extends('layouts.app')

@section('title', 'Import CSV or Excel')

@section('content')
    <div class="import-page">
        <div class="import-hero">
            <div class="import-hero-badge">Upload data</div>
            <h1 class="import-hero-title">Import Excel File</h1>
            <p class="import-hero-desc">Upload a .csv, .xlsx, or .xls file. The first row is used as headers; each following row becomes one record.</p>
        </div>
        <div class="import-card">
            <div class="import-card-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            </div>
            <h2 class="import-title">Choose your file</h2>
            <p class="import-desc">Supported: .csv, .xlsx, .xls (max 10 MB)</p>
            <div class="import-insert-alert" role="status">
                <strong>Import inserts data</strong> — New rows will be added to your records. Choose a file, click <em>Import</em>, then confirm in the popup.
            </div>
            <form id="import-form" action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data" class="import-form">
                @csrf
                <div class="import-field">
                    <label for="file" class="import-label">Choose file</label>
                    <input type="file" name="file" id="file" accept=".csv,.xlsx,.xls" required
                        class="import-file-input">
                    @error('file')
                        <p class="import-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="import-actions">
                    <button type="submit" id="import-submit" class="import-btn import-btn-primary">
                        <span class="import-btn-text">Import</span>
                        <span class="import-btn-loading" aria-hidden="true">
                            <span class="import-spinner"></span>
                            <span>Importing…</span>
                        </span>
                    </button>
                    <a href="{{ route('records.index') }}" class="import-btn import-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        {{-- Loading overlay sa tibuok page: nakatago hangtod ma-submit ang form, dayon ipakita samtang nag-upload ug nag-process ang server --}}
        <div id="import-loading-overlay" class="import-loading-overlay import-loading-hidden" aria-hidden="true">
            <div class="import-progress-popup" role="dialog" aria-modal="true" aria-labelledby="import-progress-title">
                <h3 id="import-progress-title" class="import-progress-title">Importing records</h3>
                <p id="import-progress-text" class="import-progress-percent">0%</p>
                <div class="import-progress-wrap" aria-hidden="true">
                    <div id="import-progress-bar" class="import-progress-bar"></div>
                </div>
                <p id="import-loading-hint" class="import-loading-hint">Preparing upload…</p>
                <button type="button" id="import-overlay-cancel" class="import-overlay-cancel">Cancel import</button>
            </div>
        </div>

        <div id="import-insert-confirm" class="import-insert-confirm import-insert-confirm-hidden" aria-hidden="true">
            <div class="import-insert-confirm-backdrop" data-import-dismiss tabindex="-1"></div>
            <div class="import-insert-confirm-panel" role="dialog" aria-modal="true" aria-labelledby="import-insert-confirm-title">
                <div class="import-insert-confirm-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                </div>
                <h2 id="import-insert-confirm-title" class="import-insert-confirm-title">Start import?</h2>
                <p class="import-insert-confirm-text">New rows from your file will be inserted into the database. This may take a moment for large files.</p>
                <div class="import-insert-confirm-actions">
                    <button type="button" class="import-insert-confirm-btn import-insert-confirm-btn-secondary" id="import-insert-confirm-cancel">Cancel</button>
                    <button type="button" class="import-insert-confirm-btn import-insert-confirm-btn-primary" id="import-insert-confirm-yes">Yes, import</button>
                </div>
            </div>
        </div>

        <div id="import-message-modal" class="import-message-modal import-message-hidden" aria-hidden="true">
            <div class="import-message-backdrop" data-import-message-close tabindex="-1"></div>
            <div id="import-message-panel" class="import-message-panel import-message-panel--info" role="alertdialog" aria-modal="true" aria-labelledby="import-message-title" aria-describedby="import-message-text">
                <div id="import-message-icon" class="import-message-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="import-message-icon-svg"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                </div>
                <h2 id="import-message-title" class="import-message-title">Notice</h2>
                <p id="import-message-text" class="import-message-text"></p>
                <button type="button" id="import-message-ok" class="import-message-ok">OK</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var form = document.getElementById('import-form');
            var modal = document.getElementById('import-insert-confirm');
            var messageModal = document.getElementById('import-message-modal');
            var messagePanel = document.getElementById('import-message-panel');
            var messageTitleEl = document.getElementById('import-message-title');
            var messageTextEl = document.getElementById('import-message-text');
            var messageIconWrap = document.getElementById('import-message-icon');
            var overlay = document.getElementById('import-loading-overlay');
            var btn = document.getElementById('import-submit');
            var progressBar = document.getElementById('import-progress-bar');
            var progressText = document.getElementById('import-progress-text');
            var hint = document.getElementById('import-loading-hint');
            var startedAt = 0;
            var uploadCompletedAt = 0;
            var hardTimeoutTimer = null;
            /** Stops double alerts when we abort() from watchdog / elapsed guard (XHR may still fire readystate 4). */
            var importAbortReason = null;
            /** Max wait before we abort (ms). Browser throttles timers in background tabs; tick() uses wall clock. */
            var IMPORT_MAX_ELAPSED_MS = 180000;
            var visibilityGuard = null;
            var rafWatchId = null;
            var activeImportXhr = null;
            var recordsIndexUrl = @json(route('records.index'));
            var importStoreUrl = @json(route('import.store'));
            if (!form || !modal || !overlay || !btn || !progressBar || !progressText || !hint) return;

            var messageUiOk = !!(messageModal && messagePanel && messageTitleEl && messageTextEl && messageIconWrap);

            function isMessageModalOpen() {
                return messageUiOk && !messageModal.classList.contains('import-message-hidden');
            }

            function closeImportMessage() {
                if (!messageUiOk) return;
                messageModal.classList.add('import-message-hidden');
                messageModal.setAttribute('aria-hidden', 'true');
            }

            /**
             * @param {string} text Plain text only (line breaks preserved)
             * @param {Object} [opts] Optional popup options
             */
            function showImportMessage(text, opts) {
                opts = opts || {};
                if (!messageUiOk) {
                    alert(text || '');
                    return;
                }
                var variant = opts.variant === 'error' || opts.variant === 'warning' ? opts.variant : 'info';
                var title = opts.title;
                if (!title) {
                    title = variant === 'error' ? 'Import failed' : variant === 'warning' ? 'Import stopped' : 'Notice';
                }
                messageTitleEl.textContent = title;
                messageTextEl.textContent = text || '';
                messagePanel.classList.remove('import-message-panel--info', 'import-message-panel--error', 'import-message-panel--warning');
                messagePanel.classList.add('import-message-panel--' + variant);
                messageIconWrap.classList.remove('import-message-icon--info', 'import-message-icon--error', 'import-message-icon--warning');
                messageIconWrap.classList.add('import-message-icon--' + variant);
                messageModal.classList.remove('import-message-hidden');
                messageModal.setAttribute('aria-hidden', 'false');
                var okBtn = document.getElementById('import-message-ok');
                if (okBtn) okBtn.focus();
            }

            if (messageUiOk) {
                var msgOk = document.getElementById('import-message-ok');
                if (msgOk) msgOk.addEventListener('click', closeImportMessage);
                messageModal.querySelectorAll('[data-import-message-close]').forEach(function (el) {
                    el.addEventListener('click', closeImportMessage);
                });
            }

            function openConfirm() {
                hideGlobalAppLoading();
                modal.classList.remove('import-insert-confirm-hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.getElementById('import-insert-confirm-yes').focus();
            }
            function closeConfirm() {
                modal.classList.add('import-insert-confirm-hidden');
                modal.setAttribute('aria-hidden', 'true');
            }
            function setProgress(pct, text) {
                var clamped = Math.max(0, Math.min(100, pct));
                progressBar.style.width = clamped + '%';
                progressText.textContent = text || (Math.round(clamped) + '%');
            }
            function elapsedText() {
                if (!startedAt) return '';
                var s = Math.floor((Date.now() - startedAt) / 1000);
                if (s < 60) return s + 's';
                var m = Math.floor(s / 60);
                return m + 'm ' + (s % 60) + 's';
            }
            function etaText(seconds) {
                var s = Math.max(0, Math.floor(seconds));
                if (s < 60) return s + 's';
                var m = Math.floor(s / 60);
                return m + 'm ' + (s % 60) + 's';
            }
            function hideGlobalAppLoading() {
                var appLoading = document.getElementById('app-loading');
                if (appLoading) appLoading.classList.add('hidden');
            }
            function startOverlay() {
                hideGlobalAppLoading();
                closeConfirm();
                overlay.classList.remove('import-loading-hidden');
                overlay.setAttribute('aria-hidden', 'false');
                btn.disabled = true;
                btn.classList.add('loading');
                startedAt = Date.now();
                uploadCompletedAt = 0;
                importAbortReason = null;
                setProgress(0, '0%');
                hint.textContent = 'Uploading file…';
            }
            function stopOverlay() {
                overlay.classList.add('import-loading-hidden');
                overlay.setAttribute('aria-hidden', 'true');
                btn.disabled = false;
                btn.classList.remove('loading');
                if (hardTimeoutTimer) {
                    clearTimeout(hardTimeoutTimer);
                    hardTimeoutTimer = null;
                }
                if (visibilityGuard) {
                    document.removeEventListener('visibilitychange', visibilityGuard);
                    visibilityGuard = null;
                }
                if (rafWatchId !== null) {
                    cancelAnimationFrame(rafWatchId);
                    rafWatchId = null;
                }
                activeImportXhr = null;
            }
            function clearHardTimeout() {
                if (hardTimeoutTimer) {
                    clearTimeout(hardTimeoutTimer);
                    hardTimeoutTimer = null;
                }
            }
            function responseSnippet(text) {
                if (!text) return '';
                var clean = String(text).replace(/\s+/g, ' ').trim();
                if (!clean) return '';
                return clean.length > 260 ? clean.slice(0, 260) + '…' : clean;
            }
            function buildHttpErrorMessage(xhr, fallback) {
                var err = fallback || 'Import failed.';
                var status = xhr && xhr.status ? xhr.status : 0;
                if (status === 419) {
                    err = 'Session expired (CSRF). Refresh the page, unlock again, then retry import.';
                } else if (status === 413) {
                    err = 'File is too large. Maximum allowed is 10 MB.';
                } else if (xhr && xhr.responseURL && xhr.responseURL.indexOf('/unlock') !== -1) {
                    err = 'Session is locked/expired. Unlock the system, then retry import.';
                }
                try {
                    var payload = JSON.parse((xhr && xhr.responseText) || '{}');
                    err = payload.message || (payload.errors && payload.errors.file && payload.errors.file[0]) || err;
                } catch (e) {}
                if (status >= 500) {
                    err = 'Server error during import. Please retry. If it continues, check laravel.log.';
                }
                var snippet = responseSnippet(xhr && xhr.responseText ? xhr.responseText : '');
                if (status > 0) {
                    err += '\nHTTP ' + status + '.';
                }
                if (snippet) {
                    err += '\nDetails: ' + snippet;
                }
                return err;
            }
            function doAjaxImport() {
                startOverlay();
                var xhr = new XMLHttpRequest();
                activeImportXhr = xhr;
                function abortImportDueToElapsed(reason) {
                    if (xhr.readyState === 4 || overlay.classList.contains('import-loading-hidden')) return;
                    importAbortReason = reason;
                    try { xhr.abort(); } catch (e) {}
                    stopOverlay();
                    showImportMessage(
                        'Import stopped: no server response after ' + Math.round(IMPORT_MAX_ELAPSED_MS / 1000) + 's.\n\nYour browser may still be uploading, or PHP may be stuck. Hard-refresh this page, run php artisan serve, and check storage/logs/laravel.log.',
                        { variant: 'warning', title: 'No response' }
                    );
                }
                function rafWatch() {
                    rafWatchId = null;
                    if (overlay.classList.contains('import-loading-hidden') || xhr.readyState === 4) return;
                    var elapsed = Date.now() - startedAt;
                    if (elapsed > IMPORT_MAX_ELAPSED_MS) {
                        abortImportDueToElapsed('raf');
                        return;
                    }
                    rafWatchId = requestAnimationFrame(rafWatch);
                }
                rafWatchId = requestAnimationFrame(rafWatch);
                if (visibilityGuard) {
                    document.removeEventListener('visibilitychange', visibilityGuard);
                    visibilityGuard = null;
                }
                visibilityGuard = function () {
                    if (document.hidden || xhr.readyState === 4) return;
                    if (Date.now() - startedAt > IMPORT_MAX_ELAPSED_MS) {
                        abortImportDueToElapsed('visible');
                    }
                };
                document.addEventListener('visibilitychange', visibilityGuard);
                xhr.open('POST', form.action || importStoreUrl, true);
                xhr.timeout = IMPORT_MAX_ELAPSED_MS + 60000;
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        var pct = (e.loaded / e.total) * 90;
                        setProgress(pct, Math.round(pct) + '%');
                        var elapsedSec = Math.max(1, (Date.now() - startedAt) / 1000);
                        var speed = e.loaded / elapsedSec;
                        var etaSec = speed > 0 ? (e.total - e.loaded) / speed : 0;
                        hint.textContent = 'Uploading file… ' + Math.round(pct) + '% (ETA ~' + etaText(etaSec) + ', elapsed ' + elapsedText() + ')';
                        if (e.loaded === e.total && uploadCompletedAt === 0) {
                            uploadCompletedAt = Date.now();
                        }
                    }
                });
                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) return;
                    clearHardTimeout();
                    if (importAbortReason) {
                        importAbortReason = null;
                        return;
                    }
                    if (xhr.status >= 200 && xhr.status < 300) {
                        setProgress(100, '100%');
                        hint.textContent = 'Import complete. Redirecting…';
                        stopOverlay();
                        try {
                            var data = JSON.parse(xhr.responseText || '{}');
                            window.location.href = data.redirect || recordsIndexUrl;
                            return;
                        } catch (e) {
                            window.location.href = recordsIndexUrl;
                        }
                        return;
                    }
                    var err = buildHttpErrorMessage(xhr, 'Import failed. Please check file format and try again.');
                    stopOverlay();
                    showImportMessage(err, { variant: 'error', title: 'Import failed' });
                };
                xhr.onerror = function () {
                    clearHardTimeout();
                    if (importAbortReason) {
                        importAbortReason = null;
                        return;
                    }
                    stopOverlay();
                    showImportMessage(
                        'Network error while importing. Please try again.\n\nIf this keeps happening, check server stability (PM2 or php artisan serve).',
                        { variant: 'error', title: 'Network error' }
                    );
                };
                xhr.ontimeout = function () {
                    clearHardTimeout();
                    if (importAbortReason) {
                        importAbortReason = null;
                        return;
                    }
                    stopOverlay();
                    showImportMessage(
                        'Import is taking too long and timed out. The server may be stuck processing this file.\n\nCheck storage/logs/laravel.log and try a smaller file.',
                        { variant: 'warning', title: 'Timed out' }
                    );
                };

                var formData = new FormData(form);
                xhr.send(formData);
                // Backup timer (may be throttled in background tabs; tick() also enforces IMPORT_MAX_ELAPSED_MS).
                hardTimeoutTimer = setTimeout(function () {
                    if (xhr.readyState === 4 || overlay.classList.contains('import-loading-hidden')) return;
                    importAbortReason = 'watchdog';
                    try { xhr.abort(); } catch (e) {}
                    stopOverlay();
                    showImportMessage(
                        'Import watchdog: no response after several minutes. The request was aborted.\n\nRefresh the page, confirm php artisan serve (or PM2) is running, then try again. See storage/logs/laravel.log.',
                        { variant: 'warning', title: 'Request aborted' }
                    );
                }, IMPORT_MAX_ELAPSED_MS + 120000);

                setTimeout(function tick() {
                    if (overlay.classList.contains('import-loading-hidden')) return;
                    if (xhr.readyState === 4) return;
                    var elapsed = Date.now() - startedAt;
                    if (elapsed > IMPORT_MAX_ELAPSED_MS) {
                        abortImportDueToElapsed('tick');
                        return;
                    }
                    var current = parseFloat(progressBar.style.width) || 0;
                    if (current < 96) {
                        var next = Math.min(96, current + 1.2);
                        setProgress(next, Math.round(next) + '%');
                    }
                    hint.textContent = 'Working… ' + elapsedText() + ' elapsed (stops automatically at ' + Math.round(IMPORT_MAX_ELAPSED_MS / 1000) + 's if no response)';
                    setTimeout(tick, 800);
                }, 1200);
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                openConfirm();
            });

            document.getElementById('import-insert-confirm-cancel').addEventListener('click', closeConfirm);
            modal.querySelectorAll('[data-import-dismiss]').forEach(function (el) {
                el.addEventListener('click', closeConfirm);
            });
            document.getElementById('import-insert-confirm-yes').addEventListener('click', function () {
                closeConfirm();
                doAjaxImport();
            });
            var overlayCancel = document.getElementById('import-overlay-cancel');
            if (overlayCancel) {
                overlayCancel.addEventListener('click', function () {
                    importAbortReason = 'user';
                    if (activeImportXhr) {
                        try { activeImportXhr.abort(); } catch (e) {}
                    }
                    stopOverlay();
                });
            }
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                if (isMessageModalOpen()) {
                    closeImportMessage();
                    return;
                }
                if (!modal.classList.contains('import-insert-confirm-hidden')) closeConfirm();
            });
        })();
    </script>
@endsection

@push('styles')
<style>
    .import-page { position: relative; min-height: 50vh; }
    .import-hero {
        text-align: center;
        margin-bottom: 2rem;
    }
    .import-hero-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 9999px;
        margin-bottom: 0.75rem;
    }
    .import-hero-title { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0; letter-spacing: -0.02em; }
    .import-hero-desc { font-size: 0.9375rem; color: #64748b; margin: 0; max-width: 28rem; margin-left: auto; margin-right: auto; }
    .import-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
        padding: 2rem;
        max-width: 28rem;
        margin: 0 auto;
        border: 1px solid #e2e8f0;
    }
    .import-card-icon {
        width: 3.5rem;
        height: 3.5rem;
        margin-bottom: 1rem;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.1);
        border-radius: 0.75rem;
        padding: 0.75rem;
        box-sizing: border-box;
    }
    .import-card-icon svg { width: 100%; height: 100%; }
    .import-title { font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
    .import-desc { color: #64748b; font-size: 0.875rem; line-height: 1.5; margin-bottom: 1rem; }
    .import-insert-alert {
        margin-bottom: 1.25rem;
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #1e40af;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
        border-radius: 0.5rem;
        text-align: left;
    }
    .import-insert-alert strong { font-weight: 600; }
    .import-form { display: flex; flex-direction: column; gap: 1.25rem; }
    .import-field { }
    .import-label { display: block; font-size: 0.875rem; font-weight: 500; color: rgb(51 65 85); margin-bottom: 0.375rem; }
    .import-file-input {
        display: block;
        width: 100%;
        font-size: 0.875rem;
        color: rgb(71 85 105);
        padding: 0.625rem 0.75rem;
        border: 1px solid rgb(203 213 225);
        border-radius: 0.5rem;
        background: #fff;
    }
    .import-file-input:focus { outline: none; box-shadow: 0 0 0 2px rgb(99 102 241); border-color: rgb(99 102 241); }
    .import-file-input::file-selector-button {
        margin-right: 0.75rem;
        padding: 0.5rem 0.875rem;
        border: 0;
        border-radius: 0.5rem;
        background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4f46e5;
        font-weight: 600;
        cursor: pointer;
    }
    .import-file-input::file-selector-button:hover { background: #e0e7ff; }
    .import-error { margin-top: 0.25rem; font-size: 0.875rem; color: rgb(185 28 28); }
    .import-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .import-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        min-width: 6rem;
    }
    .import-btn:disabled { cursor: not-allowed; opacity: 0.8; }
    .import-btn-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3); }
    .import-btn-primary:hover:not(:disabled) { opacity: 0.95; box-shadow: 0 4px 8px rgba(99, 102, 241, 0.35); }
    .import-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
    .import-btn-secondary:hover { background: #f8fafc; }
    .import-btn-text { }
    .import-btn-loading { display: none; align-items: center; gap: 0.5rem; }
    .import-btn.loading .import-btn-text { display: none; }
    .import-btn.loading .import-btn-loading { display: inline-flex; }
    .import-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: import-spin 0.7s linear infinite;
    }
    .import-spinner-lg { width: 2rem; height: 2rem; border-width: 3px; }
    @keyframes import-spin { to { transform: rotate(360deg); } }
    .import-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10050;
        opacity: 1;
        transition: opacity 0.2s ease;
    }
    .import-loading-overlay.import-loading-hidden { pointer-events: none; opacity: 0; }
    .import-progress-popup {
        background: #fff;
        border-radius: 1rem;
        padding: 1.25rem 1.25rem 1rem;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        border: 1px solid #e2e8f0;
        width: min(92vw, 24rem);
        position: relative;
        z-index: 10051;
    }
    .import-progress-title {
        margin: 0 0 0.35rem;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .import-progress-percent {
        margin: 0 0 0.6rem;
        font-size: 1.5rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: 0.01em;
        color: #4f46e5;
        font-variant-numeric: tabular-nums;
    }
    .import-loading-hint { font-size: 0.8125rem; color: rgb(100 116 139); }
    .import-progress-wrap {
        margin-top: 0.25rem;
        width: 100%;
        height: 0.65rem;
        border-radius: 9999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .import-progress-bar {
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
        transition: width 0.25s ease;
    }
    .import-overlay-cancel {
        margin-top: 1rem;
        padding: 0.45rem 1rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        cursor: pointer;
    }
    .import-overlay-cancel:hover { background: #e2e8f0; }
    .import-insert-confirm {
        position: fixed; inset: 0; z-index: 10060;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .import-insert-confirm-hidden { display: none !important; }
    .import-insert-confirm-backdrop {
        position: absolute; inset: 0;
        background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px);
    }
    .import-insert-confirm-panel {
        position: relative; max-width: 22rem; width: 100%;
        padding: 1.5rem; background: #fff; border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        animation: importInsertPop 0.28s cubic-bezier(0.22, 1, 0.36, 1);
    }
    @keyframes importInsertPop {
        from { opacity: 0; transform: scale(0.94) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .import-insert-confirm-icon {
        width: 2.75rem; height: 2.75rem; margin: 0 auto 1rem;
        color: #4f46e5; background: #eef2ff; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
    }
    .import-insert-confirm-icon svg { width: 1.5rem; height: 1.5rem; }
    .import-insert-confirm-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; text-align: center; }
    .import-insert-confirm-text { font-size: 0.875rem; color: #64748b; margin: 0 0 1.25rem; line-height: 1.5; text-align: center; }
    .import-insert-confirm-actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
    .import-insert-confirm-btn {
        padding: 0.5rem 1.15rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600;
        cursor: pointer; border: 1px solid transparent;
    }
    .import-insert-confirm-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
    .import-insert-confirm-btn-secondary:hover { background: #f8fafc; }
    .import-insert-confirm-btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
    }
    .import-insert-confirm-btn-primary:hover { opacity: 0.95; }

    .import-message-modal {
        position: fixed;
        inset: 0;
        z-index: 10070;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .import-message-hidden { display: none !important; }
    .import-message-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
    }
    .import-message-panel {
        position: relative;
        width: 100%;
        max-width: 26rem;
        padding: 1.5rem;
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        text-align: center;
        animation: importInsertPop 0.28s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .import-message-panel--info { border-top: 4px solid #6366f1; }
    .import-message-panel--warning { border-top: 4px solid #d97706; }
    .import-message-panel--error { border-top: 4px solid #dc2626; }
    .import-message-icon {
        width: 2.75rem;
        height: 2.75rem;
        margin: 0 auto 0.75rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .import-message-icon-svg { width: 1.5rem; height: 1.5rem; }
    .import-message-icon--info { color: #4f46e5; background: #eef2ff; }
    .import-message-icon--warning { color: #b45309; background: #fffbeb; }
    .import-message-icon--error { color: #b91c1c; background: #fef2f2; }
    .import-message-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.75rem;
        line-height: 1.3;
    }
    .import-message-text {
        font-size: 0.875rem;
        color: #475569;
        margin: 0 0 1.25rem;
        line-height: 1.55;
        text-align: left;
        white-space: pre-line;
        word-break: break-word;
        max-height: min(50vh, 18rem);
        overflow-y: auto;
    }
    .import-message-ok {
        width: 100%;
        padding: 0.55rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
    }
    .import-message-ok:hover { opacity: 0.95; }
    .import-message-panel--error .import-message-ok {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
    }
    .import-message-panel--warning .import-message-ok {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35);
    }
</style>
@endpush

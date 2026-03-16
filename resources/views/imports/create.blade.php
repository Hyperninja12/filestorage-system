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
            <div class="import-loading-box">
                <span class="import-spinner import-spinner-lg"></span>
                <p class="import-loading-text">Importing your file…</p>
                <p class="import-loading-hint">This may take a moment for large files.</p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('import-form').addEventListener('submit', function() {
            document.getElementById('import-loading-overlay').classList.remove('import-loading-hidden');
            var btn = document.getElementById('import-submit');
            btn.disabled = true;
            btn.classList.add('loading');
        });
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
    .import-desc { color: #64748b; font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.25rem; }
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
        z-index: 100;
        opacity: 1;
        transition: opacity 0.2s ease;
    }
    .import-loading-overlay.import-loading-hidden { pointer-events: none; opacity: 0; }
    .import-loading-box {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        min-width: 16rem;
    }
    .import-loading-box .import-spinner-lg { margin: 0 auto 1rem; display: block; border-color: rgba(99 102 241, 0.3); border-top-color: rgb(99 102 241); }
    .import-loading-text { font-weight: 600; color: rgb(30 41 59); margin-bottom: 0.25rem; }
    .import-loading-hint { font-size: 0.8125rem; color: rgb(100 116 139); }
</style>
@endpush

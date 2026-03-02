{{-- Import upload form: single file input and submit; loading state on submit. --}}
@extends('layouts.app')

@section('title', 'Import CSV or Excel')

@section('content')
    <div class="import-page">
        <div class="import-card">
            <div class="import-card-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            </div>
            <h1 class="import-title">Import CSV or Excel</h1>
            <p class="import-desc">Upload a .csv, .xlsx, or .xls file. The first row is used as headers; each following row becomes one record.</p>
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
        {{-- Full-page loading overlay: hidden until form is submitted, then shown while file uploads and server processes --}}
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
    .import-page { position: relative; min-height: 40vh; }
    .import-card {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.08), 0 2px 4px -2px rgb(0 0 0 / 0.06), 0 0 0 1px rgb(226 232 240);
        padding: 2rem;
        max-width: 28rem;
        margin: 0 auto;
    }
    .import-card-icon {
        width: 3rem;
        height: 3rem;
        margin-bottom: 1rem;
        color: rgb(99 102 241);
    }
    .import-card-icon svg { width: 100%; height: 100%; }
    .import-title { font-size: 1.35rem; font-weight: 600; color: rgb(30 41 59); margin-bottom: 0.5rem; }
    .import-desc { color: rgb(100 116 139); font-size: 0.9375rem; line-height: 1.5; margin-bottom: 1.5rem; }
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
        padding: 0.375rem 0.75rem;
        border: 0;
        border-radius: 0.375rem;
        background: rgb(238 242 255);
        color: rgb(99 102 241);
        font-weight: 500;
        cursor: pointer;
    }
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
    .import-btn-primary { background: rgb(99 102 241); color: white; }
    .import-btn-primary:hover:not(:disabled) { background: rgb(79 70 229); }
    .import-btn-secondary { background: #fff; color: rgb(71 85 105); border-color: rgb(203 213 225); }
    .import-btn-secondary:hover { background: rgb(248 250 252); }
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

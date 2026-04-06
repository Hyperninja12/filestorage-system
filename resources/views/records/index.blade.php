{{-- Lista sa records: search/filter bar, dayon data table nga naay tanang columns. Empty cells placeholder. --}}
@extends('layouts.app')

@section('title', 'Records')

@section('content')
    @php
        $listQuery = array_filter(
            request()->only(['page', 'search', 'person_responsible', 'type']),
            fn ($v) => $v !== null && $v !== ''
        );
    @endphp
    <div class="records-header">
        <div class="records-header-text">
            <h1 class="records-title">Records</h1>
            <p class="records-subtitle">Search, view, and manage imported data</p>
        </div>
        <a href="{{ route('records.create') }}" class="records-header-add-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add record
        </a>
    </div>

    {{-- PAR / ICS switch: Unit Value 50K+ = PAR, below 50K = ICS --}}
    @php $currentType = $type ?? request('type', 'all'); @endphp
    <div class="records-type-switch">
        <span class="records-type-label">Show:</span>
        <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'all'])) }}"
           class="records-type-btn {{ $currentType === 'all' ? 'records-type-btn-active' : '' }}">All</a>
        <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'par'])) }}"
           class="records-type-btn records-type-btn-par {{ $currentType === 'par' ? 'records-type-btn-active' : '' }}">PAR</a>
        <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'ics'])) }}"
           class="records-type-btn records-type-btn-ics {{ $currentType === 'ics' ? 'records-type-btn-active' : '' }}">ICS</a>
    </div>

    {{-- Card para sa search ug filter --}}
    <div class="records-search-card">
        <div class="records-search-header">
            <svg class="records-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <span>Search & filter</span>
        </div>
        <form action="{{ route('records.index') }}" method="GET" class="records-search-form">
            @if ($currentType !== 'all')
                <input type="hidden" name="type" value="{{ $currentType }}">
            @endif
            <div class="records-search-field records-search-field-wide">
                <label for="search" class="records-search-label">Search (any column)</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Type to search..."
                    class="records-search-input">
            </div>
            <div class="records-search-field">
                <label for="person_responsible" class="records-search-label">By Person Responsible</label>
                <input type="text" name="person_responsible" id="person_responsible" value="{{ request('person_responsible') }}" placeholder="Name or part of name"
                    class="records-search-input">
            </div>
            <div class="records-search-buttons">
                <button type="submit" class="records-search-btn records-search-btn-primary">Search</button>
                @if (request()->hasAny(['search', 'person_responsible', 'type']))
                    <a href="{{ route('records.index') }}" class="records-search-btn records-search-btn-secondary">Clear</a>
                @endif
                @if (request()->filled('person_responsible'))
                    <a href="{{ route('records.print-list', request()->query()) }}" target="_blank" class="records-search-btn" style="background: #10b981; color: white;">
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 4px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Print Data
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($records->isEmpty())
        <div class="records-empty">
            <div class="records-empty-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.558l.256 1.128a2.25 2.25 0 002.013 1.58H21.75M2.25 13.5a2.25 2.25 0 00-2.25 2.25v2.25c0 1.114.84 2.03 1.972 2.03 1.171 0 2.18-.879 2.18-2.03V15.75m0-2.25c0-1.114-.84-2.03-1.972-2.03H2.25M15.75 9v2.25m0-2.25v-2.25m0 2.25h2.25m-2.25 0h-2.25" /></svg>
            </div>
            @if ($currentType === 'par')
                <p class="records-empty-text">No PAR records (Unit Value 50,000 and above).</p>
            @elseif ($currentType === 'ics')
                <p class="records-empty-text">No ICS records (Unit Value below 50,000).</p>
            @else
                <p class="records-empty-text">No records yet.</p>
            @endif
            @if ($currentType === 'all')
            <p class="records-empty-links">
                <a href="{{ route('records.create') }}" class="records-empty-link">Add a record manually</a>
                or
                <a href="{{ route('import.create') }}" class="records-empty-link">import a CSV or Excel file</a>
                to get started.
            </p>
            @else
            <p class="records-empty-links">
                <a href="{{ route('records.index') }}" class="records-empty-link">View all records</a>
                or try a different filter.
            </p>
            @endif
        </div>
    @else
        {{-- Table wrapper: top scroll strip ug body, synced aron makascroll gikan sa taas --}}
        <div class="records-table-card overflow-hidden">
            <div class="records-table-scroll-area">
                <div id="records-top-scroll" class="records-table-top-scroll" aria-hidden="true">
                    <div id="records-top-scroll-inner" class="records-table-top-scroll-inner"></div>
                </div>
                <div id="records-table-body" class="records-table-body-wrap overflow-y-visible">
                    <table class="records-table min-w-full border-collapse" id="records-data-table">
                    <thead class="records-table-thead sticky top-0 z-10">
                        <tr>
                            <th class="records-table-th records-table-th-actions">Actions</th>
                            @foreach ($headers as $h)
                                <th class="records-table-th">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($records as $record)
                            <tr class="records-table-tr border-b border-gray-200 hover:bg-slate-50">
                                <td class="records-table-td records-table-td-actions">
                                    <div class="records-table-actions">
                                        <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}" class="records-table-btn records-table-btn-view">View</a>
                                        <a href="{{ route('records.edit', array_merge(['record' => $record], $listQuery)) }}" class="records-table-btn records-table-btn-edit">Edit</a>
                                        @php $imgPaths = $record->getImagePaths(); $imgCount = count($imgPaths); @endphp
                                        @if ($imgCount > 0)
                                            <button type="button" onclick="previewImage('{{ route('records.image', [$record, 0]) }}')" class="records-table-btn records-table-btn-image">{{ $imgCount > 1 ? 'Images (' . $imgCount . ')' : 'Image' }}</button>
                                            @if ($imgCount > 0)
                                                <form action="{{ route('records.remove-image', [$record, 0]) }}" method="POST" class="records-table-action-form records-table-remove-image-form"
                                                    data-app-confirm="1"
                                                    data-app-confirm-title="Remove first image?"
                                                    data-app-confirm-message="The first attached image will be removed from this record."
                                                    data-app-confirm-ok="Remove"
                                                    data-app-confirm-variant="danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="records-table-btn records-table-btn-remove-image" title="Remove first image">×</button>
                                                </form>
                                            @endif
                                        @endif
                                        @if ($imgCount < 2)
                                            <button type="button" onclick="document.getElementById('attach-{{ $record->id }}').click()" class="records-table-btn records-table-btn-attach">Attach</button>
                                            <form id="form-{{ $record->id }}" action="{{ route('records.attach-image', $record) }}" method="POST" enctype="multipart/form-data" class="hidden">
                                                @csrf
                                                <input type="file" name="image" id="attach-{{ $record->id }}" accept="image/*" onchange="this.form.submit()">
                                            </form>
                                        @endif
                                        <form action="{{ route('records.destroy', $record) }}" method="POST" class="records-table-action-form"
                                            data-app-confirm="1"
                                            data-app-confirm-title="Delete this record?"
                                            data-app-confirm-message="This action cannot be undone."
                                            data-app-confirm-ok="Delete"
                                            data-app-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            @foreach ($listQuery as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endforeach
                                            <button type="submit" class="records-table-btn records-table-btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                                @foreach ($headers as $h)
                                    @php
                                        $val = $record->getColumn($h);
                                        $isMoney = in_array($h, ['Unit Value', 'On Hand Value'], true);
                                        if ($isMoney) {
                                            $display = \App\Support\CashFormatter::formatOrPlaceholder($val);
                                            $title = $display;
                                            $tdClass = 'records-table-td records-table-td-cash';
                                        } else {
                                            $display = ($val !== null && $val !== '') ? Str::words((string) $val, 40) : '—';
                                            $title = (string) ($val ?? '');
                                            $tdClass = 'records-table-td';
                                        }
                                    @endphp
                                    <td class="{{ $tdClass }}" title="{{ $title }}">
                                        <span class="records-table-cell">{{ $display }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="records-pagination">
                {{ $records->links() }}
            </div>
        </div>

        {{-- Modal para sa image preview --}}
        <div id="image-modal" class="records-modal-overlay hidden" onclick="closePreview()">
            <div class="records-modal-box" onclick="event.stopPropagation()">
                <img id="preview-img" src="" alt="Preview" class="records-modal-img">
                <button type="button" onclick="closePreview()" class="records-modal-close">Close</button>
            </div>
        </div>
        <script>
            function previewImage(url) {
                document.getElementById('preview-img').src = url;
                document.getElementById('image-modal').classList.remove('hidden');
                document.getElementById('image-modal').classList.add('records-modal-open');
            }
            function closePreview() {
                document.getElementById('image-modal').classList.add('hidden');
                document.getElementById('image-modal').classList.remove('records-modal-open');
            }
            (function() {
                var topScroll = document.getElementById('records-top-scroll');
                var topInner = document.getElementById('records-top-scroll-inner');
                var bodyWrap = document.getElementById('records-table-body');
                var table = document.getElementById('records-data-table');
                if (!topScroll || !bodyWrap || !table) return;
                function syncWidth() {
                    topInner.style.width = table.scrollWidth + 'px';
                }
                function scrollTopFromBody() { topScroll.scrollLeft = bodyWrap.scrollLeft; }
                function scrollBodyFromTop() { bodyWrap.scrollLeft = topScroll.scrollLeft; }
                topScroll.addEventListener('scroll', scrollBodyFromTop);
                bodyWrap.addEventListener('scroll', scrollTopFromBody);
                syncWidth();
                if (typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(syncWidth).observe(table);
                }
                window.addEventListener('resize', syncWidth);
            })();
        </script>




    @endif
@endsection

@push('styles')
{{-- Design sa records page ug table --}}
<style>
    .records-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .records-header-text { flex: 1; }
    .records-title { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0; letter-spacing: -0.02em; }
    .records-subtitle { font-size: 0.9375rem; color: #64748b; margin: 0; }
    .records-header-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        border: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .records-header-add-btn:hover { opacity: 0.95; }
    .records-type-switch {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .records-type-label { font-size: 0.875rem; font-weight: 600; color: #475569; margin-right: 0.25rem; }
    .records-type-btn {
        display: inline-block;
        padding: 0.4rem 0.9rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
    }
    .records-type-btn:hover { background: #f8fafc; border-color: #94a3b8; }
    .records-type-btn-active { background: #e0e7ff; border-color: #6366f1; color: #4338ca; }
    .records-type-btn-par.records-type-btn-active { background: #dbeafe; border-color: #2563eb; color: #1d4ed8; }
    .records-type-btn-ics.records-type-btn-active { background: #d1fae5; border-color: #059669; color: #047857; }
    .records-search-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        padding: 0;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .records-search-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.9375rem;
        color: #334155;
    }
    .records-search-icon { width: 1.25rem; height: 1.25rem; color: #6366f1; flex-shrink: 0; }
    .records-search-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; padding: 1.25rem; }
    .records-search-field { flex: 0 0 auto; }
    .records-search-field-wide { flex: 1; min-width: 12rem; }
    .records-search-label { display: block; font-size: 0.8125rem; font-weight: 500; color: #475569; margin-bottom: 0.25rem; }
    .records-search-input {
        padding: 0.5rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        min-width: 8rem;
    }
    .records-search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
    .records-search-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .records-search-btn { padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
    .records-search-btn-primary { background: #1e293b; color: #fff; }
    .records-search-btn-primary:hover { background: #334155; }
    .records-search-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
    .records-search-btn-secondary:hover { background: #f8fafc; }
    .records-pagination { padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }
    .records-modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center; z-index: 50;
    }
    .records-modal-overlay.records-modal-open { display: flex; }
    .records-modal-box { max-width: 90vw; max-height: 90vh; padding: 1rem; }
    .records-modal-img { max-width: 100%; max-height: 80vh; border-radius: 0.75rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4); }
    .records-modal-close { margin-top: 1rem; width: 100%; padding: 0.625rem 1rem; background: #1e293b; color: #fff; border-radius: 0.5rem; font-weight: 500; cursor: pointer; border: 0; }
    .records-modal-close:hover { background: #334155; }
    .records-empty {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        border: 1px solid rgb(226 232 240);
        padding: 3rem 2rem;
        text-align: center;
    }
    .records-empty-icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        color: rgb(148 163 184);
    }
    .records-empty-icon svg { width: 100%; height: 100%; }
    .records-empty-text { color: rgb(100 116 139); margin-bottom: 0.25rem; }
    .records-empty-links { color: rgb(100 116 139); margin: 0; font-size: 0.9375rem; }
    .records-empty-link { color: rgb(99 102 241); font-weight: 500; }
    .records-empty-link:hover { text-decoration: underline; }
    .records-table-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04),
                    0 0 24px rgba(99, 102, 241, 0.12), 0 0 48px rgba(99, 102, 241, 0.06);
        border: 1px solid rgba(99, 102, 241, 0.2);
        overflow: hidden;
    }
    .records-table-scroll-area { }
    .records-table-top-scroll {
        overflow-x: auto;
        overflow-y: hidden;
        height: 40px;
        background: linear-gradient(180deg, #e0e7ff 0%, #c7d2fe 100%);
        border-bottom: 2px solid #a5b4fc;
        flex-shrink: 0;
      -webkit-overflow-scrolling: touch;
    }
    .records-table-top-scroll::-webkit-scrollbar { height: 20px; }
    .records-table-top-scroll::-webkit-scrollbar-track { background: #c7d2fe; border-radius: 8px; }
    .records-table-top-scroll::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 8px; }
    .records-table-top-scroll::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    .records-table-top-scroll-inner { display: inline-block; min-width: 1px; height: 1px; }
    .records-table-body-wrap {
        overflow-x: auto;
        overflow-y: visible;
      -webkit-overflow-scrolling: touch;
    }
    .records-table-body-wrap::-webkit-scrollbar { height: 20px; }
    .records-table-body-wrap::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 8px; }
    .records-table-body-wrap::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 8px; }
    .records-table-body-wrap::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    .records-table { font-size: 0.8125rem; }
    .records-table thead { background: linear-gradient(180deg, #475569 0%, #334155 100%); }
    .records-table-th {
        padding: 0.625rem 0.75rem;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
        min-width: 5rem;
        max-width: 12rem;
        border-bottom: none;
        color: #f1f5f9;
    }
    .records-table-th-actions { min-width: 10rem; max-width: none; }
    .records-table-td {
        padding: 0.5rem 0.75rem;
        color: rgb(55 65 81);
        vertical-align: top;
        min-width: 5rem;
        max-width: 12rem;
        border-bottom: 1px solid rgb(229 231 235);
    }
    .records-table-td-actions { min-width: 10rem; max-width: none; }
    .records-table-td-cash .records-table-cell {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        max-height: none;
    }
    .records-table-cell {
        display: block;
        max-height: 6em;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.25;
        word-break: break-word;
    }
    .records-table-tr:nth-child(even) { background-color: rgb(248 250 252); }
    .records-table-tr:hover { background-color: rgb(241 245 249) !important; }
    .records-table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        align-items: center;
    }
    .records-table-action-form { display: inline-block; }
    .records-table-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 4.25rem;
        padding: 0.35rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        box-sizing: border-box;
    }
    .records-table-btn-view { background: rgb(224 231 255); color: rgb(49 46 129); }
    .records-table-btn-view:hover { background: rgb(199 210 254); }
    .records-table-btn-edit { background: rgb(229 231 235); color: rgb(55 65 81); }
    .records-table-btn-edit:hover { background: rgb(209 213 219); }
    .records-table-btn-image, .records-table-btn-attach { background: rgb(219 234 254); color: rgb(29 78 216); }
    .records-table-btn-image:hover, .records-table-btn-attach:hover { background: rgb(191 219 254); }
    .records-table-btn-delete { background: rgb(254 226 226); color: rgb(153 27 27); }
    .records-table-btn-delete:hover { background: rgb(254 202 202); }
    .records-table-remove-image-form { display: inline-block; }
    .records-table-btn-remove-image {
        min-width: 4.25rem;
        padding: 0.35rem 0.5rem;
        font-size: 1.1rem;
        line-height: 1;
        font-weight: 700;
        background: rgb(254 226 226);
        color: rgb(153 27 27);
    }
    .records-table-btn-remove-image:hover { background: rgb(254 202 202); }
</style>
@endpush

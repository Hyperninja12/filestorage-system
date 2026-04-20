{{-- Detail sa record: back link, card nga naay header ug data table, image section. --}}
@extends('layouts.app')

@section('title', 'Record #' . $record->getDisplayNumber())

@section('content')
    @php
        $listQuery = array_filter(
            request()->only(['page', 'search', 'person_responsible', 'type']),
            fn ($v) => $v !== null && $v !== ''
        );
    @endphp
    <div class="record-detail">
        <a href="{{ route('records.index', $listQuery) }}" class="app-back-btn mb-6">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to records
        </a>

        {{-- Search & Filter Global Section --}}
        <div class="futuristic-card rounded-xl mb-8 overflow-hidden animate-staggered" style="animation-delay: 50ms;">
            <div class="flex items-center gap-2 px-5 py-3.5 bg-slate-800/80 border-b border-cyan-900/30 text-sm font-semibold text-cyan-300">
                <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span>Search & filter global records</span>
            </div>
            <form action="{{ route('records.index') }}" method="GET" class="p-5 flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:flex-none md:w-32 relative">
                    <label for="type" class="block text-xs font-semibold text-cyan-200/70 mb-1.5 flex items-center font-sans tracking-tight text-shadow-glow">Type</label>
                    <select name="type" id="type" class="w-full px-3 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-shadow text-slate-100 appearance-none pb-[9px] shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
                        <option value="all" {{ request('type') === 'all' || !request('type') ? 'selected' : '' }}>All</option>
                        <option value="par" {{ request('type') === 'par' ? 'selected' : '' }}>PAR</option>
                        <option value="ics" {{ request('type') === 'ics' ? 'selected' : '' }}>ICS</option>
                    </select>
                </div>

                <div class="w-full md:flex-1 relative">
                    <label for="search" class="block text-xs font-semibold text-cyan-200/70 mb-1.5 flex items-center font-sans tracking-tight text-shadow-glow">Search (any column)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Type to search..." class="w-full pl-9 pr-4 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-shadow font-sans shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
                    </div>
                </div>

                <div class="w-full md:flex-1 relative">
                    <label for="person_responsible" class="block text-xs font-semibold text-cyan-200/70 mb-1.5 flex items-center font-sans tracking-tight text-shadow-glow">By Person Responsible</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <input type="text" name="person_responsible" id="person_responsible" value="{{ request('person_responsible') }}" placeholder="Name or part of name" class="w-full pl-9 pr-4 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-shadow font-sans shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-2 md:mt-0 font-sans">
                    <button type="submit" class="px-5 py-2 bg-cyan-900/40 hover:bg-cyan-800/60 text-cyan-300 border border-cyan-500/50 text-sm font-medium rounded-lg transition-all focus:ring-2 focus:ring-cyan-500 focus:outline-none shadow-[0_0_10px_rgba(0,240,255,0.1)] hover:shadow-[0_0_15px_rgba(0,240,255,0.3)] tracking-tight">Search</button>
                    <a href="{{ route('records.index') }}" class="px-5 py-2 bg-slate-800 border border-slate-600 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-slate-500 focus:outline-none tracking-tight">Clear</a>
                </div>
            </form>
        </div>
        <div class="record-detail-card">
            <div class="record-detail-header">
                <h1 class="record-detail-title">Record #{{ $record->getDisplayNumber() }}</h1>
                <div class="record-detail-actions">
                    <a href="{{ route('records.edit', array_merge(['record' => $record], $listQuery)) }}" class="record-detail-btn record-detail-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                        Edit
                    </a>
                    <a href="{{ route('records.print', $record) }}" target="_blank" class="record-detail-btn record-detail-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318-4.171a3.023 3.023 0 0 0-3.064-2.83H9.416a3.023 3.023 0 0 0-3.064 2.83M16.5 12V6.375a1.875 1.875 0 0 0-1.875-1.875h-5.25A1.875 1.875 0 0 0 7.5 6.375V12m9 0h-9" /></svg>
                        Print
                    </a>
                    @if (count($record->getImagePaths()) > 0)
                        <button type="button" onclick="previewImage('{{ route('records.image', [$record, 0]) }}')" class="record-detail-btn record-detail-btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            Preview images
                        </button>
                    @endif
                    @if (count($record->getImagePaths()) < 2)
                        <form action="{{ route('records.attach-image', $record) }}" method="POST" enctype="multipart/form-data" class="inline" id="attach-form">
                            @csrf
                            <input type="file" name="image" accept="image/*" id="attach-file" class="hidden" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('attach-file').click()" class="record-detail-btn record-detail-btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32a1.5 1.5 0 0 1-2.121-2.121l10.94-10.94" /></svg>
                                Attach image ({{ count($record->getImagePaths()) }}/2)
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="inline"
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
                        <button type="submit" class="record-detail-btn record-detail-btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="record-detail-body">
                <table class="detail-table w-full border-collapse">
                    <tbody>
                        @foreach ($columns as $col)
                            @php
                                $val = $record->getColumn($col);
                                if (in_array($col, ['Unit Value', 'On Hand Value'], true)) {
                                    $display = \App\Support\CashFormatter::formatOrPlaceholder($val);
                                } else {
                                    $display = ($val !== null && $val !== '') ? $val : '—';
                                }
                            @endphp
                            <tr class="detail-table-tr">
                                <th class="detail-table-th">{{ $col }}</th>
                                <td class="detail-table-td @if(in_array($col, ['Unit Value', 'On Hand Value'], true)) detail-table-td-cash @endif">{{ $display }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (count($record->getImagePaths()) > 0)
                <div class="record-detail-image-section">
                    <p class="record-detail-image-label">Attached images ({{ count($record->getImagePaths()) }}/2)</p>
                    <div class="record-detail-image-grid">
                        @foreach ($record->getImagePaths() as $idx => $path)
                            <div class="record-detail-image-item">
                                <img src="{{ route('records.image', [$record, $idx]) }}" alt="Image {{ $idx + 1 }}" class="record-detail-image">
                                <form action="{{ route('records.remove-image', [$record, $idx]) }}" method="POST" class="record-detail-image-remove-form"
                                    data-app-confirm="1"
                                    data-app-confirm-title="Remove this image?"
                                    data-app-confirm-message="This image will be removed from the record."
                                    data-app-confirm-ok="Remove"
                                    data-app-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="record-detail-image-remove-btn" title="Remove image" aria-label="Remove image">×</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>



        <div id="image-modal" class="records-modal-overlay hidden" onclick="closePreview()">
            <div class="records-modal-box" onclick="event.stopPropagation()">
                <img id="preview-img" src="" alt="Preview" class="records-modal-img">
                <button type="button" onclick="closePreview()" class="records-modal-close">Close</button>
            </div>
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
    </script>
@endsection

@push('styles')
<style>
    .record-detail { }
    .record-detail .app-back-btn { margin-bottom: 1.25rem; }
    .record-detail-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border-radius: 1rem;
        box-shadow: 0 0 20px rgba(0,240,255,0.05), inset 0 1px 0 rgba(255,255,255,0.05);
        border: 1px solid rgba(0, 240, 255, 0.2);
        overflow: hidden;
    }
    .record-detail-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: rgba(15, 23, 42, 0.8);
        border-bottom: 1px solid rgba(0, 240, 255, 0.2);
    }
    .record-detail-title { font-size: 1.25rem; font-weight: 700; color: #f8fafc; margin: 0; text-shadow: 0 0 10px rgba(0,240,255,0.3); }
    .record-detail-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .record-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    .record-detail-btn svg { width: 16px; height: 16px; stroke-width: 2; flex-shrink: 0; }
    .record-detail-btn-primary { background: rgba(0, 240, 255, 0.15); color: #00f0ff; border-color: rgba(0, 240, 255, 0.4); box-shadow: 0 0 10px rgba(0, 240, 255, 0.2); }
    .record-detail-btn-primary:hover { background: rgba(0, 240, 255, 0.3); transform: translateY(-1px); box-shadow: 0 0 15px rgba(0, 240, 255, 0.4); color: #fff; }
    .record-detail-btn-secondary { background: rgba(191, 0, 255, 0.15); color: #e879f9; border-color: rgba(191, 0, 255, 0.4); box-shadow: 0 0 10px rgba(191, 0, 255, 0.2); }
    .record-detail-btn-secondary:hover { background: rgba(191, 0, 255, 0.3); transform: translateY(-1px); box-shadow: 0 0 15px rgba(191, 0, 255, 0.4); color: #fff; }
    .record-detail-btn-danger { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 0 10px rgba(239, 68, 68, 0.2); }
    .record-detail-btn-danger:hover { background: rgba(239, 68, 68, 0.3); transform: translateY(-1px); box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); color: #fff; }
    .record-detail-body { overflow-x: auto; }
    .record-detail-image-section { padding: 1.5rem; border-top: 1px solid rgba(0, 240, 255, 0.2); background: rgba(5, 5, 17, 0.4); }
    .record-detail-image-label { font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0; }
    .record-detail-image-grid { display: flex; flex-wrap: wrap; gap: 1rem; }
    .record-detail-image-item { position: relative; display: inline-block; }
    .record-detail-image-remove-form { position: absolute; top: 0.5rem; right: 0.5rem; }
    .record-detail-image-remove-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem;
        padding: 0; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 0.375rem;
        background: rgba(239, 68, 68, 0.2); color: #fca5a5;
        font-size: 1.25rem; line-height: 1; font-weight: 700; cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }
    .record-detail-image-remove-btn:hover { background: rgba(239, 68, 68, 0.4); color: #fff; }
    .record-detail-image { border-radius: 0.5rem; border: 1px solid rgba(0, 240, 255, 0.3); box-shadow: 0 0 15px rgba(0,240,255,0.1); max-width: 20rem; max-height: 16rem; object-fit: contain; display: block; }
    .detail-table { font-size: 0.875rem; }
    .detail-table-th {
        width: 12rem;
        min-width: 10rem;
        padding: 0.75rem 1.25rem;
        text-align: left;
        font-weight: 600;
        color: #00f0ff;
        background: rgba(15, 23, 42, 0.8);
        border-right: 1px solid rgba(0, 240, 255, 0.15);
        vertical-align: top;
        text-shadow: 0 0 5px rgba(0, 240, 255, 0.2);
    }
    .detail-table-td {
        padding: 0.75rem 1.25rem;
        color: #e2e8f0;
        vertical-align: top;
        word-break: break-word;
        white-space: pre-wrap;
        max-width: 40rem;
    }
    .detail-table-td-cash {
        font-variant-numeric: tabular-nums;
        white-space: normal;
    }
    .detail-table-tr:nth-child(even) .detail-table-th { background: rgba(5, 5, 17, 0.6); }
    .detail-table-tr:nth-child(even) .detail-table-td { background: rgba(15, 23, 42, 0.4); }
    .records-modal-overlay { position: fixed; inset: 0; background: rgba(5, 5, 17, 0.85); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 10050; }
    .records-modal-overlay.records-modal-open { display: flex; }
    .records-modal-box { max-width: 90vw; max-height: 90vh; padding: 1rem; }
    .records-modal-img { max-width: 100%; max-height: 80vh; border-radius: 0.75rem; box-shadow: 0 0 30px rgba(0,240,255,0.2); border: 1px solid rgba(0,240,255,0.4); }
    .records-modal-close { margin-top: 1rem; width: 100%; padding: 0.625rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(0, 240, 255, 0.3); color: #00f0ff; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .records-modal-close:hover { background: rgba(0, 240, 255, 0.15); color: #fff; box-shadow: 0 0 15px rgba(0, 240, 255, 0.3); }
</style>
@endpush

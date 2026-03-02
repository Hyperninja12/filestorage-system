{{-- Records list: search/filter bar, then data table with all columns and styled design. Empty cells use placeholder. --}}
@extends('layouts.app')

@section('title', 'Records')

@section('content')
    <div class="records-header">
        <div>
            <h1 class="records-title">Records</h1>
            <p class="records-subtitle">Search, view, and manage imported data</p>
        </div>
        <a href="{{ route('import.create') }}" class="records-import-btn">Import CSV/Excel</a>
    </div>

    {{-- Search and filter card --}}
    <div class="records-search-card">
        <form action="{{ route('records.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search (any column)</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            @if (count($headers) > 0)
            <div class="w-40">
                <label for="column" class="block text-sm font-medium text-gray-700 mb-1">Filter by column</label>
                <select name="column" id="column" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Any —</option>
                    @foreach ($headers as $h)
                        <option value="{{ $h }}" {{ request('column') === $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Filter value</label>
                <input type="text" name="value" id="value" value="{{ request('value') }}" placeholder="Value..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            @endif
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium">Search</button>
            @if (request()->hasAny(['search', 'column', 'value']))
                <a href="{{ route('records.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    @if ($records->isEmpty())
        <div class="records-empty">
            <div class="records-empty-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.558l.256 1.128a2.25 2.25 0 002.013 1.58H21.75M2.25 13.5a2.25 2.25 0 00-2.25 2.25v2.25c0 1.114.84 2.03 1.972 2.03 1.171 0 2.18-.879 2.18-2.03V15.75m0-2.25c0-1.114-.84-2.03-1.972-2.03H2.25M15.75 9v2.25m0-2.25v-2.25m0 2.25h2.25m-2.25 0h-2.25" /></svg>
            </div>
            <p class="records-empty-text">No records yet.</p>
            <a href="{{ route('import.create') }}" class="records-empty-link">Import a CSV or Excel file</a> to get started.
        </div>
    @else
        {{-- Table wrapper: horizontal scroll so all columns fit; design applied to table --}}
        <div class="records-table-card overflow-hidden">
            <div class="overflow-x-auto overflow-y-visible">
                <table class="records-table min-w-full border-collapse">
                    <thead class="bg-slate-700 text-white sticky top-0 z-10">
                        <tr>
                            @foreach ($headers as $h)
                                <th class="records-table-th">{{ $h }}</th>
                            @endforeach
                            <th class="records-table-th records-table-th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($records as $record)
                            <tr class="records-table-tr border-b border-gray-200 hover:bg-slate-50">
                                @foreach ($headers as $h)
                                    @php
                                        $val = $record->getColumn($h);
                                        $display = ($val !== null && $val !== '') ? Str::words($val, 8) : '—';
                                    @endphp
                                    <td class="records-table-td" title="{{ $val ?? '' }}">
                                        <span class="records-table-cell">{{ $display }}</span>
                                    </td>
                                @endforeach
                                <td class="records-table-td records-table-td-actions">
                                    <div class="records-table-actions">
                                        <a href="{{ route('records.show', $record) }}" class="records-table-btn records-table-btn-view">View</a>
                                        <a href="{{ route('records.edit', $record) }}" class="records-table-btn records-table-btn-edit">Edit</a>
                                        @if ($record->image_path)
                                            <button type="button" onclick="previewImage('{{ route('records.image', $record) }}')" class="records-table-btn records-table-btn-image">Image</button>
                                        @else
                                            <button type="button" onclick="document.getElementById('attach-{{ $record->id }}').click()" class="records-table-btn records-table-btn-attach">Attach</button>
                                            <form id="form-{{ $record->id }}" action="{{ route('records.attach-image', $record) }}" method="POST" enctype="multipart/form-data" class="hidden">
                                                @csrf
                                                <input type="file" name="image" id="attach-{{ $record->id }}" accept="image/*" onchange="this.form.submit()">
                                            </form>
                                        @endif
                                        <form action="{{ route('records.destroy', $record) }}" method="POST" class="records-table-action-form" onsubmit="return confirm('Delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="records-table-btn records-table-btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                {{ $records->links() }}
            </div>
        </div>

        {{-- Image preview modal --}}
        <div id="image-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50" onclick="closePreview()">
            <div class="max-w-4xl max-h-[90vh] p-4" onclick="event.stopPropagation()">
                <img id="preview-img" src="" alt="Preview" class="max-w-full max-h-[85vh] rounded-lg shadow-xl">
                <button type="button" onclick="closePreview()" class="mt-2 w-full py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">Close</button>
            </div>
        </div>
        <script>
            function previewImage(url) {
                document.getElementById('preview-img').src = url;
                document.getElementById('image-modal').classList.remove('hidden');
                document.getElementById('image-modal').classList.add('flex');
            }
            function closePreview() {
                document.getElementById('image-modal').classList.add('hidden');
                document.getElementById('image-modal').classList.remove('flex');
            }
        </script>
    @endif
@endsection

@push('styles')
{{-- Records page and table design --}}
<style>
    .records-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .records-title { font-size: 1.625rem; font-weight: 600; color: rgb(30 41 59); margin: 0 0 0.25rem 0; }
    .records-subtitle { font-size: 0.9375rem; color: rgb(100 116 139); margin: 0; }
    .records-import-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, rgb(99 102 241) 0%, rgb(79 70 229) 100%);
        color: white;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
    }
    .records-import-btn:hover { opacity: 0.95; }
    .records-search-card {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.06);
        border: 1px solid rgb(226 232 240);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
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
    .records-empty-text { color: rgb(100 116 139); margin-bottom: 0.5rem; }
    .records-empty-link { color: rgb(99 102 241); font-weight: 500; }
    .records-empty-link:hover { text-decoration: underline; }
    .records-table-card {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.06);
        border: 1px solid rgb(226 232 240);
    }
    .records-table { font-size: 0.8125rem; }
    .records-table-th {
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        min-width: 5rem;
        max-width: 12rem;
        border-bottom: 2px solid rgb(51 65 85);
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
    .records-table-cell {
        display: block;
        max-height: 3.5em;
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
</style>
@endpush

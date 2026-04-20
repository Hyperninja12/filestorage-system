{{-- Lista sa records: search/filter bar, dayon data table nga naay tanang columns. Empty cells placeholder. --}}
@extends('layouts.app')

@section('title', 'Records')

@section('content')
    @php
        $listQuery = array_filter(
            request()->only(['page', 'search', 'person_responsible', 'type']),
            fn($v) => $v !== null && $v !== ''
        );
    @endphp
    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 animate-staggered" aria-label="Breadcrumb" style="animation-delay: 0ms;">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-gray-500">
            <li class="inline-flex items-center">
                <a href="/" class="hover:text-indigo-600 transition-colors">Home</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" width="16" height="16">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 md:ml-2">Data Management</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 animate-staggered" style="animation-delay: 100ms;">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            <div>
                <h1 class="text-2xl font-bold text-white leading-tight tracking-tight text-shadow-glow">Records</h1>
                <p class="text-sm text-cyan-200/70 mt-1">Search, view, and manage imported data</p>
            </div>

            {{-- Summary Stats Badges --}}
            <div class="flex items-center gap-3">
                <div class="px-3 py-1.5 futuristic-card rounded-lg flex items-center gap-2">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total</span>
                    <span class="text-sm font-bold text-white">{{ number_format($totalCount ?? 0) }}</span>
                </div>
                <div class="px-3 py-1.5 futuristic-card rounded-lg flex items-center gap-2">
                    <span class="text-[10px] uppercase font-bold text-cyan-400 tracking-wider text-shadow-glow">PAR</span>
                    <span class="text-sm font-bold text-white">{{ number_format($parCount ?? 0) }}</span>
                </div>
                <div class="px-3 py-1.5 futuristic-card rounded-lg flex items-center gap-2">
                    <span class="text-[10px] uppercase font-bold text-purple-400 tracking-wider text-shadow-glow">ICS</span>
                    <span class="text-sm font-bold text-white">{{ number_format($icsCount ?? 0) }}</span>
                </div>
            </div>
        </div>

        <a href="{{ route('records.create') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] border border-cyan-400/50 hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] transition-all transform hover:-translate-y-0.5">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2.5" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add record
        </a>
    </div>

    {{-- PAR / ICS switch: Segmented Control --}}
    @php $currentType = $type ?? request('type', 'all'); @endphp
    {{-- PAR / ICS switch: Segmented Control with counts --}}
    <div class="flex items-center gap-3 mb-8 animate-staggered" style="animation-delay: 200ms;">
        <span class="text-sm font-semibold text-slate-400">Filter By:</span>
        <div class="inline-flex p-1 bg-slate-900/50 rounded-xl backdrop-blur-md border border-cyan-900/50 shadow-[0_0_15px_rgba(0,240,255,0.05)]">
            <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'all'])) }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $currentType === 'all' ? 'bg-cyan-900/40 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)] border border-cyan-500/30' : 'text-slate-400 hover:text-cyan-200 hover:bg-slate-800/50' }}">
                <span>All Records</span>
                <span
                    class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentType === 'all' ? 'bg-cyan-500/20 text-cyan-300' : 'bg-slate-800/80 text-slate-500' }}">
                    {{ $totalCount ?? 0 }}
                </span>
            </a>
            <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'par'])) }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $currentType === 'par' ? 'bg-cyan-900/40 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)] border border-cyan-500/30' : 'text-slate-400 hover:text-cyan-200 hover:bg-slate-800/50' }}">
                <span>PAR</span>
                <span
                    class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentType === 'par' ? 'bg-cyan-500/20 text-cyan-300' : 'bg-slate-800/80 text-slate-500' }}">
                    {{ $parCount ?? 0 }}
                </span>
            </a>
            <a href="{{ route('records.index', array_merge(request()->only(['search', 'person_responsible']), ['type' => 'ics'])) }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $currentType === 'ics' ? 'bg-cyan-900/40 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)] border border-cyan-500/30' : 'text-slate-400 hover:text-cyan-200 hover:bg-slate-800/50' }}">
                <span>ICS</span>
                <span
                    class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentType === 'ics' ? 'bg-cyan-500/20 text-cyan-300' : 'bg-slate-800/80 text-slate-500' }}">
                    {{ $icsCount ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="futuristic-card rounded-xl mb-6 overflow-hidden animate-staggered" style="animation-delay: 300ms;">
        <div
            class="flex items-center gap-2 px-5 py-3.5 bg-slate-800/80 border-b border-cyan-900/30 text-sm font-semibold text-cyan-300">
            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
            </svg>
            <span>Search & filter</span>
        </div>
        <form action="{{ route('records.index') }}" method="GET" class="p-5 flex flex-col md:flex-row gap-4 items-end">
            @if ($currentType !== 'all')
                <input type="hidden" name="type" value="{{ $currentType }}">
            @endif

            <div class="w-full md:flex-1 relative">
                <label for="search" class="block text-xs font-semibold text-cyan-200/70 mb-1.5 text-shadow-glow">Search (any column)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Type to search..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
                </div>
            </div>

            <div class="w-full md:flex-1 relative">
                <label for="person_responsible" class="block text-xs font-semibold text-cyan-200/70 mb-1.5 text-shadow-glow">By Person
                    Responsible</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <input type="text" name="person_responsible" id="person_responsible"
                        value="{{ request('person_responsible') }}" placeholder="Name or part of name"
                        class="w-full pl-9 pr-4 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
                </div>
            </div>

            <div class="flex items-center gap-2 mt-2 md:mt-0">
                <button type="submit"
                    class="px-5 py-2 bg-cyan-900/40 hover:bg-cyan-800/60 text-cyan-300 border border-cyan-500/50 text-sm font-medium rounded-lg transition-all focus:ring-2 focus:ring-cyan-500 focus:outline-none shadow-[0_0_10px_rgba(0,240,255,0.1)] hover:shadow-[0_0_15px_rgba(0,240,255,0.3)]">Search</button>
                @if (request()->hasAny(['search', 'person_responsible', 'type']))
                    <a href="{{ route('records.index') }}"
                        class="px-5 py-2 bg-slate-800 border border-slate-600 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-slate-500 focus:outline-none">Clear</a>
                @endif
                @if (request()->filled('person_responsible') || in_array(request('type'), ['par', 'ics']))
                    <a href="{{ route('records.print-list', request()->query()) }}"
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium rounded-lg inline-flex items-center transition-all focus:ring-2 focus:ring-purple-500 focus:outline-none border border-purple-400 shadow-[0_0_15px_rgba(191,0,255,0.4)]"
                        data-no-app-loading>
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($records->isEmpty())
        <div class="records-empty">
            <div class="records-empty-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.558l.256 1.128a2.25 2.25 0 002.013 1.58H21.75M2.25 13.5a2.25 2.25 0 00-2.25 2.25v2.25c0 1.114.84 2.03 1.972 2.03 1.171 0 2.18-.879 2.18-2.03V15.75m0-2.25c0-1.114-.84-2.03-1.972-2.03H2.25M15.75 9v2.25m0-2.25v-2.25m0 2.25h2.25m-2.25 0h-2.25" />
                </svg>
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
        <div class="records-table-card overflow-hidden animate-staggered" style="animation-delay: 400ms;">
            <div class="records-table-scroll-area">
                <div id="records-top-scroll" class="records-table-top-scroll" aria-hidden="true">
                    <div id="records-top-scroll-inner" class="records-table-top-scroll-inner"></div>
                </div>
                <div id="records-table-body" class="records-table-body-wrap overflow-y-visible">
                    <table class="records-table min-w-full border-collapse" id="records-data-table">
                        <thead class="records-table-thead sticky top-0 z-10">
                            <tr>
                                <th class="records-table-th records-table-th-actions sticky left-0 z-30 bg-slate-900 border-r border-cyan-900/30 shadow-[4px_0_8px_-2px_rgba(0,0,0,0.4)]"
                                    style="background-color: rgba(15, 23, 42, 0.95);">Actions</th>
                                @foreach ($headers as $h)
                                    <th class="records-table-th">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-transparent">
                            @foreach ($records as $record)
                                <tr class="group border-b border-cyan-900/20 hover:bg-slate-800/50 transition-colors">
                                    <td
                                        class="px-3 py-2 align-middle sticky left-0 z-20 bg-slate-900/90 group-hover:bg-slate-800 shadow-[4px_0_8px_-2px_rgba(0,0,0,0.3)] border-r border-cyan-900/20 transition-colors">
                                        <div class="flex items-center gap-1.5 min-w-max">
                                            <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}"
                                                title="View Details"
                                                class="p-1.5 text-emerald-400 bg-emerald-900/30 hover:bg-emerald-800/50 hover:text-emerald-300 rounded-full transition-colors border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('records.edit', array_merge(['record' => $record], $listQuery)) }}"
                                                title="Edit Record"
                                                class="p-1.5 text-cyan-400 bg-cyan-900/30 hover:bg-cyan-800/50 hover:text-cyan-300 rounded-full transition-colors border border-cyan-500/20 shadow-[0_0_10px_rgba(34,211,238,0.1)]">
                                                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>
                                            @php $imgPaths = $record->getImagePaths();
                                            $imgCount = count($imgPaths); @endphp
                                            @if ($imgCount > 0)
                                                <button type="button"
                                                    onclick="previewImage('{{ route('records.image', [$record, 0]) }}')"
                                                    title="{{ $imgCount > 1 ? 'Images (' . $imgCount . ')' : 'Image' }}"
                                                    class="p-1.5 text-sky-400 bg-sky-900/30 hover:bg-sky-800/50 hover:text-sky-300 rounded-full transition-colors relative border border-sky-500/20 shadow-[0_0_10px_rgba(14,165,233,0.1)]">
                                                    <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                    </svg>
                                                    @if($imgCount > 1) <span
                                                        class="absolute -top-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-cyan-500 text-[9px] font-bold text-slate-900 ring-1 ring-slate-800 shadow-[0_0_5px_rgba(6,182,212,0.6)]">{{$imgCount}}</span>
                                                    @endif
                                                </button>

                                                <form action="{{ route('records.remove-image', [$record, 0]) }}" method="POST"
                                                    class="inline-block" data-app-confirm="1"
                                                    data-app-confirm-title="Remove first image?"
                                                    data-app-confirm-message="The first attached image will be removed from this record."
                                                    data-app-confirm-ok="Remove" data-app-confirm-variant="danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Remove first image"
                                                        class="p-1.5 text-red-500 bg-red-900/30 hover:bg-red-800/50 hover:text-red-400 rounded-full transition-colors border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.1)]">
                                                        <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($imgCount < 2)
                                                <button type="button"
                                                    onclick="document.getElementById('attach-{{ $record->id }}').click()"
                                                    title="Attach Image"
                                                    class="p-1.5 text-sky-400 bg-sky-900/30 hover:bg-sky-800/50 hover:text-sky-300 rounded-full transition-colors border border-sky-500/20 shadow-[0_0_10px_rgba(14,165,233,0.1)]">
                                                    <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32a1.5 1.5 0 0 1-2.121-2.121l10.94-10.94" />
                                                    </svg>
                                                </button>
                                                <form id="form-{{ $record->id }}" action="{{ route('records.attach-image', $record) }}"
                                                    method="POST" enctype="multipart/form-data" class="hidden">
                                                    @csrf
                                                    <input type="file" name="image" id="attach-{{ $record->id }}" accept="image/*"
                                                        onchange="this.form.submit()">
                                                </form>
                                            @endif
                                            <form action="{{ route('records.destroy', $record) }}" method="POST"
                                                class="inline-block" data-app-confirm="1"
                                                data-app-confirm-title="Delete this record?"
                                                data-app-confirm-message="This action cannot be undone."
                                                data-app-confirm-ok="Delete" data-app-confirm-variant="danger">
                                                @csrf
                                                @method('DELETE')
                                                @foreach ($listQuery as $key => $value)
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                @endforeach
                                                <button type="submit" title="Delete Record"
                                                    class="p-1.5 text-red-500 bg-red-900/30 hover:bg-red-800/50 hover:text-red-400 rounded-full transition-colors ml-1 border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.1)]">
                                                    <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
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
                                            
                                            // Classification logic for PAR/ICS badges
                                            $isCategory = $h === 'Category';
                                            $isPar = false;
                                            if ($isCategory) {
                                                $unitPriceString = $record->getColumn('Unit Value');
                                                $parsedPrice = (float) str_replace([',', '₱', ' '], '', $unitPriceString ?? '0');
                                                $isPar = $parsedPrice >= 50000;
                                            }
                                        @endphp
                                        <td class="px-3 py-2 align-middle text-sm {{ $isMoney ? 'tabular-nums whitespace-nowrap' : '' }}"
                                            title="{{ $title }}">
                                            <div class="flex items-center gap-2">
                                                @if($isCategory)
                                                    @if($isPar)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-900/40 text-cyan-400 border border-cyan-500/30 uppercase tracking-tighter shadow-[0_0_10px_rgba(34,211,238,0.2)]">PAR</span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-900/40 text-purple-400 border border-purple-500/30 uppercase tracking-tighter shadow-[0_0_10px_rgba(191,0,255,0.2)]">ICS</span>
                                                    @endif
                                                @endif
                                                <div class="truncate max-w-xs text-slate-300">
                                                    {{ $display }}
                                                </div>
                                            </div>
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
            (function () {
                var topScroll = document.getElementById('records-top-scroll');
                var topInner = document.getElementById('records-top-scroll-inner');
                var bodyWrap = document.getElementById('records-table-body');
                var table = document.getElementById('records-data-table');
                var scrollArea = document.querySelector('.records-table-scroll-area');
                if (!topScroll || !bodyWrap || !table) return;

                function syncWidth() {
                    topInner.style.width = table.scrollWidth + 'px';
                    checkScrollShadows();
                }

                function checkScrollShadows() {
                    if (!scrollArea) return;
                    if (bodyWrap.scrollLeft + bodyWrap.clientWidth < bodyWrap.scrollWidth - 2) {
                        scrollArea.classList.add('has-scroll-right');
                    } else {
                        scrollArea.classList.remove('has-scroll-right');
                    }
                }

                function scrollTopFromBody() {
                    topScroll.scrollLeft = bodyWrap.scrollLeft;
                    checkScrollShadows();
                }
                function scrollBodyFromTop() {
                    bodyWrap.scrollLeft = topScroll.scrollLeft;
                    checkScrollShadows();
                }

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
        .records-pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(0, 240, 255, 0.2);
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
        }

        /* Update vendor pagination text colors via nested targeting */
        .records-pagination nav {
            color: #cbd5e1;
            font-size: 0.875rem;
        }

        .records-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(5, 5, 17, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10050;
        }

        .records-modal-overlay.records-modal-open {
            display: flex;
        }

        .records-modal-box {
            max-width: 90vw;
            max-height: 90vh;
            padding: 1rem;
        }

        .records-modal-img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 0.75rem;
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.2);
            border: 1px solid rgba(0, 240, 255, 0.4);
        }

        .records-modal-close {
            margin-top: 1rem;
            width: 100%;
            padding: 0.625rem 1rem;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: #00f0ff;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-shadow: 0 0 8px rgba(0, 240, 255, 0.4);
        }

        .records-modal-close:hover {
            background: rgba(0, 240, 255, 0.15);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
            color: #fff;
        }

        .records-empty {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            border: 1px dashed rgba(0, 240, 255, 0.3);
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.05);
        }

        .records-empty-icon {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1rem;
            color: rgba(0, 240, 255, 0.5);
            filter: drop-shadow(0 0 8px rgba(0, 240, 255, 0.3));
        }

        .records-empty-icon svg {
            width: 100%;
            height: 100%;
        }

        .records-empty-text {
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }

        .records-empty-links {
            color: #cbd5e1;
            margin: 0;
            font-size: 0.9375rem;
        }

        .records-empty-link {
            color: #00f0ff;
            font-weight: 500;
            text-decoration: none;
            text-shadow: 0 0 8px rgba(0, 240, 255, 0.3);
        }

        .records-empty-link:hover {
            color: #67e8f9;
            text-decoration: underline;
        }

        .records-table-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 240, 255, 0.2);
            overflow: hidden;
        }

        .records-table-scroll-area {
            position: relative;
        }

        .records-table-scroll-area::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1.5rem;
            background: linear-gradient(to right, rgba(15, 23, 42, 0) 0%, rgba(5, 5, 17, 0.8) 100%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 20;
        }

        .records-table-scroll-area.has-scroll-right::after {
            opacity: 1;
        }

        .records-table-top-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            height: 6px;
            flex-shrink: 0;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
        }

        .records-table-top-scroll::-webkit-scrollbar {
            height: 5px;
            background: transparent;
        }

        .records-table-top-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .records-table-top-scroll::-webkit-scrollbar-thumb {
            background-color: transparent;
            border-radius: 9999px;
            transition: background-color 0.3s ease;
        }

        .records-table-card:hover .records-table-top-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(0, 240, 255, 0.3);
        }

        .records-table-card:hover .records-table-top-scroll::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0, 240, 255, 0.6);
        }

        .records-table-top-scroll-inner {
            display: inline-block;
            min-width: 1px;
            height: 1px;
        }

        .records-table-body-wrap {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
        }

        .records-table-body-wrap::-webkit-scrollbar {
            height: 5px;
            width: 5px;
            background: transparent;
        }

        .records-table-body-wrap::-webkit-scrollbar-track {
            background: transparent;
        }

        .records-table-body-wrap::-webkit-scrollbar-thumb {
            background-color: transparent;
            border-radius: 9999px;
        }

        .records-table-card:hover .records-table-body-wrap::-webkit-scrollbar-thumb {
            background-color: rgba(0, 240, 255, 0.3);
        }

        .records-table-body-wrap::-webkit-scrollbar-thumb:hover,
        .records-table-card:hover .records-table-body-wrap::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0, 240, 255, 0.6);
        }

        .records-table {
            font-size: 0.8125rem;
            color: #e2e8f0;
        }

        .records-table thead {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
        }

        .records-table-th {
            padding: 0.5rem 0.75rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            min-width: 5rem;
            max-width: 12rem;
            border-bottom: 1px solid rgba(0, 240, 255, 0.2);
            color: #00f0ff;
            font-size: 0.75rem;
            text-shadow: 0 0 5px rgba(0, 240, 255, 0.3);
        }

        .records-table-th-actions {
            min-width: 9rem;
            max-width: none;
        }
    </style>
@endpush
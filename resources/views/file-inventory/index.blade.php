@extends('layouts.app')

@section('title', 'File Inventory')

@section('content')
    {{-- Back Button --}}
    <div class="mb-4 animate-staggered" style="animation-delay: 0ms;">
        @if($currentFolder && $currentFolder->parent_id)
            <a href="{{ route('file-inventory.folder', $currentFolder->parent_id) }}" class="app-back-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        @elseif($currentFolder)
            <a href="{{ route('file-inventory.index') }}" class="app-back-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        @else
            <a href="{{ route('records.index') }}" class="app-back-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Records
            </a>
        @endif
    </div>

    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 animate-staggered" aria-label="Breadcrumb" style="animation-delay: 0ms;">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-gray-500">
            <li class="inline-flex items-center">
                <a href="/" class="hover:text-cyan-400 transition-colors">Home</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('file-inventory.index') }}"
                        class="hover:text-cyan-400 transition-colors {{ !$currentFolder ? 'text-cyan-500' : '' }}">File
                        Inventory</a>
                </div>
            </li>
            @foreach($breadcrumbs as $crumb)
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('file-inventory.folder', $crumb->id) }}"
                            class="hover:text-cyan-400 transition-colors {{ $currentFolder && $currentFolder->id === $crumb->id ? 'text-cyan-500' : '' }}">{{ $crumb->name }}</a>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- Header & Stats --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 animate-staggered"
        style="animation-delay: 100ms;">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            <div>
                <h1 class="text-2xl font-bold text-white leading-tight tracking-tight text-shadow-glow">
                    {{ $currentFolder ? $currentFolder->name : 'File Inventory' }}
                </h1>
                <p class="text-sm text-cyan-200/70 mt-1">Manage, view, and organize your files</p>
            </div>

            @if(!$currentFolder && empty($search))
                <div class="flex items-center gap-3">
                    <div
                        class="px-3 py-1.5 bg-slate-900/60 border border-cyan-900/30 rounded-lg flex items-center gap-2 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Files</span>
                        <span class="text-sm font-bold text-white">{{ number_format($totalFiles) }}</span>
                    </div>
                    <div
                        class="px-3 py-1.5 bg-slate-900/60 border border-cyan-900/30 rounded-lg flex items-center gap-2 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Folders</span>
                        <span class="text-sm font-bold text-white">{{ number_format($totalFolders) }}</span>
                    </div>
                    <div
                        class="px-3 py-1.5 bg-slate-900/60 border border-cyan-900/30 rounded-lg flex items-center gap-2 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                        <span
                            class="text-[10px] uppercase font-bold text-cyan-400 tracking-wider text-shadow-glow">Storage</span>
                        <span class="text-sm font-bold text-white">{{ $formattedTotalSize }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="openModal('new-folder-modal')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-cyan-100 text-sm font-semibold rounded-lg shadow-[0_0_10px_rgba(0,0,0,0.3)] border border-cyan-900/50 hover:border-cyan-500/50 transition-all">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 10.5v6m3-3H9m4.06-7.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
                New Folder
            </button>
            <button type="button" onclick="openModal('upload-modal')"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] border border-cyan-400/50 hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Upload
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-slate-800/60 backdrop-blur-md rounded-xl border border-cyan-900/30 p-4 mb-8 animate-staggered shadow-[0_0_15px_rgba(0,0,0,0.3)]"
        style="animation-delay: 200ms;">
        <form
            action="{{ $currentFolder ? route('file-inventory.folder', $currentFolder->id) : route('file-inventory.index') }}"
            method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="w-full md:w-1/3 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search files & folders..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-900/80 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.4)]">
            </div>

            <div class="w-full md:w-auto flex items-center gap-3">
                <span class="text-xs font-semibold text-cyan-200/70">Sort by:</span>
                <select name="sort" onchange="this.form.submit()"
                    class="bg-slate-900/80 border border-slate-700 text-slate-100 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block p-2 outline-none">
                    <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Newest</option>
                    <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Oldest</option>
                    <option value="size_desc" {{ $sort === 'size_desc' ? 'selected' : '' }}>Size (Largest)</option>
                    <option value="size_asc" {{ $sort === 'size_asc' ? 'selected' : '' }}>Size (Smallest)</option>
                </select>
                <div class="flex items-center bg-slate-900/80 border border-slate-700 rounded-lg overflow-hidden">
                    <button type="button" onclick="setViewMode('grid')" id="btn-grid-view" class="p-2 text-cyan-400 bg-slate-800 transition-colors" title="Grid View">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    </button>
                    <button type="button" onclick="setViewMode('list')" id="btn-list-view" class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-800 transition-colors" title="List View">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    </button>
                </div>
                @if($search)
                    <a href="{{ $currentFolder ? route('file-inventory.folder', $currentFolder->id) : route('file-inventory.index') }}"
                        class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-xs font-medium rounded-lg text-slate-200 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Drag overlay --}}
    <div id="drag-overlay" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center border-4 border-dashed border-cyan-500 m-4 rounded-3xl pointer-events-none">
        <svg class="w-24 h-24 text-cyan-400 mb-6 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
        <h2 class="text-3xl font-bold text-white shadow-sm">Drop files here to upload</h2>
    </div>

    @if($folders->isEmpty() && $files->isEmpty())
        {{-- Empty State --}}
        <div class="bg-slate-800/40 border border-dashed border-cyan-900/50 rounded-2xl p-12 text-center animate-staggered"
            style="animation-delay: 300ms;">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-cyan-900/20 text-cyan-500/50 mb-4">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-300 mb-1">This folder is empty</h3>
            <p class="text-slate-500 text-sm mb-6">Create a new folder or upload some files to get started.</p>
            <div class="flex justify-center gap-4">
                <button onclick="openModal('new-folder-modal')"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-cyan-300 text-sm font-medium rounded-lg transition-colors border border-cyan-900/30">Create
                    Folder</button>
                <button onclick="openModal('upload-modal')"
                    class="px-4 py-2 bg-cyan-900/40 hover:bg-cyan-800/60 text-cyan-300 text-sm font-medium rounded-lg transition-colors border border-cyan-500/30">Upload
                    Files</button>
            </div>
        </div>
    @else
        <div id="items-container" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 animate-staggered pb-10"
            style="animation-delay: 300ms;">

            {{-- Folders --}}
            @foreach($folders as $folder)
                <div class="item-card group relative bg-slate-900/60 hover:bg-slate-800 border border-slate-700/50 hover:border-cyan-500/50 rounded-xl p-4 transition-all shadow-[0_4px_15px_rgba(0,0,0,0.3)] hover:shadow-[0_0_15px_rgba(6,182,212,0.2)] cursor-pointer"
                    onclick="window.location='{{ route('file-inventory.folder', $folder->id) }}'">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button" class="p-1 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-md"
                            onclick="event.stopPropagation(); toggleDropdown('folder-menu-{{ $folder->id }}')">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div id="folder-menu-{{ $folder->id }}"
                            class="hidden absolute right-0 mt-1 w-32 bg-slate-800 border border-slate-600 rounded-lg shadow-xl z-20 overflow-hidden text-sm">
                            <button type="button"
                                onclick="event.stopPropagation(); renameFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ $folder->color }}')"
                                class="w-full text-left px-4 py-2 hover:bg-slate-700 text-slate-300">Rename</button>
                            <button type="button" onclick="event.stopPropagation(); moveItem('folder', {{ $folder->id }})"
                                class="w-full text-left px-4 py-2 hover:bg-slate-700 text-slate-300">Move</button>
                            <form action="{{ route('file-inventory.folders.destroy', $folder->id) }}" method="POST"
                                data-app-confirm="1" data-app-confirm-title="Delete folder?"
                                data-app-confirm-message="All files inside this folder will also be deleted!"
                                data-app-confirm-ok="Delete" data-app-confirm-variant="danger">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation()"
                                    class="w-full text-left px-4 py-2 hover:bg-red-900/50 text-red-400">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="icon-text-wrapper flex flex-col items-center text-center gap-3">
                        <svg class="w-14 h-14" style="color: {{ $folder->color ?: '#38bdf8' }}" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z" />
                        </svg>
                        <span class="text-sm font-medium text-slate-200 line-clamp-2"
                            title="{{ $folder->name }}">{{ $folder->name }}</span>
                    </div>
                </div>
            @endforeach

            {{-- Files --}}
            @foreach($files as $file)
                <div
                    class="item-card group relative bg-slate-900/40 hover:bg-slate-800 border border-slate-700/50 hover:border-slate-500 rounded-xl p-4 transition-all shadow-[0_2px_10px_rgba(0,0,0,0.2)]">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <button type="button"
                            class="p-1 text-slate-400 hover:text-white bg-slate-800/80 hover:bg-slate-700 rounded-md backdrop-blur-sm"
                            onclick="event.stopPropagation(); toggleDropdown('file-menu-{{ $file->id }}')">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div id="file-menu-{{ $file->id }}"
                            class="hidden absolute right-0 mt-1 w-32 bg-slate-800 border border-slate-600 rounded-lg shadow-xl overflow-hidden text-sm z-20">
                            <a href="{{ route('file-inventory.files.download', $file->id) }}"
                                class="block px-4 py-2 hover:bg-slate-700 text-slate-300">Download</a>
                            <button type="button"
                                onclick="event.stopPropagation(); renameFileObj({{ $file->id }}, '{{ addslashes($file->original_name) }}')"
                                class="w-full text-left px-4 py-2 hover:bg-slate-700 text-slate-300">Rename</button>
                            <button type="button" onclick="event.stopPropagation(); moveItem('file', {{ $file->id }})"
                                class="w-full text-left px-4 py-2 hover:bg-slate-700 text-slate-300">Move</button>
                            <form action="{{ route('file-inventory.files.destroy', $file->id) }}" method="POST" data-app-confirm="1"
                                data-app-confirm-title="Delete file?"
                                data-app-confirm-message="Are you sure you want to delete '{{ $file->original_name }}'?"
                                data-app-confirm-ok="Delete" data-app-confirm-variant="danger">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 hover:bg-red-900/50 text-red-400">Delete</button>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('file-inventory.files.show', $file->id) }}" class="icon-text-wrapper flex flex-col h-full cursor-pointer">
                        <div class="flex-grow flex items-center justify-center mb-3 h-16">
                            @if($file->icon_type === 'image')
                                <div
                                    class="w-16 h-16 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                    <img src="{{ route('file-inventory.files.preview', $file->id) }}"
                                        class="max-w-full max-h-full object-cover" alt="Preview" loading="lazy">
                                </div>
                            @elseif($file->icon_type === 'pdf')
                                <div
                                    class="w-14 h-14 rounded bg-red-900/20 text-red-500 flex items-center justify-center border border-red-500/30">
                                    <span class="text-xs font-bold">PDF</span>
                                </div>
                            @elseif($file->icon_type === 'spreadsheet')
                                <div
                                    class="w-14 h-14 rounded bg-green-900/20 text-green-500 flex items-center justify-center border border-green-500/30">
                                    <span class="text-xs font-bold">XLS</span>
                                </div>
                            @elseif($file->icon_type === 'document')
                                <div
                                    class="w-14 h-14 rounded bg-blue-900/20 text-blue-500 flex items-center justify-center border border-blue-500/30">
                                    <span class="text-xs font-bold">DOC</span>
                                </div>
                            @else
                                <div
                                    class="w-14 h-14 rounded bg-slate-800 text-slate-400 flex items-center justify-center border border-slate-700">
                                    <span class="text-xs font-bold uppercase">{{ $file->file_extension }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-center mt-auto">
                            <span class="block text-xs font-medium text-slate-300 truncate mb-1"
                                title="{{ $file->original_name }}">{{ $file->original_name }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $file->human_size }} •
                                {{ $file->created_at->format('M d, Y') }}</span>
                        </div>
                    </a>
                </div>
            @endforeach

            {{-- Upload shortcut card --}}
            <div class="group relative bg-slate-900/30 hover:bg-slate-800/50 border-2 border-dashed border-slate-700/50 hover:border-cyan-500/40 rounded-xl p-4 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[140px]"
                onclick="openModal('upload-modal')">
                <svg class="w-10 h-10 text-slate-600 group-hover:text-cyan-400 transition-colors mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="text-xs font-medium text-slate-500 group-hover:text-cyan-300 transition-colors">Upload Files</span>
            </div>
        </div>
    @endif

    {{-- MODALS --}}

    {{-- Upload Modal --}}
    <div id="upload-modal"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-cyan-900/50 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.8)] w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="upload-modal-content">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white">Upload Files</h3>
                <button type="button" onclick="closeModal('upload-modal')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('file-inventory.upload') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $currentFolder ? $currentFolder->id : '' }}">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-cyan-200/70 mb-2">Select files (Max 10MB each)</label>
                    <div class="relative group cursor-pointer" onclick="document.getElementById('upload-file-input').click()">
                        <div class="rounded-xl border-2 border-dashed border-cyan-900/50 group-hover:border-cyan-500/50 bg-cyan-500/5 transition-colors py-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-cyan-500/40 mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <p class="text-sm text-slate-300 font-medium">Click to select files</p>
                            <p id="file-count" class="text-xs text-cyan-400 mt-2 font-bold"></p>
                        </div>
                    </div>
                    <input type="file" name="files[]" id="upload-file-input" multiple required class="hidden"
                        onchange="handleFilesSelection(this.files, this)">
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeModal('upload-modal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-colors">Upload</button>
                </div>
            </form>
        </div>
    </div>

    {{-- New / Edit Folder Modal --}}
    <div id="new-folder-modal"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-cyan-900/50 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.8)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="new-folder-modal-content">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white" id="folder-modal-title">New Folder</h3>
                <button type="button" onclick="closeModal('new-folder-modal')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="folder-form" action="{{ route('file-inventory.folders.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="_method" id="folder-method" value="POST">
                <input type="hidden" name="parent_id" value="{{ $currentFolder ? $currentFolder->id : '' }}">

                <div class="mb-5">
                    <label for="folder-name" class="block text-sm font-medium text-cyan-200/70 mb-2">Folder Name</label>
                    <input type="text" name="name" id="folder-name" required autofocus
                        class="w-full px-4 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-cyan-200/70 mb-2">Color Label (Optional)</label>
                    <div class="flex gap-2">
                        @foreach(['#38bdf8', '#fbbf24', '#f87171', '#34d399', '#a78bfa', '#94a3b8'] as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color }}" class="peer sr-only">
                                <div class="w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-white transition-all shadow-lg"
                                    style="background-color: {{ $color }}"></div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeModal('new-folder-modal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-colors"
                        id="folder-submit-btn">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Rename File Modal --}}
    <div id="rename-file-modal"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-cyan-900/50 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.8)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="rename-file-modal-content">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white">Rename File</h3>
                <button type="button" onclick="closeModal('rename-file-modal')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="rename-file-form" action="" method="POST" class="p-6">
                @csrf @method('PUT')
                <div class="mb-5">
                    <label for="file-rename-input" class="block text-sm font-medium text-cyan-200/70 mb-2">File Name</label>
                    <input type="text" name="name" id="file-rename-input" required autofocus
                        class="w-full px-4 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeModal('rename-file-modal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-colors">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Move Modal --}}
    <div id="move-modal"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-cyan-900/50 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.8)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="move-modal-content">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white" id="move-modal-title">Move Item</h3>
                <button type="button" onclick="closeModal('move-modal')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="move-form" action="" method="POST" class="p-6">
                @csrf @method('PUT')

                <div class="mb-6">
                    <label for="move-folder-id" class="block text-sm font-medium text-cyan-200/70 mb-2">Destination
                        Folder</label>
                    <select name="folder_id" id="move-folder-id"
                        class="w-full px-4 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <option value="">-- Root Inventory --</option>
                        @foreach($allFolders as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="parent_id" id="move-parent-id" disabled>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeModal('move-modal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-colors">Move</button>
                </div>
            </form>
        </div>
    </div>


    @push('styles')
        <script>
            function handleFilesSelection(files, inputElement) {
                const MAX_SIZE = 10 * 1024 * 1024; // 10MB
                let oversizedFiles = [];
                
                Array.from(files).forEach(file => {
                    if (file.size > MAX_SIZE) {
                        oversizedFiles.push(file.name);
                    }
                });

                if (oversizedFiles.length > 0) {
                    alert('The following files exceed the 10MB limit and cannot be uploaded:\n\n' + oversizedFiles.join('\n'));
                    inputElement.value = ''; // Clear input
                    document.getElementById('file-count').textContent = '';
                    return false;
                }

                document.getElementById('file-count').textContent = files.length + ' file(s) selected';
                return true;
            }

            // Dropdown toggling
            function toggleDropdown(id) {
                document.querySelectorAll('[id^="folder-menu-"], [id^="file-menu-"]').forEach(el => {
                    if (el.id !== id) el.classList.add('hidden');
                });
                const menu = document.getElementById(id);
                if (menu) menu.classList.toggle('hidden');
            }

            // Close dropdowns on outside click
            document.addEventListener('click', () => {
                document.querySelectorAll('[id^="folder-menu-"], [id^="file-menu-"]').forEach(el => el.classList.add('hidden'));
            });

            // Modal logic
            function openModal(id) {
                const modal = document.getElementById(id);
                const content = document.getElementById(id + '-content');
                if (modal && content) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        content.classList.remove('scale-95', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }
            }

            function closeModal(id) {
                const modal = document.getElementById(id);
                const content = document.getElementById(id + '-content');
                if (modal && content) {
                    content.classList.remove('scale-100', 'opacity-100');
                    content.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        // Reset forms
                        if (id === 'new-folder-modal') {
                            document.getElementById('folder-form').reset();
                            document.getElementById('folder-modal-title').textContent = 'New Folder';
                            document.getElementById('folder-method').value = 'POST';
                            document.getElementById('folder-submit-btn').textContent = 'Create';
                            document.getElementById('folder-form').action = '{{ route("file-inventory.folders.store") }}';
                        }
                    }, 300);
                }
            }

            function renameFolder(id, name, color) {
                const form = document.getElementById('folder-form');
                form.action = `/file-inventory/folders/${id}`;
                document.getElementById('folder-method').value = 'PUT';
                document.getElementById('folder-name').value = name;

                if (color) {
                    const radio = document.querySelector(`input[name="color"][value="${color}"]`);
                    if (radio) radio.checked = true;
                }

                document.getElementById('folder-modal-title').textContent = 'Rename Folder';
                document.getElementById('folder-submit-btn').textContent = 'Save Changes';
                openModal('new-folder-modal');
            }

            function renameFileObj(id, name) {
                const form = document.getElementById('rename-file-form');
                form.action = `/file-inventory/files/${id}/rename`;
                document.getElementById('file-rename-input').value = name;
                openModal('rename-file-modal');
            }

            function moveItem(type, id) {
                const form = document.getElementById('move-form');
                const title = document.getElementById('move-modal-title');
                const folderSelect = document.getElementById('move-folder-id');
                const parentIdInput = document.getElementById('move-parent-id');

                if (type === 'file') {
                    form.action = `/file-inventory/files/${id}/move`;
                    title.textContent = 'Move File';
                    folderSelect.name = 'folder_id';
                    parentIdInput.disabled = true;
                } else if (type === 'folder') {
                    form.action = `/file-inventory/folders/${id}/move`;
                    title.textContent = 'Move Folder';
                    folderSelect.name = ''; 
                    parentIdInput.disabled = false;
                    
                    folderSelect.onchange = function() {
                        parentIdInput.value = this.value;
                    };
                    parentIdInput.value = folderSelect.value;
                }
                
                openModal('move-modal');
            }


            // View Mode Logic
            function setViewMode(mode) {
                localStorage.setItem('fileInventoryView', mode);
                const container = document.getElementById('items-container');
                if (!container) return;

                const btnGrid = document.getElementById('btn-grid-view');
                const btnList = document.getElementById('btn-list-view');

                if (mode === 'list') {
                    container.className = 'flex flex-col gap-2 animate-staggered pb-10';
                    document.querySelectorAll('.item-card').forEach(el => {
                        el.classList.remove('flex-col', 'text-center');
                        el.classList.add('flex-row', 'items-center', 'text-left');
                        const iconWrapper = el.querySelector('.icon-text-wrapper');
                        if(iconWrapper) {
                            iconWrapper.classList.remove('flex-col');
                            iconWrapper.classList.add('flex-row', 'items-center', 'gap-4', 'flex-1', 'justify-between');
                        }
                    });

                    btnGrid.classList.remove('text-cyan-400', 'bg-slate-800');
                    btnGrid.classList.add('text-slate-400', 'hover:text-cyan-400', 'hover:bg-slate-800');
                    btnList.classList.remove('text-slate-400', 'hover:text-cyan-400', 'hover:bg-slate-800');
                    btnList.classList.add('text-cyan-400', 'bg-slate-800');
                } else {
                    window.location.reload(); 
                }
            }

            // Apply view on load
            document.addEventListener('DOMContentLoaded', () => {
                const mode = localStorage.getItem('fileInventoryView');
                if (mode === 'list') {
                    setTimeout(() => setViewMode('list'), 50); // slight delay to allow dom render
                }

                // Drag and Drop Logic
                const dropzone = document.documentElement; // whole page
                const overlay = document.getElementById('drag-overlay');
                
                if (overlay) {
                    let dragCounter = 0;

                    dropzone.addEventListener('dragenter', (e) => {
                        e.preventDefault();
                        dragCounter++;
                        overlay.classList.remove('hidden');
                        overlay.classList.add('flex');
                    });

                    dropzone.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        dragCounter--;
                        if (dragCounter === 0) {
                            overlay.classList.add('hidden');
                            overlay.classList.remove('flex');
                        }
                    });

                    dropzone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                    });

                    dropzone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dragCounter = 0;
                        overlay.classList.add('hidden');
                        overlay.classList.remove('flex');

                        if (e.dataTransfer.files.length > 0) {
                            const input = document.getElementById('upload-file-input');
                            if(input) {
                                input.files = e.dataTransfer.files;
                                if (handleFilesSelection(input.files, input)) {
                                    openModal('upload-modal');
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
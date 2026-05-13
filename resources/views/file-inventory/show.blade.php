@extends('layouts.app')

@section('title', $file->original_name)

@section('content')
    {{-- Back Button --}}
    <div class="mb-4 animate-staggered" style="animation-delay: 0ms;">
        @if($file->folder_id)
            <a href="{{ route('file-inventory.folder', $file->folder_id) }}" class="app-back-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to {{ $file->folder->name }}
            </a>
        @else
            <a href="{{ route('file-inventory.index') }}" class="app-back-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to File Inventory
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-staggered" style="animation-delay: 100ms;">

        {{-- File Preview Panel --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden shadow-[0_4px_30px_rgba(0,0,0,0.4)]">
                <div class="px-6 py-4 border-b border-slate-800 bg-slate-800/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-cyan-300 uppercase tracking-wider">Preview</h2>
                    @if(in_array($file->icon_type, ['image', 'pdf']))
                        <span class="text-[10px] font-bold text-green-400 bg-green-900/30 px-2 py-0.5 rounded border border-green-500/30">VIEWABLE</span>
                    @else
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-0.5 rounded border border-slate-600">NO PREVIEW</span>
                    @endif
                </div>

                <div class="flex items-center justify-center min-h-[400px] bg-slate-950/40 p-6">
                    @if($file->icon_type === 'image')
                        <img src="{{ route('file-inventory.files.preview', $file->id) }}"
                            alt="{{ $file->original_name }}"
                            class="max-w-full max-h-[60vh] object-contain rounded-lg shadow-[0_0_30px_rgba(0,240,255,0.1)] border border-slate-700">
                    @elseif($file->icon_type === 'pdf')
                        <iframe src="{{ route('file-inventory.files.preview', $file->id) }}"
                            class="w-full h-[60vh] border-0 rounded-lg"></iframe>
                    @else
                        {{-- Non-previewable file type --}}
                        <div class="text-center py-12">
                            @if($file->icon_type === 'document')
                                <div class="w-24 h-24 mx-auto rounded-2xl bg-blue-900/20 text-blue-400 flex items-center justify-center border border-blue-500/30 mb-6">
                                    <span class="text-2xl font-black">DOC</span>
                                </div>
                            @elseif($file->icon_type === 'spreadsheet')
                                <div class="w-24 h-24 mx-auto rounded-2xl bg-green-900/20 text-green-400 flex items-center justify-center border border-green-500/30 mb-6">
                                    <span class="text-2xl font-black">XLS</span>
                                </div>
                            @elseif($file->icon_type === 'archive')
                                <div class="w-24 h-24 mx-auto rounded-2xl bg-yellow-900/20 text-yellow-400 flex items-center justify-center border border-yellow-500/30 mb-6">
                                    <span class="text-2xl font-black">ZIP</span>
                                </div>
                            @else
                                <div class="w-24 h-24 mx-auto rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center border border-slate-600 mb-6">
                                    <span class="text-2xl font-black uppercase">{{ $file->file_extension }}</span>
                                </div>
                            @endif
                            <p class="text-slate-400 text-sm mb-1">This file type cannot be previewed in the browser.</p>
                            <p class="text-slate-500 text-xs">Download it to view the contents.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- File Details Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden shadow-[0_4px_30px_rgba(0,0,0,0.4)]">
                <div class="px-6 py-4 border-b border-slate-800 bg-slate-800/50">
                    <h2 class="text-sm font-semibold text-cyan-300 uppercase tracking-wider">File Details</h2>
                </div>
                
                <div class="p-6 space-y-5">
                    {{-- File Name --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Filename</span>
                        <p class="text-sm font-medium text-white break-all">{{ $file->original_name }}</p>
                    </div>

                    {{-- File Type --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Type</span>
                        <div class="flex items-center gap-2">
                            @if($file->icon_type === 'pdf')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-900/30 text-red-400 border border-red-500/30 uppercase">PDF</span>
                            @elseif($file->icon_type === 'image')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-900/30 text-purple-400 border border-purple-500/30 uppercase">Image</span>
                            @elseif($file->icon_type === 'document')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-900/30 text-blue-400 border border-blue-500/30 uppercase">Document</span>
                            @elseif($file->icon_type === 'spreadsheet')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-900/30 text-green-400 border border-green-500/30 uppercase">Spreadsheet</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-600 uppercase">{{ $file->file_extension }}</span>
                            @endif
                            <span class="text-xs text-slate-500">({{ $file->file_type }})</span>
                        </div>
                    </div>

                    {{-- File Size --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Size</span>
                        <p class="text-sm text-white">{{ $file->human_size }}</p>
                    </div>

                    {{-- Folder --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Location</span>
                        @if($file->folder)
                            <a href="{{ route('file-inventory.folder', $file->folder_id) }}" class="inline-flex items-center gap-1.5 text-sm text-cyan-400 hover:text-cyan-300 transition-colors">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z" />
                                </svg>
                                {{ $file->folder->name }}
                            </a>
                        @else
                            <p class="text-sm text-slate-500">Root Inventory</p>
                        @endif
                    </div>

                    {{-- Upload Date --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Uploaded</span>
                        <p class="text-sm text-white">{{ $file->created_at->format('M d, Y — h:i A') }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-6 py-5 border-t border-slate-800 space-y-3">
                    <div class="grid {{ in_array($file->icon_type, ['image', 'pdf']) ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
                        <a href="{{ route('file-inventory.files.download', $file->id) }}"
                            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] border border-cyan-400/50 hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] transition-all" data-no-app-loading>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download
                        </a>

                        @if(in_array($file->icon_type, ['image', 'pdf']))
                            <button type="button" onclick="printFile('{{ route('file-inventory.files.preview', $file->id) }}', '{{ $file->icon_type }}')"
                                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold rounded-lg shadow-md border border-slate-500 transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="openRenameModal()"
                            class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors border border-slate-600">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                            Rename
                        </button>

                        <button type="button" onclick="openMoveModal()"
                            class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors border border-slate-600">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Move
                        </button>
                    </div>

                    <form action="{{ route('file-inventory.files.destroy', $file->id) }}" method="POST"
                        class="w-full"
                        data-app-confirm="1"
                        data-app-confirm-title="Delete this file?"
                        data-app-confirm-message="Are you sure you want to delete '{{ $file->original_name }}'? This cannot be undone."
                        data-app-confirm-ok="Delete"
                        data-app-confirm-variant="danger">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-900/10 hover:bg-red-900/30 text-red-400 hover:text-red-300 text-sm font-medium rounded-lg transition-colors border border-red-500/20 hover:border-red-500/40">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Rename File Modal --}}
    <div id="rename-file-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
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
            <form action="{{ route('file-inventory.files.rename', $file->id) }}" method="POST" class="p-6">
                @csrf @method('PUT')
                <div class="mb-5">
                    <label for="file-rename-input" class="block text-sm font-medium text-cyan-200/70 mb-2">File Name</label>
                    <input type="text" name="name" id="file-rename-input" value="{{ $file->original_name }}" required autofocus
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

    {{-- Move File Modal --}}
    <div id="move-file-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-cyan-900/50 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.8)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="move-file-modal-content">
            <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white">Move File</h3>
                <button type="button" onclick="closeModal('move-file-modal')" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('file-inventory.files.move', $file->id) }}" method="POST" class="p-6">
                @csrf @method('PUT')
                <div class="mb-6">
                    <label for="move-folder-id" class="block text-sm font-medium text-cyan-200/70 mb-2">Destination Folder</label>
                    <select name="folder_id" id="move-folder-id"
                        class="w-full px-4 py-2 bg-slate-950 border border-slate-700 rounded-lg text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <option value="">-- Root Inventory --</option>
                        @foreach($allFolders as $f)
                            <option value="{{ $f->id }}" {{ $f->id === $file->folder_id ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeModal('move-file-modal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-colors">Move</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <script>
            function printFile(url, type) {
                if (type === 'pdf') {
                    let iframe = document.getElementById('print-iframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'print-iframe';
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);
                    }
                    
                    iframe.onload = function() {
                        setTimeout(() => {
                            try {
                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                            } catch (e) {
                                console.error('Printing blocked or not supported', e);
                            }
                        }, 500);
                    };
                    iframe.src = url;
                } else if (type === 'image') {
                    const printWindow = window.open('', '_blank');
                    if (printWindow) {
                        printWindow.document.write(`
                            <html>
                                <head>
                                    <title>Print Image</title>
                                    <style>
                                        body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: white; }
                                        img { max-width: 100%; max-height: 100vh; object-fit: contain; }
                                        @media print {
                                            @page { margin: 0; }
                                            body { margin: 0; }
                                        }
                                    </style>
                                </head>
                                <body>
                                    <img src="${url}" onload="window.print(); setTimeout(() => window.close(), 500);" />
                                </body>
                            </html>
                        `);
                        printWindow.document.close();
                    } else {
                        alert('Please allow popups to print images.');
                    }
                }
            }

            function openRenameModal() {
                openModal('rename-file-modal');
            }

            function openMoveModal() {
                openModal('move-file-modal');
            }

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
                    setTimeout(() => modal.classList.add('hidden'), 300);
                }
            }
        </script>
    @endpush
@endsection

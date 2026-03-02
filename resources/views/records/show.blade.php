{{-- Record detail: header with actions, then a styled table of all fields; placeholder for empty values. --}}
@extends('layouts.app')

@section('title', 'Record #' . $record->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('records.index') }}" class="text-indigo-600 hover:underline text-sm font-medium">← Back to records</a>
    </div>
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        {{-- Header bar with title and action buttons --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-slate-50 flex flex-wrap justify-between items-center gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Record #{{ $record->id }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('records.edit', $record) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Edit</a>
                @if ($record->image_path)
                    <button type="button" onclick="previewImage('{{ route('records.image', $record) }}')" class="px-3 py-1.5 bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 text-sm font-medium">Preview image</button>
                @else
                    <form action="{{ route('records.attach-image', $record) }}" method="POST" enctype="multipart/form-data" class="inline" id="attach-form">
                        @csrf
                        <input type="file" name="image" accept="image/*" id="attach-file" class="hidden" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('attach-file').click()" class="px-3 py-1.5 bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 text-sm font-medium">Attach image</button>
                    </form>
                @endif
                <form action="{{ route('records.destroy', $record) }}" method="POST" class="inline" onsubmit="return confirm('Delete this record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium">Delete</button>
                </form>
            </div>
        </div>

        {{-- Data table: fixed columns in order (label | value); empty values show placeholder — --}}
        <div class="overflow-x-auto">
            <table class="detail-table w-full border-collapse">
                <tbody>
                    @foreach ($columns as $col)
                        @php
                            $val = $record->getColumn($col);
                            $display = ($val !== null && $val !== '') ? Str::words($val, 8) : '—';
                        @endphp
                        <tr class="detail-table-tr border-b border-gray-200 hover:bg-slate-50">
                            <th class="detail-table-th">{{ $col }}</th>
                            <td class="detail-table-td" title="{{ $val ?? '' }}">{{ $display }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($record->image_path)
            <div class="px-6 pb-6 pt-2 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Attached image</p>
                <img src="{{ route('records.image', $record) }}" alt="Attached" class="rounded-lg border border-gray-200 shadow-sm max-w-sm max-h-64 object-contain">
            </div>
        @endif
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
@endsection

@push('styles')
{{-- Detail table design: label column fixed width, value column fills rest; alternating row tint. --}}
<style>
    .detail-table { font-size: 0.875rem; }
    .detail-table-th {
        width: 12rem;
        min-width: 10rem;
        padding: 0.625rem 1rem;
        text-align: left;
        font-weight: 600;
        color: rgb(71 85 105);
        background: rgb(248 250 252);
        border-right: 1px solid rgb(226 232 240);
        vertical-align: top;
    }
    .detail-table-td {
        padding: 0.625rem 1rem;
        color: rgb(30 41 59);
        vertical-align: top;
        word-break: break-word;
    }
    .detail-table-tr:nth-child(even) .detail-table-th { background: rgb(241 245 249); }
    .detail-table-tr:nth-child(even) .detail-table-td { background: rgb(248 250 252); }
</style>
@endpush

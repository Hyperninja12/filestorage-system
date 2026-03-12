{{-- Record detail: back link, card with header and data table, image section. --}}
@extends('layouts.app')

@section('title', 'Record #' . $record->id)

@section('content')
    <div class="record-detail">
        <a href="{{ route('records.index') }}" class="app-back-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to records
        </a>
        <div class="record-detail-card">
            <div class="record-detail-header">
                <h1 class="record-detail-title">Record #{{ $record->id }}</h1>
                <div class="record-detail-actions">
                    <a href="{{ route('records.edit', $record) }}" class="record-detail-btn record-detail-btn-primary">Edit</a>
                    @if ($record->image_path)
                        <button type="button" onclick="previewImage('{{ route('records.image', $record) }}')" class="record-detail-btn record-detail-btn-secondary">Preview image</button>
                    @else
                        <form action="{{ route('records.attach-image', $record) }}" method="POST" enctype="multipart/form-data" class="inline" id="attach-form">
                            @csrf
                            <input type="file" name="image" accept="image/*" id="attach-file" class="hidden" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('attach-file').click()" class="record-detail-btn record-detail-btn-secondary">Attach image</button>
                        </form>
                    @endif
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="inline" onsubmit="return confirm('Delete this record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="record-detail-btn record-detail-btn-danger">Delete</button>
                    </form>
                </div>
            </div>

            <div class="record-detail-body">
                <table class="detail-table w-full border-collapse">
                    <tbody>
                        @foreach ($columns as $col)
                            @php
                                $val = $record->getColumn($col);
                                $display = ($val !== null && $val !== '') ? $val : '—';
                            @endphp
                            <tr class="detail-table-tr">
                                <th class="detail-table-th">{{ $col }}</th>
                                <td class="detail-table-td">{{ $display }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($record->image_path)
                <div class="record-detail-image-section">
                    <div class="record-detail-image-header">
                        <p class="record-detail-image-label">Attached image</p>
                        <form action="{{ route('records.remove-image', $record) }}" method="POST" class="record-detail-image-remove-form" onsubmit="return confirm('Remove this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="record-detail-image-remove-btn" title="Remove image" aria-label="Remove image">×</button>
                        </form>
                    </div>
                    <img src="{{ route('records.image', $record) }}" alt="Attached" class="record-detail-image">
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
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .record-detail-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .record-detail-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; }
    .record-detail-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .record-detail-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .record-detail-btn-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff; }
    .record-detail-btn-primary:hover { opacity: 0.95; }
    .record-detail-btn-secondary { background: #e2e8f0; color: #334155; }
    .record-detail-btn-secondary:hover { background: #cbd5e1; }
    .record-detail-btn-danger { background: #fee2e2; color: #b91c1c; }
    .record-detail-btn-danger:hover { background: #fecaca; }
    .record-detail-body { overflow-x: auto; }
    .record-detail-image-section { padding: 1.5rem; border-top: 1px solid #e2e8f0; background: #fafafa; }
    .record-detail-image-header { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.75rem; }
    .record-detail-image-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; }
    .record-detail-image-remove-form { display: inline-block; }
    .record-detail-image-remove-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem;
        padding: 0; border: none; border-radius: 0.375rem;
        background: #fee2e2; color: #b91c1c;
        font-size: 1.25rem; line-height: 1; font-weight: 700; cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }
    .record-detail-image-remove-btn:hover { background: #fecaca; color: #991b1b; }
    .record-detail-image { border-radius: 0.5rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.06); max-width: 20rem; max-height: 16rem; object-fit: contain; }
    .detail-table { font-size: 0.875rem; }
    .detail-table-th {
        width: 12rem;
        min-width: 10rem;
        padding: 0.75rem 1.25rem;
        text-align: left;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .detail-table-td {
        padding: 0.75rem 1.25rem;
        color: #1e293b;
        vertical-align: top;
        word-break: break-word;
        white-space: pre-wrap;
        max-width: 40rem;
    }
    .detail-table-tr:nth-child(even) .detail-table-th { background: #f1f5f9; }
    .detail-table-tr:nth-child(even) .detail-table-td { background: #f8fafc; }
    .records-modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 50; }
    .records-modal-overlay.records-modal-open { display: flex; }
    .records-modal-box { max-width: 90vw; max-height: 90vh; padding: 1rem; }
    .records-modal-img { max-width: 100%; max-height: 80vh; border-radius: 0.75rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4); }
    .records-modal-close { margin-top: 1rem; width: 100%; padding: 0.625rem 1rem; background: #1e293b; color: #fff; border-radius: 0.5rem; font-weight: 500; cursor: pointer; border: 0; }
    .records-modal-close:hover { background: #334155; }
</style>
@endpush

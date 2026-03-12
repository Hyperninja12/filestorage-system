{{-- Edit record form: back link, card with header and form table. --}}
@extends('layouts.app')

@section('title', 'Edit Record #' . $record->id)

@section('content')
    <div class="record-edit">
        <a href="{{ route('records.show', $record) }}" class="app-back-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to record
        </a>
        <div class="record-edit-card">
            <div class="record-edit-header">
                <h1 class="record-edit-title">Edit Record #{{ $record->id }}</h1>
            </div>
            <form action="{{ route('records.update', $record) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="record-edit-body">
                    <table class="edit-table w-full border-collapse">
                        <tbody>
                            @foreach ($columns as $col)
                                <tr class="edit-table-tr">
                                    <th class="edit-table-th">
                                        <label for="field-{{ Str::slug($col) }}">{{ $col }}</label>
                                    </th>
                                    <td class="edit-table-td">
                                        <input type="text" name="{{ $col }}" id="field-{{ Str::slug($col) }}" value="{{ old($col, $record->getColumn($col)) }}"
                                            placeholder="—"
                                            class="edit-table-input">
                                        @error($col)
                                            <p class="edit-table-error">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($record->image_path)
                <div class="record-edit-image-section">
                    <div class="record-edit-image-header">
                        <span class="record-edit-image-label">Attached image</span>
                        <form action="{{ route('records.remove-image', $record) }}" method="POST" class="record-edit-image-remove-form" onsubmit="return confirm('Remove this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="record-edit-image-remove-btn" title="Remove image" aria-label="Remove image">× Remove</button>
                        </form>
                    </div>
                    <img src="{{ route('records.image', $record) }}" alt="Attached" class="record-edit-image-thumb">
                </div>
                @endif
                <div class="record-edit-footer">
                    <button type="submit" class="record-edit-btn record-edit-btn-primary">Save</button>
                    <a href="{{ route('records.show', $record) }}" class="record-edit-btn record-edit-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .record-edit { }
    .record-edit .app-back-btn { margin-bottom: 1.25rem; }
    .record-edit-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        max-width: 56rem;
    }
    .record-edit-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .record-edit-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; }
    .record-edit-body { overflow-x: auto; }
    .record-edit-image-section {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #fafafa;
    }
    .record-edit-image-header { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem; }
    .record-edit-image-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .record-edit-image-remove-form { display: inline-block; }
    .record-edit-image-remove-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 0.75rem;
        border: none;
        border-radius: 0.375rem;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
    }
    .record-edit-image-remove-btn:hover { background: #fecaca; color: #991b1b; }
    .record-edit-image-thumb {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        max-width: 14rem;
        max-height: 10rem;
        object-fit: contain;
    }
    .record-edit-footer {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .record-edit-btn {
        padding: 0.5rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .record-edit-btn-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff; }
    .record-edit-btn-primary:hover { opacity: 0.95; }
    .record-edit-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
    .record-edit-btn-secondary:hover { background: #f8fafc; }
    .edit-table { font-size: 0.875rem; }
    .edit-table-th {
        width: 12rem;
        min-width: 10rem;
        padding: 0.75rem 1.25rem;
        text-align: left;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .edit-table-td { padding: 0.75rem 1.25rem; background: #fff; vertical-align: middle; }
    .edit-table-tr:nth-child(even) .edit-table-th { background: #f1f5f9; }
    .edit-table-tr:nth-child(even) .edit-table-td { background: #fafafa; }
    .edit-table-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        font-size: 0.875rem;
    }
    .edit-table-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
    .edit-table-error { margin-top: 0.25rem; font-size: 0.8125rem; color: #b91c1c; }
</style>
@endpush

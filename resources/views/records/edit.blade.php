{{-- Form sa pag-edit sa record: back link, card nga naay header ug form table. --}}
@extends('layouts.app')

@section('title', 'Edit Record #' . $record->getDisplayNumber())

@section('content')
    @php
        $listQuery = array_filter(
            request()->only(['page', 'search', 'person_responsible', 'type']),
            fn ($v) => $v !== null && $v !== ''
        );
    @endphp
    <div class="record-edit">
        <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}" class="app-back-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to record
        </a>
        <div class="record-edit-card">
            <div class="record-edit-header">
                <h1 class="record-edit-title">Edit Record #{{ $record->getDisplayNumber() }}</h1>
            </div>
            {{-- Remove-image forms must NOT be nested inside the update <form> (invalid HTML breaks Save). --}}
            @if (count($record->getImagePaths()) > 0)
                <div class="record-edit-image-section">
                    <span class="record-edit-image-label">Attached images ({{ count($record->getImagePaths()) }}/2)</span>
                    <div class="record-edit-image-grid">
                        @foreach ($record->getImagePaths() as $idx => $path)
                            <div class="record-edit-image-item">
                                <img src="{{ route('records.image', [$record, $idx]) }}" alt="Image {{ $idx + 1 }}" class="record-edit-image-thumb">
                                <form action="{{ route('records.remove-image', [$record, $idx]) }}" method="POST" class="record-edit-image-remove-form"
                                    data-app-confirm="1"
                                    data-app-confirm-title="Remove this image?"
                                    data-app-confirm-message="This image will be removed from the record."
                                    data-app-confirm-ok="Remove"
                                    data-app-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="record-edit-image-remove-btn" title="Remove image" aria-label="Remove image">× Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <form action="{{ route('records.update', $record) }}" method="POST">
                @csrf
                @method('PUT')
                @foreach ($listQuery as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <div class="record-edit-body">
                    <table class="edit-table w-full border-collapse">
                        <tbody>
                            @foreach ($columns as $col)
                                <tr class="edit-table-tr">
                                    <th class="edit-table-th">
                                        <label for="field-{{ Str::slug($col) }}">{{ $col }}</label>
                                    </th>
                                    <td class="edit-table-td">
                                        @php
                                            $requestKey = preg_replace('/[.\s]+/', '_', $col) ?? str_replace(' ', '_', $col);
                                            $fieldValue = old($col, old($requestKey, $record->getColumn($col)));
                                            $inputType = 'text';
                                            $inputPattern = null;
                                            $inputTitle = null;
                                            $inputMin = null;
                                            $inputStep = null;
                                            $inputMode = null;
                                            $placeholder = '—';
                                            $inputMaxlength = null;

                                            if ($col === 'Date of Purchase') {
                                                $inputType = 'date';
                                                $placeholder = '';
                                                if ($fieldValue) {
                                                    $str = trim((string) $fieldValue);
                                                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
                                                        $fieldValue = "{$m[1]}-{$m[2]}-{$m[3]}";
                                                    } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
                                                        // Fallback matching to assume mm/dd/yyyy like most imported spreadsheets
                                                        $fieldValue = \Carbon\Carbon::createFromFormat('m/d/Y', $str)?->format('Y-m-d') ?? $fieldValue;
                                                    } else {
                                                        try {
                                                            $fieldValue = \Carbon\Carbon::parse($str)->format('Y-m-d');
                                                        } catch (\Exception $e) {}
                                                    }
                                                }
                                            } elseif ($col === 'Account Code') {
                                                $inputPattern = '\d-\d{2}-\d{2}-\d{3}';
                                                $inputTitle = 'Digits only — dashes are added automatically (0-00-00-000)';
                                                $inputMode = 'numeric';
                                                $placeholder = 'Type digits, e.g. 10705020 → 1-07-05-020';
                                                $inputMaxlength = 11;
                                            } elseif ($col === 'PO No.') {
                                                $inputPattern = '\d{2}-\d{2}-\d{4}';
                                                $inputTitle = 'Format: 00-00-0000';
                                                $inputMode = 'numeric';
                                                $placeholder = '00-00-0000';
                                            } elseif ($col === 'Unit Value' || $col === 'On Hand Value') {
                                                $inputType = 'text';
                                                $inputMode = 'decimal';
                                                $inputTitle = 'Cash format: commas + two decimals (e.g. ₱1,234.00)';
                                                $placeholder = '1,234.00';
                                                if ($fieldValue !== null && $fieldValue !== '') {
                                                    $fieldValue = \App\Support\CashFormatter::formatForInput($fieldValue);
                                                }
                                            }
                                        @endphp
                                        <input type="{{ $inputType }}" name="{{ $requestKey }}" id="field-{{ Str::slug($col) }}" value="{{ $fieldValue }}"
                                            placeholder="{{ $placeholder }}"
                                            @if ($inputMaxlength) maxlength="{{ $inputMaxlength }}" @endif
                                            @if ($inputPattern) pattern="{{ $inputPattern }}" @endif
                                            @if ($inputTitle) title="{{ $inputTitle }}" @endif
                                            @if ($inputMin !== null) min="{{ $inputMin }}" @endif
                                            @if ($inputStep !== null) step="{{ $inputStep }}" @endif
                                            @if ($inputMode) inputmode="{{ $inputMode }}" @endif
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
                <div class="record-edit-footer">
                    <button type="submit" class="record-edit-btn record-edit-btn-primary">Save</button>
                    <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}" class="record-edit-btn record-edit-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function () {
            function formatAccountCodeDigits(raw) {
                var d = String(raw).replace(/\D/g, '').slice(0, 9);
                if (!d.length) return '';
                var p1 = d[0];
                if (d.length <= 1) return p1;
                var out = p1 + '-' + d.slice(1, 3);
                if (d.length <= 3) return out;
                out += '-' + d.slice(3, 5);
                if (d.length <= 5) return out;
                return out + '-' + d.slice(5, 9);
            }
            var el = document.getElementById('field-account-code');
            if (!el) return;
            if (el.value) {
                var initial = formatAccountCodeDigits(el.value);
                if (initial !== el.value) el.value = initial;
            }
            el.addEventListener('input', function () {
                var next = formatAccountCodeDigits(el.value);
                if (el.value !== next) {
                    el.value = next;
                    try { el.setSelectionRange(next.length, next.length); } catch (e) {}
                }
            });
        })();
    </script>
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
        border-bottom: 1px solid #e2e8f0;
        background: #fafafa;
    }
    .record-edit-image-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.75rem; }
    .record-edit-image-grid { display: flex; flex-wrap: wrap; gap: 1rem; }
    .record-edit-image-item { position: relative; }
    .record-edit-image-remove-form { margin-top: 0.25rem; }
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

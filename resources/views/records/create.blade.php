{{-- Form to add a new record manually (no import file). --}}
@extends('layouts.app')

@section('title', 'Add Record')

@section('content')
    <div class="record-edit">
        <a href="{{ route('records.index') }}" class="app-back-btn">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to records
        </a>
        <div class="record-edit-card">
            <div class="record-edit-header">
                <h1 class="record-edit-title">Add new record</h1>
                <p class="record-edit-subtitle">Enter the data below. You can add images after saving.</p>
            </div>
            <div class="record-insert-alert" role="status">
                <strong>Before you save</strong> — Check that the fields are correct. Click <em>Add record</em>, then confirm in the popup to insert this row.
            </div>
            <form action="{{ route('records.store') }}" method="POST" id="record-create-form">
                @csrf
                <div class="record-edit-body">
                    <table class="edit-table w-full border-collapse">
                        <tbody>
                            @php
                                $manualCreateOptionalColumns = $manualCreateOptionalColumns ?? [];
                            @endphp
                            @foreach ($columns as $col)
                                <tr class="edit-table-tr">
                                    <th class="edit-table-th">
                                        <label for="field-{{ Str::slug($col) }}">{{ $col }}</label>
                                    </th>
                                    <td class="edit-table-td">
                                        @php
                                            $requestKey = preg_replace('/[.\s]+/', '_', $col) ?? str_replace(' ', '_', $col);
                                            $val = old($col) ?? old($requestKey);
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
                                                if ($val !== null && $val !== '') {
                                                    $val = \App\Support\CashFormatter::formatForInput($val);
                                                }
                                            }
                                        @endphp
                                        <input type="{{ $inputType }}" name="{{ $requestKey }}" id="field-{{ Str::slug($col) }}" value="{{ $val }}"
                                            placeholder="{{ $placeholder }}"
                                            @if ($inputMaxlength) maxlength="{{ $inputMaxlength }}" @endif
                                            @if ($inputPattern) pattern="{{ $inputPattern }}" @endif
                                            @if ($inputTitle) title="{{ $inputTitle }}" @endif
                                            @if ($inputMin !== null) min="{{ $inputMin }}" @endif
                                            @if ($inputStep !== null) step="{{ $inputStep }}" @endif
                                            @if ($inputMode) inputmode="{{ $inputMode }}" @endif
                                            @if (! in_array($col, $manualCreateOptionalColumns, true)) required aria-required="true" @endif
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
                    <button type="submit" class="record-edit-btn record-edit-btn-primary" id="record-create-submit">Add record</button>
                    <a href="{{ route('records.index') }}" class="record-edit-btn record-edit-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div id="record-insert-confirm" class="record-insert-confirm record-insert-confirm-hidden" aria-hidden="true">
        <div class="record-insert-confirm-backdrop" data-insert-dismiss tabindex="-1"></div>
        <div class="record-insert-confirm-panel" role="dialog" aria-modal="true" aria-labelledby="record-insert-confirm-title">
            <div class="record-insert-confirm-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <h2 id="record-insert-confirm-title" class="record-insert-confirm-title">Insert this record?</h2>
            <p class="record-insert-confirm-text">This will save a new row to the database. You can add images after saving.</p>
            <div class="record-insert-confirm-actions">
                <button type="button" class="record-insert-confirm-btn record-insert-confirm-btn-secondary" id="record-insert-confirm-cancel">Cancel</button>
                <button type="button" class="record-insert-confirm-btn record-insert-confirm-btn-primary" id="record-insert-confirm-yes">Yes, insert</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var form = document.getElementById('record-create-form');
            var modal = document.getElementById('record-insert-confirm');
            if (!form || !modal) return;
            function openModal() {
                if (typeof window.hideAppGlobalLoading === 'function') {
                    window.hideAppGlobalLoading();
                }
                modal.classList.remove('record-insert-confirm-hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.getElementById('record-insert-confirm-yes').focus();
            }
            function closeModal() {
                modal.classList.add('record-insert-confirm-hidden');
                modal.setAttribute('aria-hidden', 'true');
            }
            form.addEventListener('submit', function (e) {
                if (form.getAttribute('data-insert-confirmed') === '1') {
                    form.setAttribute('data-insert-confirmed', '0');
                    return;
                }
                e.preventDefault();
                openModal();
            });
            document.getElementById('record-insert-confirm-cancel').addEventListener('click', closeModal);
            modal.querySelectorAll('[data-insert-dismiss]').forEach(function (el) {
                el.addEventListener('click', closeModal);
            });
            document.getElementById('record-insert-confirm-yes').addEventListener('click', function () {
                closeModal();
                form.setAttribute('data-insert-confirmed', '1');
                form.requestSubmit();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('record-insert-confirm-hidden')) closeModal();
            });
        })();
    </script>
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
    .record-edit-subtitle { font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0; }
    .record-insert-alert {
        margin: 0.75rem 1.5rem 1rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        line-height: 1.45;
        color: #1e40af;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
        border-radius: 0.5rem;
    }
    .record-insert-alert strong { font-weight: 600; }
    .record-edit-body { overflow-x: auto; }
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
    .record-insert-confirm {
        position: fixed; inset: 0; z-index: 10060;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .record-insert-confirm-hidden { display: none !important; }
    .record-insert-confirm-backdrop {
        position: absolute; inset: 0;
        background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px);
    }
    .record-insert-confirm-panel {
        position: relative; max-width: 22rem; width: 100%;
        padding: 1.5rem; background: #fff; border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        animation: recordInsertPop 0.28s cubic-bezier(0.22, 1, 0.36, 1);
    }
    @keyframes recordInsertPop {
        from { opacity: 0; transform: scale(0.94) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .record-insert-confirm-icon {
        width: 2.75rem; height: 2.75rem; margin: 0 auto 1rem;
        color: #4f46e5; background: #eef2ff; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
    }
    .record-insert-confirm-icon svg { width: 1.5rem; height: 1.5rem; }
    .record-insert-confirm-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; text-align: center; }
    .record-insert-confirm-text { font-size: 0.875rem; color: #64748b; margin: 0 0 1.25rem; line-height: 1.5; text-align: center; }
    .record-insert-confirm-actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
    .record-insert-confirm-btn {
        padding: 0.5rem 1.15rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600;
        cursor: pointer; border: 1px solid transparent;
    }
    .record-insert-confirm-btn-secondary { background: #fff; color: #475569; border-color: #cbd5e1; }
    .record-insert-confirm-btn-secondary:hover { background: #f8fafc; }
    .record-insert-confirm-btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
    }
    .record-insert-confirm-btn-primary:hover { opacity: 0.95; }
</style>
@endpush

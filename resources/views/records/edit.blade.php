@extends('layouts.app')

@section('title', 'Edit Record #' . $record->getDisplayNumber())

@section('content')
    @php
        $listQuery = array_filter(
            request()->only(['page', 'search', 'person_responsible', 'type']),
            fn($v) => $v !== null && $v !== '',
        );

        // Helper function for input logic to avoid repetitive code
        $getFieldData = function ($col) use ($record) {
            $requestKey = preg_replace('/[.\s]+/', '_', $col) ?? str_replace(' ', '_', $col);
            $fieldValue = old($col, old($requestKey, $record->getColumn($col)));
            $inputType = 'text';
            $inputPattern = null;
            $inputTitle = null;
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
                        $fieldValue = \Carbon\Carbon::createFromFormat('m/d/Y', $str)?->format('Y-m-d') ?? $fieldValue;
                    } else {
                        try {
                            $fieldValue = \Carbon\Carbon::parse($str)->format('Y-m-d');
                        } catch (\Exception $e) {
                        }
                    }
                }
            } elseif ($col === 'Account Code') {
                $inputPattern = '\d-\d{2}-\d{2}-\d{3}';
                $inputTitle = 'Digits only — dashes are added automatically (0-00-00-000)';
                $inputMode = 'numeric';
                $placeholder = 'e.g. 1-07-05-020';
                $inputMaxlength = 11;
            } elseif ($col === 'PO No.') {
                $inputPattern = '\d{2}-\d{2}-\d{4}';
                $inputTitle = 'Format: 00-00-0000';
                $inputMode = 'numeric';
                $placeholder = '00-00-0000';
            } elseif ($col === 'Unit Value' || $col === 'On Hand Value') {
                $inputMode = 'decimal';
                $placeholder = '0.00';
                if ($fieldValue !== null && $fieldValue !== '') {
                    $fieldValue = \App\Support\CashFormatter::formatForInput($fieldValue);
                }
            }

            return (object) [
                'name' => $requestKey,
                'id' => 'field-' . Str::slug($col),
                'value' => $fieldValue,
                'type' => $inputType,
                'placeholder' => $placeholder,
                'pattern' => $inputPattern,
                'title' => $inputTitle,
                'maxlength' => $inputMaxlength,
                'inputmode' => $inputMode,
                'label' => $col,
            ];
        };
    @endphp

    <div class="max-w-5xl mx-auto px-4 py-8">
        {{-- Clean Navigation Link --}}
        <div class="mb-8">
            <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors group">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" class="group-hover:-translate-x-1 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to record details
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-4 tracking-tight">Edit Record</h1>
            <p class="text-gray-500 mt-2 font-medium">Update the details for record #{{ $record->getDisplayNumber() }}</p>
        </div>

        {{-- Attached Images Card --}}
        @if (count($record->getImagePaths()) > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 animate-staggered overflow-hidden" style="animation-delay: 0ms;">
                <div class="border-l-4 border-sky-400 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-sky-50 rounded-lg">
                        <svg class="w-5 h-5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Attached Images ({{ count($record->getImagePaths()) }}/2)</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ($record->getImagePaths() as $idx => $path)
                        <div class="group relative bg-gray-50 rounded-2xl border border-gray-200 p-2 transition-all hover:shadow-md">
                            <img src="{{ route('records.image', [$record, $idx]) }}" alt="Image {{ $idx + 1 }}"
                                class="w-full h-48 object-contain rounded-xl">
                            <form action="{{ route('records.remove-image', [$record, $idx]) }}" method="POST"
                                class="absolute top-4 right-4" data-app-confirm="1"
                                data-app-confirm-title="Remove this image?"
                                data-app-confirm-message="This image will be removed permanently." data-app-confirm-ok="Remove"
                                data-app-confirm-variant="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-2 bg-white/90 backdrop-blur-sm text-red-600 rounded-lg shadow-sm hover:bg-red-50 transition-colors border border-red-100"
                                    title="Remove image">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                </div>
            </div>
        @endif

        <form action="{{ route('records.update', $record) }}" method="POST">
            @csrf
            @method('PUT')
            @foreach ($listQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach

            {{-- Section 1: Item Details Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 animate-staggered overflow-hidden" style="animation-delay: 100ms;">
                <div class="border-l-4 border-indigo-400 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-indigo-50 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Item Details & Referencing</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Classification, codes, and item descriptions</p>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-6">
                    @php $f = $getFieldData('Account Code'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" pattern="{{ $f->pattern }}" title="{{ $f->title }}" maxlength="{{ $f->maxlength }}" inputmode="{{ $f->inputmode }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                        @error($f->label) <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>

                    @php $f = $getFieldData('Fund'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('Category'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-bold text-indigo-700">
                    </div>

                    @php $f = $getFieldData('Subcategory'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('Property No.'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-mono">
                    </div>

                    @php $f = $getFieldData('Inventory Item No.'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-mono">
                    </div>

                    @php $f = $getFieldData('Description'); @endphp
                    <div class="col-span-6">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <textarea name="{{ $f->name }}" id="{{ $f->id }}" rows="3" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none resize-none leading-relaxed">{{ $f->value }}</textarea>
                    </div>

                    @php $f = $getFieldData('PO No.'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" pattern="{{ $f->pattern }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('Date of Purchase'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>
                </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 animate-staggered overflow-hidden" style="animation-delay: 200ms;">
                <div class="border-l-4 border-emerald-400 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-emerald-50 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Inventory & Financials</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Quantities, unit pricing, and valuations</p>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-6">
                    @php $f = $getFieldData('Qty'); @endphp
                    <div class="col-span-2">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-bold">
                    </div>

                    @php $f = $getFieldData('Unit'); @endphp
                    <div class="col-span-2">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none text-gray-500">
                    </div>

                    @php $f = $getFieldData('Unit Value'); @endphp
                    <div class="col-span-2">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }} (₱)</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" inputmode="{{ $f->inputmode }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-bold text-emerald-600 tabular-nums">
                        @error($f->label) <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>

                    @php $f = $getFieldData('On Hand Count'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('On Hand Value'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }} (₱)</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" inputmode="{{ $f->inputmode }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-medium text-gray-700 tabular-nums">
                    </div>
                </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-12 animate-staggered overflow-hidden" style="animation-delay: 300ms;">
                <div class="border-l-4 border-amber-400 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-amber-50 rounded-lg">
                        <svg class="w-5 h-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Accountability & Support</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Responsible personnel, location, and notes</p>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-6">
                    @php $f = $getFieldData('Person Responsible'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none font-semibold text-indigo-900">
                    </div>

                    @php $f = $getFieldData('Office'); @endphp
                    <div class="col-span-3">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('Area Location'); @endphp
                    <div class="col-span-6">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <input type="{{ $f->type }}" name="{{ $f->name }}" id="{{ $f->id }}" value="{{ $f->value }}" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none">
                    </div>

                    @php $f = $getFieldData('Additional Information'); @endphp
                    <div class="col-span-6">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <textarea name="{{ $f->name }}" id="{{ $f->id }}" rows="2" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none resize-none">{{ $f->value }}</textarea>
                    </div>

                    @php $f = $getFieldData('Remarks'); @endphp
                    <div class="col-span-6">
                        <label for="{{ $f->id }}" class="block text-sm font-semibold text-gray-600 mb-2">{{ $f->label }}</label>
                        <textarea name="{{ $f->name }}" id="{{ $f->id }}" rows="2" placeholder="{{ $f->placeholder }}" class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500/20 transition-all border outline-none resize-none">{{ $f->value }}</textarea>
                    </div>
                </div>
                </div>
            </div>

            <div class="flex items-center gap-6 animate-staggered" style="animation-delay: 400ms;">
                <button type="submit"
                    class="flex-1 py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95 leading-tight tracking-wide">
                    Update Record Details
                </button>
                <a href="{{ route('records.show', array_merge(['record' => $record], $listQuery)) }}"
                    class="px-8 py-4 text-gray-500 font-semibold hover:text-gray-900 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        (function() {
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
            el.addEventListener('input', function() {
                var next = formatAccountCodeDigits(el.value);
                if (el.value !== next) {
                    el.value = next;
                    try {
                        el.setSelectionRange(next.length, next.length);
                    } catch (e) {}
                }
            });
        })();
    </script>
@endsection

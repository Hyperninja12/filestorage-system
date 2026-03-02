{{-- Edit record form: table layout (label | input) so all fields fit; placeholder for empty. --}}
@extends('layouts.app')

@section('title', 'Edit Record #' . $record->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('records.index') }}" class="text-indigo-600 hover:underline text-sm font-medium">← Back to records</a>
    </div>
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden max-w-4xl">
        <div class="px-6 py-4 border-b border-gray-200 bg-slate-50">
            <h1 class="text-xl font-semibold text-gray-800">Edit Record #{{ $record->id }}</h1>
        </div>
        <form action="{{ route('records.update', $record) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto">
                <table class="edit-table w-full border-collapse">
                    <tbody>
                        @foreach ($columns as $col)
                            <tr class="edit-table-tr border-b border-gray-200">
                                <th class="edit-table-th">
                                    <label for="field-{{ Str::slug($col) }}">{{ $col }}</label>
                                </th>
                                <td class="edit-table-td">
                                    <input type="text" name="{{ $col }}" id="field-{{ Str::slug($col) }}" value="{{ old($col, $record->getColumn($col)) }}"
                                        placeholder="—"
                                        class="edit-table-input">
                                    @error($col)
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Save</button>
                <a href="{{ route('records.show', $record) }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
{{-- Edit form table: label column fixed width, full-width input in value column. --}}
<style>
    .edit-table { font-size: 0.875rem; }
    .edit-table-th {
        width: 12rem;
        min-width: 10rem;
        padding: 0.5rem 1rem;
        text-align: left;
        font-weight: 600;
        color: rgb(71 85 105);
        background: rgb(248 250 252);
        border-right: 1px solid rgb(226 232 240);
        vertical-align: middle;
    }
    .edit-table-td { padding: 0.5rem 1rem; background: #fff; vertical-align: middle; }
    .edit-table-tr:nth-child(even) .edit-table-th { background: rgb(241 245 249); }
    .edit-table-tr:nth-child(even) .edit-table-td { background: rgb(249 250 251); }
    .edit-table-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgb(209 213 219);
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .edit-table-input:focus {
        outline: none;
        border-color: rgb(99 102 241);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
</style>
@endpush

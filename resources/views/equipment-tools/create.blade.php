@extends('layouts.app')

@section('title', 'إضافة أداة جديدة')

@section('content')

<div class="max-w-3xl mx-auto">

    @include('partials.page-header', [
        'title' => 'إضافة أداة جديدة',
        'icon' => 'fa-tools',
        'backRoute' => 'equipment-tools.index',
    ])

    <div class="card p-8">

        <form action="{{ route('equipment-tools.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <div>
                <div class="grid md:grid-cols-2 gap-5">

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            اسم الأداة <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="input-field w-full px-4 py-3 @error('name') border-red-500 @enderror"
                            required>

                        @error('name')
                            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            الوحدة <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="text"
                            name="unit"
                            value="{{ old('unit') }}"
                            class="input-field w-full px-4 py-3 @error('unit') border-red-500 @enderror"
                            required>

                        @error('unit')
                            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            الكمية الأولية
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            value="{{ old('quantity',0) }}"
                            min="0"
                            step="0.01"
                            class="input-field w-full px-4 py-3 @error('quantity') border-red-500 @enderror">

                        @error('quantity')
                            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            سعر الوحدة
                        </label>

                        <div class="relative">
                            <input
                                type="number"
                                name="price_per_unit"
                                value="{{ old('price_per_unit',0) }}"
                                min="0"
                                step="0.01"
                                class="input-field w-full px-4 py-3 pr-16 @error('price_per_unit') border-red-500 @enderror">
                        </div>

                        @error('price_per_unit')
                            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            ملاحظات
                        </label>

                        <textarea
                            name="notes"
                            rows="4"
                            class="input-field w-full px-4 py-3 resize-none @error('notes') border-red-500 @enderror"
                            placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>

                        @error('notes')
                            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="border-t border-slate-700 pt-6 flex justify-end gap-3">

                <a href="{{ route('equipment-tools.index') }}"
                   class="px-6 py-3 rounded-lg border border-slate-600 hover:border-slate-500 hover:bg-slate-700 transition flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    إلغاء
                </a>

                <button type="submit"
                        class="btn-primary px-8 py-3 rounded-lg text-white flex items-center gap-2 shadow-lg">
                    <i class="fas fa-save"></i>
                    حفظ الأداة
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
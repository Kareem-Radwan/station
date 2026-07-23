@extends('layouts.app')
@section('title', 'إضافة مادة جديدة')
@section('content')

@include('partials.page-header', ['title' => 'إضافة مادة جديدة للمخزون', 'icon' => 'fa-plus-circle'])

<div class="max-w-2xl">
    <form action="{{ route('inventory.store') }}" method="POST" class="space-y-6">
        @csrf

        @if(session('error'))
        <div class="bg-red-900/30 border border-red-500/40 rounded-xl p-4 text-red-400 text-sm">
            <i class="fas fa-exclamation-circle ml-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="card p-6 space-y-5">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                <i class="fas fa-box text-amber-400 text-sm"></i> بيانات المادة
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم المادة بالعربي <span class="text-red-400">*</span></label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" required
                        class="input-field w-full px-3 py-2.5 text-sm">
                    @error('name_ar')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم المادة بالإنجليزي <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input-field w-full px-3 py-2.5 text-sm">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">وحدة القياس <span class="text-red-400">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit') }}" required
                        class="input-field w-full px-3 py-2.5 text-sm">
                    @error('unit')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">سعر الوحدة</label>
                    <input type="number" step="0.01" name="price_per_unit" value="{{ old('price_per_unit', 0) }}"
                        class="input-field w-full px-3 py-2.5 text-sm" min="0">
                    @error('price_per_unit')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المخزون الحالي <span class="text-red-400">*</span></label>
                    <input type="number" step="0.001" name="current_stock" value="{{ old('current_stock', 0) }}" required
                        class="input-field w-full px-3 py-2.5 text-sm" min="0">
                    @error('current_stock')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">حد التنبيه <span class="text-red-400">*</span></label>
                    <input type="number" step="0.001" name="alert_threshold" value="{{ old('alert_threshold', 0) }}" required
                        class="input-field w-full px-3 py-2.5 text-sm" min="0">
                    @error('alert_threshold')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ المادة
            </button>
            <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>

@endsection

@extends('layouts.app')
@section('title', 'المخزون الوارد - '.$item->name_ar)
@section('content')

@include('partials.page-header', ['title' => 'وارد مخزون: '.$item->name_ar, 'icon' => 'fa-arrow-down'])

<div class="max-w-2xl">
    <div class="card p-4 mb-6 flex items-center gap-4 border border-amber-500/30">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <i class="fas fa-boxes text-amber-400 text-xl"></i>
        </div>
        <div>
            <p class="text-slate-400 text-xs">الرصيد الحالي</p>
            <p class="text-2xl font-bold text-amber-400">{{ number_format($item->current_stock, 1) }} <span class="text-sm font-normal text-slate-400">{{ $item->unit }}</span></p>
        </div>
    </div>

    <form action="{{ route('inventory.stock-in.store', $item) }}" method="POST" class="card p-6 space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الكمية الواردة ({{ $item->unit }}) <span class="text-red-400">*</span></label>
                <input type="number" step="0.001" name="quantity" min="0.001" required
                    class="input-field w-full px-3 py-2.5 text-sm" placeholder="أدخل الكمية">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="date" value="{{ today()->toDateString() }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المورد</label>
                <select name="supplier_id" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">بدون مورد</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تكلفة الوحدة <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost', $item->price_per_unit ?? 0) }}" required class="input-field w-full px-3 py-2.5 text-sm" placeholder="أدخل تكلفة الوحدة">
                @error('unit_cost')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"></textarea>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm">
                <i class="fas fa-plus"></i> إضافة للمخزون
            </button>
            <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

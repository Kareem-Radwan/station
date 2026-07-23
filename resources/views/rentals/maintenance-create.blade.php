@extends('layouts.app')
@section('title', 'تسجيل صيانة إيجار')
@section('content')

@include('partials.page-header', ['title' => 'صيانة: '.$rental->equipment_name, 'icon' => 'fa-wrench'])

<div class="max-w-xl">
    <form action="{{ route('rentals.maintenance.store', $rental) }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="maintenance_date" value="{{ today()->toDateString() }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التكلفة <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="cost" min="0" required class="input-field w-full px-3 py-2.5 text-sm">
                <p class="text-xs text-slate-500 mt-1">سيتم خصم التكلفة من الخزينة تلقائياً</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">الوصف <span class="text-red-400">*</span></label>
                <textarea name="description" rows="2" required class="input-field w-full px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"></textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('rentals.show',$rental) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

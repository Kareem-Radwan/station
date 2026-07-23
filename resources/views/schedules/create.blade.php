@extends('layouts.app')
@section('title', 'إضافة جدول')
@section('content')

@include('partials.page-header', ['title' => 'إنشاء جدول عمليات جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-xl">
    <form action="{{ route('schedules.store') }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الأسبوع <span class="text-red-400">*</span></label>
                <input type="number" name="week_number" value="{{ old('week_number', now()->weekOfYear) }}" required min="1" max="53" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">السنة <span class="text-red-400">*</span></label>
                <input type="number" name="year" value="{{ old('year', now()->year) }}" required min="2020" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تبدأ من <span class="text-red-400">*</span></label>
                <input type="date" name="week_start" value="{{ old('week_start', now()->startOfWeek()->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                @error('week_start')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تنتهي في <span class="text-red-400">*</span></label>
                <input type="date" name="week_end" value="{{ old('week_end', now()->endOfWeek()->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                @error('week_end')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات / أهداف</label>
                <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 text-xs text-slate-300">
            <i class="fas fa-info-circle text-blue-400 ml-1"></i>
            بعد إنشاء الجدول، ستتمكن من ربط طلبات العمليات (صب الخرسانة) بهذا الأسبوع.
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> إنشاء الجدول</button>
            <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

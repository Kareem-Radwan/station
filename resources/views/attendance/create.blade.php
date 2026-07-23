@extends('layouts.app')
@section('title', 'تسجيل حضور')
@section('content')

@include('partials.page-header', ['title' => 'تسجيل حضور وانصراف', 'icon' => 'fa-clock'])

<div class="max-w-xl">
    <form action="{{ route('attendance.store') }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">الموظف <span class="text-red-400">*</span></label>
                <select name="employee_id" required class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">اختر الموظف</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id',request('employee_id'))==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">وقت الدخول <span class="text-red-400">*</span></label>
                <input type="time" name="time_in" value="{{ old('time_in', '08:00') }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">وقت الخروج</label>
                <input type="time" name="time_out" value="{{ old('time_out', '18:00') }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الخصومات</label>
                <input type="number" step="0.01" name="deduction" value="{{ old('deduction', 0) }}" min="0" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 text-xs text-slate-300">
            <i class="fas fa-info-circle text-blue-400 ml-1"></i>
            ساعات العمل الطبيعية: 8 صباحًا → 6 مساءً (10 ساعات). ما زاد يُحسب إضافيًا تلقائيًا.
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> تسجيل</button>
            <a href="{{ route('attendance.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

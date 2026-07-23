@extends('layouts.app')
@section('title', 'إضافة موظف')
@section('content')

@include('partials.page-header', ['title' => 'إضافة موظف جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-2xl">
    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الاسم الكامل <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المنصب / الوظيفة</label>
                    <input type="text" name="position" value="{{ old('position') }}" class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: سائق، عامل">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ التوظيف</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', today()->toDateString()) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الراتب الأساسي (شهري) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="base_salary" value="{{ old('base_salary') }}" required min="0" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('base_salary')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">معدل ساعة الإضافي</label>
                    <input type="number" step="0.01" name="overtime_rate" value="{{ old('overtime_rate') }}" min="0" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم الهوية</label>
                    <input type="text" name="national_id" value="{{ old('national_id') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('employees.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

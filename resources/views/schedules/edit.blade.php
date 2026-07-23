@extends('layouts.app')
@section('title', 'تعديل جدول')
@section('content')

@include('partials.page-header', ['title' => 'تعديل جدول أسبوع ' . $schedule->week_number, 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('schedules.update', $schedule) }}" method="POST" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الأسبوع <span class="text-red-400">*</span></label>
                <input type="number" name="week_number" value="{{ old('week_number', $schedule->week_number) }}" required min="1" max="53" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">السنة <span class="text-red-400">*</span></label>
                <input type="number" name="year" value="{{ old('year', $schedule->year) }}" required min="2020" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تبدأ من <span class="text-red-400">*</span></label>
                <input type="date" name="week_start" value="{{ old('week_start', $schedule->week_start->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                @error('week_start')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تنتهي في <span class="text-red-400">*</span></label>
                <input type="date" name="week_end" value="{{ old('week_end', $schedule->week_end->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                @error('week_end')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                <select name="status" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="draft"     {{ $schedule->status=='draft'    ?'selected':'' }}>مسودة</option>
                    <option value="published" {{ $schedule->status=='published'?'selected':'' }}>منشور</option>
                    <option value="completed" {{ $schedule->status=='completed'?'selected':'' }}>مكتمل</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات / أهداف</label>
                <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $schedule->notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

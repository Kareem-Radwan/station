@extends('layouts.app')
@section('title', 'تعديل حضور')
@section('content')

@include('partials.page-header', ['title' => 'تعديل سجل حضور', 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('attendance.update',$attendance) }}" method="POST" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <div class="bg-slate-800/50 rounded-xl p-3 mb-2 text-sm flex items-center gap-3">
            <i class="fas fa-user text-amber-400"></i>
            <span class="text-white font-medium">{{ $attendance->employee->name }}</span>
            <span class="text-slate-400 mr-2">{{ $attendance->date->format('d/m/Y') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">وقت الدخول</label>
                <input type="time" name="time_in" value="{{ old('time_in', $attendance->time_in) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">وقت الخروج</label>
                <input type="time" name="time_out" value="{{ old('time_out', $attendance->time_out) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الخصومات</label>
                <input type="number" step="0.01" name="deduction" value="{{ old('deduction', $attendance->deduction) }}" min="0" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $attendance->notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('attendance.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

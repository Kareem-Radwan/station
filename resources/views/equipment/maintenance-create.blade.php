@extends('layouts.app')
@section('title', 'تسجيل صيانة')
@section('content')

@include('partials.page-header', ['title' => 'تسجيل صيانة: '.$equipment->name, 'icon' => 'fa-wrench'])

<div class="max-w-xl">
    <form action="{{ route('equipment.maintenance.store', $equipment) }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="maintenance_date" value="{{ today()->toDateString() }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            @if($equipment->tracking_type === 'hours')
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تسجيل ساعات <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="hours_logged" min="{{ $equipment->current_hours + 0.01 }}" required
                    class="input-field w-full px-3 py-2.5 text-sm" placeholder="القيمة الجديدة">
                <p class="text-slate-500 text-xs mt-1">الحالية: {{ number_format($equipment->current_hours, 1) }} ساعة</p>
                @error('hours_logged')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @else
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تسجيل أيام <span class="text-red-400">*</span></label>
                <input type="number" step="1" name="days_logged" min="{{ $equipment->current_days + 1 }}" required
                    class="input-field w-full px-3 py-2.5 text-sm" placeholder="القيمة الجديدة">
                <p class="text-slate-500 text-xs mt-1">الحالية: {{ number_format($equipment->current_days, 0) }} يوم</p>
                @error('days_logged')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">نوع الصيانة <span class="text-red-400">*</span></label>
                <select name="type" required class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="routine"    {{ old('type')=='routine'   ?'selected':'' }}>صيانة دورية</option>
                    <option value="repair"     {{ old('type')=='repair'    ?'selected':'' }}>إصلاح</option>
                    <option value="spare_part" {{ old('type')=='spare_part'?'selected':'' }}>قطع غيار</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">الوصف <span class="text-red-400">*</span></label>
                <textarea name="description" rows="2" required class="input-field w-full px-3 py-2.5 text-sm">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التكلفة <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="cost" min="0" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المورد (اختياري)</label>
                <select name="supplier_id" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">بدون مورد</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"></textarea>
            </div>
        </div>
        <div class="flex gap-4 pt-2">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('equipment.show',$equipment) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

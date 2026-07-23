@extends('layouts.app')
@section('title', 'تعديل: '.$rental->equipment_name)
@section('content')

@include('partials.page-header', ['title' => 'تعديل: '.$rental->equipment_name, 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('rentals.update',$rental) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">نوع / وصف السيارة <span class="text-red-400">*</span></label>
                    <input type="text" name="equipment_name" value="{{ old('equipment_name',$rental->equipment_name) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم اللوحة / رقم السيارة</label>
                    <input type="text" name="car_number" value="{{ old('car_number',$rental->car_number) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم السواق</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name',$rental->driver_name) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">سعر الساعة</label>
                    <input type="number" step="0.01" name="hourly_price" value="{{ old('hourly_price',$rental->hourly_price) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">معيشة السواق (لكل وردية)</label>
                    <input type="number" step="0.01" name="driver_allowance" value="{{ old('driver_allowance',$rental->driver_allowance) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                    <select name="status" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="active"    {{ $rental->status==='active'   ?'selected':'' }}>نشط</option>
                        <option value="expired"   {{ $rental->status==='expired'  ?'selected':'' }}>منتهي</option>
                        <option value="cancelled" {{ $rental->status==='cancelled'?'selected':'' }}>ملغي</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes',$rental->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('rentals.show',$rental) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

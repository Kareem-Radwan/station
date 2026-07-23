@extends('layouts.app')
@section('title', 'إضافة معدة')
@section('content')

@include('partials.page-header', ['title' => 'إضافة معدة جديدة', 'icon' => 'fa-plus-circle'])

<div class="max-w-2xl">
    <form action="{{ route('equipment.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم المعدة <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: لودر سكاتسكو">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع المعدة <span class="text-red-400">*</span></label>
                    <select name="type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر النوع</option>
                        <option value="loader"          {{ old('type')=='loader'          ?'selected':'' }}>رافعة (لودر)</option>
                        <option value="mixer"           {{ old('type')=='mixer'           ?'selected':'' }}>خلاط</option>
                        <option value="service_vehicle" {{ old('type')=='service_vehicle' ?'selected':'' }}>مركبة خدمة</option>
                        <option value="pump"            {{ old('type')=='pump'            ?'selected':'' }}>مضخة</option>
                        <option value="generator"       {{ old('type')=='generator'       ?'selected':'' }}>مولدات</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الموديل</label>
                    <input type="text" name="model" value="{{ old('model') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم المعدة</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الشراء</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تكلفة الشراء</label>
                    <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع التتبع <span class="text-red-400">*</span></label>
                    <select name="tracking_type" id="trackingType" required class="input-field w-full px-3 py-2.5 text-sm" onchange="updateTrackingLabel()">
                        <option value="days" {{ old('tracking_type')=='days' ? 'selected' : '' }}>شغل بالأيام</option>
                        <option value="hours" {{ old('tracking_type')=='hours' ? 'selected' : '' }}>شغل بالساعات</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">
                        <span id="thresholdLabel">حد الصيانة (أيام)</span>
                    </label>
                    <input type="number" step="1" min="1" name="maintenance_threshold" value="{{ old('maintenance_threshold') }}" 
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: 400">
                    <p class="text-slate-500 text-xs mt-1">القيمة التراكمية للصيانة الدورية</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('equipment.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function updateTrackingLabel() {
    const trackingType = document.getElementById('trackingType').value;
    const label = document.getElementById('thresholdLabel');
    label.textContent = trackingType === 'hours' ? 'حد الصيانة (ساعات)' : 'حد الصيانة (أيام)';
}
</script>
@endpush
@endsection

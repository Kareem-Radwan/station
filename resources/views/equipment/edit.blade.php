@extends('layouts.app')
@section('title', 'تعديل معدة')
@section('content')

@include('partials.page-header', ['title' => 'تعديل: '.$equipment->name, 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('equipment.update', $equipment) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الاسم <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name',$equipment->name) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                    <select name="status" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="active"      {{ $equipment->status==='active'     ?'selected':'' }}>نشط</option>
                        <option value="maintenance" {{ $equipment->status==='maintenance'?'selected':'' }}>في الصيانة</option>
                        <option value="inactive"    {{ $equipment->status==='inactive'   ?'selected':'' }}>متوقف</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع التتبع</label>
                    <select name="tracking_type" id="trackingType" class="input-field w-full px-3 py-2.5 text-sm" onchange="updateTrackingLabel()">
                        <option value="days" {{ $equipment->tracking_type=='days' ? 'selected' : '' }}>شغل بالأيام</option>
                        <option value="hours" {{ $equipment->tracking_type=='hours' ? 'selected' : '' }}>شغل بالساعات</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">
                        <span id="thresholdLabel">حد الصيانة ({{ $equipment->tracking_type === 'hours' ? 'ساعات' : 'أيام' }})</span>
                    </label>
                    <input type="number" step="1" min="1" name="maintenance_threshold" value="{{ old('maintenance_threshold', $equipment->maintenance_threshold) }}" 
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: 400">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes',$equipment->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('equipment.show',$equipment) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
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

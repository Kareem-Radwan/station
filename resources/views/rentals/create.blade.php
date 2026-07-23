@extends('layouts.app')
@section('title', 'سيارة مستأجرة جديدة')
@section('content')

@include('partials.page-header', ['title' => 'إضافة سيارة مستأجرة', 'icon' => 'fa-car'])

<div class="max-w-2xl">
    <form action="{{ route('rentals.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="card p-6 space-y-4">
            <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
                <i class="fas fa-car text-amber-400"></i> بيانات السيارة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">نوع / وصف السيارة <span class="text-red-400">*</span></label>
                    <input type="text" name="equipment_name" value="{{ old('equipment_name') }}" required
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: سيارة نقل - تيوتا">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم اللوحة / رقم السيارة</label>
                    <input type="text" name="car_number" value="{{ old('car_number') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: أ ب ج 1234">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم السواق</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="الاسم الكامل للسواق">
                </div>
            </div>

            <h3 class="text-white font-bold border-b border-slate-700 pb-3 pt-2 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-green-400"></i> تسعيرة الوردية
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">سعر الساعة <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="hourly_price" value="{{ old('hourly_price') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">معيشة السواق (لكل وردية)</label>
                    <input type="number" step="0.01" name="driver_allowance" value="{{ old('driver_allowance') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="0.00">
                </div>
            </div>

            <h3 class="text-white font-bold border-b border-slate-700 pb-3 pt-2 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-400"></i> بيانات إضافية
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المؤجِّر (مورد)</label>
                    <select name="supplier_id" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر (اختياري)</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="cash"   {{ old('payment_type')=='cash'  ?'selected':'' }}>نقدي</option>
                        <option value="credit" {{ old('payment_type')=='credit'?'selected':'' }}>آجل</option>
                        <option value="mixed"  {{ old('payment_type')=='mixed' ?'selected':'' }}>مختلط</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">وصف / ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm">
                <i class="fas fa-save"></i> حفظ السيارة
            </button>
            <a href="{{ route('rentals.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>

@endsection

@extends('layouts.app')
@section('title', 'إضافة مورد')
@section('content')

@include('partials.page-header', ['title' => 'إضافة مورد جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-3xl">
    <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="card p-6 space-y-4">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3">بيانات المورد</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم المورد <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">العنوان</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر</option>
                        <option value="cash"   {{ old('payment_type')=='cash'  ?'selected':'' }}>نقدي</option>
                        <option value="credit" {{ old('payment_type')=='credit'?'selected':'' }}>آجل</option>
                        <option value="mixed"  {{ old('payment_type')=='mixed' ?'selected':'' }}>مختلط</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المواد المورَّدة</label>
                    <input type="text" name="materials[]" value="{{ old('materials.0') }}" class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: اسمنت، رمل">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

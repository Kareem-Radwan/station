@extends('layouts.app')
@section('title', 'تعديل العميل')
@section('content')

@include('partials.page-header', ['title' => 'تعديل: '.$customer->name, 'icon' => 'fa-user-edit'])

<div class="max-w-3xl">
    <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="card p-6 space-y-5">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3">البيانات الأساسية</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم العميل <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الموقع</label>
                    <input type="text" name="location" value="{{ old('location', $customer->location) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                    <select name="is_active" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="1" {{ $customer->is_active ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>موقف</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3">نوع الخرسانة والدفع</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الخرسانة <span class="text-red-400">*</span></label>
                    <select name="concrete_type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="operational" {{ $customer->concrete_type=='operational'?'selected':'' }}>تشغيلية</option>
                        <option value="complete"    {{ $customer->concrete_type=='complete'?'selected':'' }}>متكامل</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="cash"   {{ $customer->payment_type=='cash'?'selected':'' }}>نقدي</option>
                        <option value="credit" {{ $customer->payment_type=='credit'?'selected':'' }}>آجل</option>
                        <option value="mixed"  {{ $customer->payment_type=='mixed'?'selected':'' }}>مختلط</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm">
                <i class="fas fa-save"></i> حفظ التعديلات
            </button>
            <a href="{{ route('customers.show', $customer) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection

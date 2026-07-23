@extends('layouts.app')
@section('title', 'تعديل مورد')
@section('content')

@include('partials.page-header', ['title' => 'تعديل: '.$supplier->name, 'icon' => 'fa-edit'])

<div class="max-w-3xl">
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم المورد <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name',$supplier->name) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone',$supplier->phone) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">العنوان</label>
                    <input type="text" name="address" value="{{ old('address',$supplier->address) }}" class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع</label>
                    <select name="payment_type" class="input-field w-full px-3 py-2.5 text-sm">
                        @foreach(['cash'=>'نقدي','credit'=>'آجل','mixed'=>'مختلط'] as $v=>$l)
                        <option value="{{ $v }}" {{ $supplier->payment_type==$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                    <select name="is_active" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="1" {{ $supplier->is_active?'selected':'' }}>نشط</option>
                        <option value="0" {{ !$supplier->is_active?'selected':'' }}>موقف</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes',$supplier->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('suppliers.show',$supplier) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

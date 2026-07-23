@extends('layouts.app')
@section('title', 'تعديل فاتورة #'.$supplierPurchase->id)
@section('content')

@include('partials.page-header', ['title' => 'تعديل فاتورة #'.$supplierPurchase->id, 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('supplier-purchases.update',$supplierPurchase) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="card p-6 space-y-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                <select name="status" required class="input-field w-full px-3 py-2.5 text-sm">
                    @foreach(['pending'=>'معلق','partial'=>'دفع جزئي','paid'=>'مسدد'] as $v=>$l)
                    <option value="{{ $v }}" {{ $supplierPurchase->status==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes',$supplierPurchase->notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('supplier-purchases.show',$supplierPurchase) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'تعديل سند')
@section('content')

@include('partials.page-header', ['title' => 'تعديل سند #'.$receipt->id, 'icon' => 'fa-edit'])

<div class="max-w-2xl">
    <form action="{{ route('receipts.update',$receipt) }}" method="POST" class="card p-6 space-y-4">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="alert-error px-4 py-3 mb-4 flex flex-col gap-1.5">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                        <span class="text-sm">{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">نوع السند</label>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center gap-2 text-white cursor-pointer"><input type="radio" name="type" value="in" required class="accent-amber-400 w-4 h-4" {{ old('type',$receipt->type)=='in'?'checked':'' }}> سند قبض</label>
                    <label class="flex items-center gap-2 text-white cursor-pointer"><input type="radio" name="type" value="out" required class="accent-amber-400 w-4 h-4" {{ old('type',$receipt->type)=='out'?'checked':'' }}> سند صرف</label>
                </div>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ</label>
                <input type="date" name="receipt_date" value="{{ old('receipt_date', $receipt->receipt_date->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $receipt->amount) }}" required min="0.01" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الجهة / الاسم</label>
                <input type="text" name="recipient_name" value="{{ old('recipient_name', $receipt->recipient_name) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">البيان / الوصف</label>
                <textarea name="description" rows="2" required class="input-field w-full px-3 py-2.5 text-sm">{{ old('description', $receipt->description) }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('receipts.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

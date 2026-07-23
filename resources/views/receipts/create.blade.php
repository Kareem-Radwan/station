@extends('layouts.app')
@section('title', 'إضافة سند')
@section('content')

@include('partials.page-header', ['title' => 'إضافة سند جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-2xl">
    <form action="{{ route('receipts.store') }}" method="POST" class="card p-6 space-y-4">
        @csrf
        
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
                <label class="block text-slate-400 text-sm mb-1.5">نوع السند <span class="text-red-400">*</span></label>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center gap-2 text-white cursor-pointer"><input type="radio" name="type" value="in" required class="accent-amber-400 w-4 h-4" {{ old('type')=='in'?'checked':'' }}> سند قبض (وارد)</label>
                    <label class="flex items-center gap-2 text-white cursor-pointer"><input type="radio" name="type" value="out" required class="accent-amber-400 w-4 h-4" {{ old('type')=='out'?'checked':'' }}> سند صرف (صادر)</label>
                </div>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="receipt_date" value="{{ old('receipt_date', today()->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الجهة / الاسم <span class="text-red-400">*</span></label>
                <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" required class="input-field w-full px-3 py-2.5 text-sm" placeholder="استلمنا من / صرفنا لـ">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">البيان / الوصف <span class="text-red-400">*</span></label>
                <textarea name="description" rows="2" required class="input-field w-full px-3 py-2.5 text-sm">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ السند</button>
            <a href="{{ route('receipts.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection

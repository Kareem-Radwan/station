@extends('layouts.app')
@section('title', 'تفاصيل المصروف')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-white text-sm">المصروفات</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">مصروف #{{ $expense->id }}</span>
        </div>
        <span class="badge badge-blue">{{ $expense->category }}</span>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('expenses.edit',$expense) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
        <form action="{{ route('expenses.destroy',$expense) }}" method="POST" onsubmit="return confirm('تأكيد الحذف؟')">
            @csrf @method('DELETE')
            <button class="text-red-400 hover:text-red-300 border border-red-400/30 px-4 py-2 rounded-lg text-sm transition"><i class="fas fa-trash"></i> حذف</button>
        </form>
    </div>
</div>

<div class="card p-6 max-w-2xl">
    <div class="space-y-4 text-sm">
        <div class="flex justify-between border-b border-slate-700 pb-3"><span class="text-slate-400">التاريخ</span><span class="text-white">{{ $expense->expense_date->format('d/m/Y') }}</span></div>
        <div class="flex justify-between border-b border-slate-700 pb-3"><span class="text-slate-400">الفئة</span><span class="text-white">{{ $expense->category }}</span></div>
        <div class="flex justify-between border-b border-slate-700 pb-3"><span class="text-slate-400">الوصف</span><span class="text-white">{{ $expense->description }}</span></div>
        <div class="flex justify-between border-b border-slate-700 pb-3"><span class="text-slate-400">المبلغ</span><span class="text-red-400 font-bold text-xl">{{ number_format($expense->amount,0) }}</span></div>
        @if($expense->notes)
        <div class="pt-2">
            <span class="text-slate-400 block mb-2">ملاحظات:</span>
            <p class="text-slate-300 bg-slate-800/50 p-3 rounded-lg">{{ $expense->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

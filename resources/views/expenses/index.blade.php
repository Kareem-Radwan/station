@extends('layouts.app')
@section('title', 'المصروفات')
@section('content')

@include('partials.page-header', [
    'title'       => 'المصروفات العامة',
    'icon'        => 'fa-receipt',
    'createRoute' => 'expenses.create',
    'createLabel' => 'إضافة مصروف',
])

<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">الفئة</label>
            <select name="category" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(['وقود','صيانة','مواد','إداري','أخرى'] as $cat)
                <option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">من</label>
            <input type="date" name="from_date" value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">إلى</label>
            <input type="date" name="to_date" value="{{ request('to_date', today()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> بحث</button>
    </form>
</div>

@if($totalThisMonth > 0)
<div class="stat-card rounded-2xl p-4 mb-6 flex items-center gap-4 border border-red-500/20">
    <div class="text-sm text-slate-400">إجمالي الفترة المحددة:</div>
    <div class="text-red-400 font-bold text-2xl">{{ number_format($totalThisMonth, 0) }} <span class="text-sm font-normal text-slate-400">جنية</span></div>
</div>
@endif

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['التاريخ','الفئة','الوصف','المبلغ','ملاحظات','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($expenses as $exp)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $exp->expense_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3"><span class="badge badge-blue">{{ $exp->category_label }}</span></td>
                    <td class="px-4 py-3 text-white">{{ $exp->description }}</td>
                    <td class="px-4 py-3 text-red-400 font-bold">{{ number_format($exp->amount,0) }}</td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $exp->notes ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('expenses.edit',$exp) }}" class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 border border-amber-400/30 rounded"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('expenses.destroy',$exp) }}" method="POST" onsubmit="return confirm('حذف؟')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-300 text-xs px-2 py-1 border border-red-400/30 rounded"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-receipt text-4xl mb-3 opacity-30"></i><br>لا توجد مصروفات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $expenses->links() }}</div>@endif
</div>
@endsection

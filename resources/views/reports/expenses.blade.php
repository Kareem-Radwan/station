@extends('layouts.app')
@section('title', 'تقرير المصروفات')
@section('content')

@include('partials.page-header', ['title' => 'تقرير المصروفات', 'icon' => 'fa-receipt'])

{{-- Filters --}}
<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">الفئة</label>
            <select name="category" class="input-field w-full px-3 py-2 text-sm">
                <option value="">جميع الفئات</option>
                @foreach($allCategories as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
            <input type="date" name="from_date" value="{{ request('from_date', $fromDate) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ request('to_date', $toDate) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full">
                <i class="fas fa-filter"></i> عرض التقرير
            </button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap">
                <i class="fas fa-file-excel"></i> إكسل
            </button>
        </div>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="stat-card rounded-2xl p-4 border border-red-500/30">
        <p class="text-slate-400 text-xs mb-1">إجمالي المصروفات</p>
        <p class="text-xl font-bold text-red-400">{{ number_format($totalAmount, 0) }} جنيه</p>
        <p class="text-slate-500 text-xs mt-1">{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">عدد المصروفات</p>
        <p class="text-xl font-bold text-white">{{ $expenses->total() }} مصروف</p>
        <p class="text-slate-500 text-xs mt-1">{{ request('category') ? 'فئة: ' . ($allCategories[request('category')] ?? request('category')) : 'جميع الفئات' }}</p>
    </div>
</div>

{{-- Category Summary --}}
@if($categoryTotals->count() > 0)
<div class="card p-6 mb-6">
    <h3 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
        <i class="fas fa-chart-pie text-amber-400"></i> ملخص حسب الفئات
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($categoryTotals as $category => $total)
        <div class="bg-slate-800/50 rounded-lg p-3 border border-slate-700/50">
            <p class="text-slate-400 text-xs mb-1">{{ $category }}</p>
            <p class="text-white font-bold">{{ number_format($total, 0) }} جنيه</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Expenses Table --}}
<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-list text-amber-400"></i>
            المصروفات — {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} إلى {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
        </h3>
        <span class="text-slate-400 text-xs">{{ $expenses->total() }} مصروف</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">الفئة</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">الوصف</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">المبلغ</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">طريقة الدفع</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">ملاحظات</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">المسجل بواسطة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($expenses as $expense)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-3 py-3 text-slate-300 whitespace-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-3">
                        <span class="badge badge-blue text-xs">{{ $expense->category_label }}</span>
                    </td>
                    <td class="px-3 py-3 text-white">{{ $expense->description }}</td>
                    <td class="px-3 py-3 text-center font-bold text-red-400">{{ number_format($expense->amount, 0) }}</td>
                    <td class="px-3 py-3 text-center">
                        @if($expense->reference_type === 'contributor' && $expense->contributor)
                            <span class="badge badge-purple text-xs">مساهم</span>
                            <div class="text-slate-400 text-xs mt-0.5">{{ $expense->contributor->name }}</div>
                        @else
                            <span class="badge badge-green text-xs">نقدي من الخزينة</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-slate-400 text-xs">{{ $expense->notes ?? '-' }}</td>
                    <td class="px-3 py-3 text-center text-slate-300 text-xs">{{ $expense->recordedBy?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">لا توجد مصروفات في هذه الفترة</td>
                </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
            <tfoot>
                <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                    <td colspan="3" class="px-3 py-3 text-white">الإجمالي</td>
                    <td class="px-3 py-3 text-center text-red-400">{{ number_format($totalAmount, 0) }}</td>
                    <td colspan="3" class="px-3 py-3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($expenses->hasPages())
<div class="mt-6">
    {{ $expenses->links() }}
</div>
@endif

@endsection


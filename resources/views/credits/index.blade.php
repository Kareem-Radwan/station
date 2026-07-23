@extends('layouts.app')
@section('title', 'الديون والآجل')
@section('content')

@include('partials.page-header', [
    'title'       => 'سجل الديون (الآجل)',
    'icon'        => 'fa-calendar-check',
    'createRoute' => 'credits.create',
    'createLabel' => 'دين جديد',
])

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-red-600"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي الديون</p>
        <p class="text-3xl font-bold text-red-400">{{ number_format($totalCredits, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
        <p class="text-slate-400 text-xs mb-1">المسدد</p>
        <p class="text-3xl font-bold text-green-400">{{ number_format($totalPaid, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
        <p class="text-slate-400 text-xs mb-1">المتأخرات المتبقية</p>
        <p class="text-3xl font-bold text-amber-400">{{ number_format($totalOverdue, 0) }}</p>
    </div>
</div>

<div class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
    <form method="GET" class="flex-1 flex gap-3">
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">الحالة</label>
            <select name="status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(['active'=>'نشط (معلق)','paid'=>'مسدد','overdue'=>'متأخر'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">النوع</label>
            <select name="type" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="customer" {{ request('type')=='customer'?'selected':'' }}>عملاء</option>
                <option value="supplier" {{ request('type')=='supplier'?'selected':'' }}>موردين</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm mt-auto"><i class="fas fa-search"></i> بحث</button>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','العميل / الجهة','النوع','المبلغ','المسدد','المتبقي','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($credits as $credit)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $credit->id }}</td>
                    <td class="px-4 py-3 font-medium text-white">
                        @if($credit->creditable_type === 'customer' && $credit->creditable)
                            <a href="{{ route('customers.show', $credit->creditable) }}" class="text-blue-400 hover:text-blue-300">
                                {{ $credit->creditable->name }} <span class="text-xs text-slate-500">(عميل)</span>
                            </a>
                        @elseif($credit->creditable_type === 'supplier' && $credit->creditable)
                            <a href="{{ route('suppliers.show', $credit->creditable) }}" class="text-blue-400 hover:text-blue-300">
                                {{ $credit->creditable->name }} <span class="text-xs text-slate-500">(مورد)</span>
                            </a>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-400">
                        @if($credit->reference_type === 'order')
                            <a href="{{ route('orders.show', $credit->reference_id) }}" class="text-blue-400 hover:underline">طلب #{{ $credit->reference_id }}</a>
                        @elseif($credit->reference_type === 'purchase')
                            <a href="{{ route('supplier-purchases.show', $credit->reference_id) }}" class="text-blue-400 hover:underline">مشتريات #{{ $credit->reference_id }}</a>
                        @else
                            {{ $credit->reference_type ?? 'أخرى' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-amber-400">{{ number_format($credit->amount, 0) }}</td>
                    <td class="px-4 py-3 text-green-400">{{ number_format($credit->paid_amount, 0) }}</td>
                    <td class="px-4 py-3 font-bold text-red-400">{{ number_format($credit->remaining_amount, 0) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-{{ ['pending'=>'yellow','paid'=>'green','overdue'=>'red'][$credit->status]??'gray' }}">
                            {{ ['pending'=>'معلق','paid'=>'مسدد','overdue'=>'متأخر'][$credit->status]??$credit->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('credits.show', $credit) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-calendar-check text-4xl mb-3 opacity-30"></i><br>لا توجد سجلات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($credits->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $credits->links() }}</div>@endif
</div>
@endsection

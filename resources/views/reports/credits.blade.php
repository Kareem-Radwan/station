@extends('layouts.app')
@section('title', 'تقرير الديون والآجل')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الديون والآجل', 'icon' => 'fa-calendar-check'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">العميل</label>
            <select name="customer_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">كل العملاء</option>
                @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">حالة الدين</label>
            <select name="status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>نشط (معلق)</option>
                <option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>متأخر (تجاوز الاستحقاق)</option>
                <option value="paid" {{ request('status')=='paid'?'selected':'' }}>مسدد</option>
            </select>
        </div>
        <div class="flex gap-2 md:col-span-2 justify-end">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full md:w-auto"><i class="fas fa-filter"></i> عرض</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> تصدير إكسل</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي الديون (للفلتر المختار)</p>
        <p class="text-2xl font-bold text-white">{{ number_format($totalAmount, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي المسدد</p>
        <p class="text-2xl font-bold text-green-400">{{ number_format($totalPaid, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-red-500/30">
        <p class="text-slate-400 text-xs mb-1">المتبقي</p>
        <p class="text-2xl font-bold text-red-400">{{ number_format($totalAmount - $totalPaid, 0) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['العميل / الجهة','النوع / المرجع','المبلغ','المسدد','المتبقي','تاريخ الاستحقاق','الحالة'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($credits as $c)
                <tr class="table-row">
                    <td class="px-4 py-3 text-white">
                        @if($c->creditable_type === 'customer' && $c->creditable)
                            <a href="{{ route('customers.show', $c->creditable) }}" class="text-blue-400 hover:text-blue-300">
                                {{ $c->creditable->name }} <span class="text-xs text-slate-500">(عميل)</span>
                            </a>
                        @elseif($c->creditable_type === 'supplier' && $c->creditable)
                            <a href="{{ route('suppliers.show', $c->creditable) }}" class="text-blue-400 hover:text-blue-300">
                                {{ $c->creditable->name }} <span class="text-xs text-slate-500">(مورد)</span>
                            </a>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-400">
                        @if($c->reference_type === 'order')
                            <a href="{{ route('orders.show', $c->reference_id) }}" class="text-blue-400 hover:underline">طلب #{{ $c->reference_id }}</a>
                        @elseif($c->reference_type === 'purchase')
                            <a href="{{ route('supplier-purchases.show', $c->reference_id) }}" class="text-blue-400 hover:underline">مشتريات #{{ $c->reference_id }}</a>
                        @else
                            {{ $c->reference_type ?? 'أخرى' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-white">{{ number_format($c->amount, 0) }}</td>
                    <td class="px-4 py-3 text-green-400">{{ number_format($c->paid_amount, 0) }}</td>
                    <td class="px-4 py-3 text-red-400 font-bold">{{ number_format($c->remaining_amount, 0) }}</td>
                    <td class="px-4 py-3 {{ $c->due_date && $c->due_date->isPast() && $c->status!=='paid' ? 'text-red-400' : 'text-slate-300' }}">{{ $c->due_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-{{ ['pending'=>'yellow','paid'=>'green','overdue'=>'red'][$c->status]??'gray' }}">
                            {{ ['pending'=>'معلق','paid'=>'مسدد','overdue'=>'متأخر'][$c->status]??$c->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">لا توجد ديون مطابقة للبحث</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($credits->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $credits->links() }}</div>@endif
</div>
@endsection


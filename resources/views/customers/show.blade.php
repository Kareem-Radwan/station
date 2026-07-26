@extends('layouts.app')
@section('title', $customer->name)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white text-sm">العملاء</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">{{ $customer->name }}</span>
        </div>
        <span class="badge {{ $customer->is_active ? 'badge-green' : 'badge-gray' }}">{{ $customer->is_active ? 'نشط' : 'موقف' }}</span>
        <span class="badge badge-blue mr-2">{{ $customer->concrete_type_label }}</span>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-plus"></i> طلب جديد
        </a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-edit"></i> تعديل
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Card --}}
    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
            <i class="fas fa-user text-amber-400"></i> بيانات العميل
        </h3>
        <div class="space-y-3 text-sm">
            @foreach([
                ['الهاتف', $customer->phone ?? '-', 'fa-phone'],
                ['الموقع', $customer->location ?? '-', 'fa-map-marker-alt'],
                ['نوع الخرسانة', $customer->concrete_type_label, 'fa-industry'],
                ['نوع الدفع', $customer->payment_type_label, 'fa-credit-card'],
            ] as [$label, $value, $icon])
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                    <i class="fas {{ $icon }} text-slate-400 text-xs"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">{{ $label }}</p>
                    <p class="text-white">{{ $value }}</p>
                </div>
            </div>
            @endforeach
        </div>

        @if($customer->notes)
        <div class="bg-slate-800/50 rounded-lg p-3 text-slate-300 text-sm">{{ $customer->notes }}</div>
        @endif
    </div>

    {{-- Cement Balance (Operational only) --}}
    @if($customer->isOperational())
    <div class="card p-6">
        <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
            <i class="fas fa-weight text-amber-400"></i> رصيد الاسمنت
        </h3>
        <div class="text-center py-4">
            <p class="text-5xl font-bold {{ (float)$customer->cement_balance < 40 ? 'text-red-400' : 'text-amber-400' }}">
                {{ number_format($customer->cement_balance, 2) }}
            </p>
            <p class="text-slate-400 mt-1">طن</p>
        </div>
        <form action="{{ route('customers.add-cement', $customer) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-slate-400 text-xs mb-1 block">إضافة رصيد (طن)</label>
                <input type="number" step="0.001" name="amount" min="0.001" required
                    class="input-field w-full px-3 py-2 text-sm" placeholder="0.00">
            </div>
            <div>
                <label class="text-slate-400 text-xs mb-1 block">رقم الفاتورة</label>
                <input type="text" name="invoice_number"
                    class="input-field w-full px-3 py-2 text-sm" placeholder="رقم الفاتورة (اختياري)">
            </div>
            <button type="submit" class="w-full btn-primary text-white px-4 py-2 rounded-lg text-sm font-bold">
                <i class="fas fa-plus"></i> إضافة اسمنت
            </button>
        </form>
    </div>
    @endif

    {{-- Financial Summary --}}
    <div class="card p-6">
        <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
            <i class="fas fa-chart-bar text-blue-400"></i> الملخص المالي
        </h3>
        <div class="space-y-3 text-sm mt-4">
            @php
                $totalOrders   = $customer->getTotalOrdersAmount();
                $totalPaid     = $customer->getTotalPaid();
                $outstanding   = $customer->getOutstandingBalance();
            @endphp
            <div class="flex justify-between">
                <span class="text-slate-400">إجمالي الطلبات</span>
                <span class="text-white font-bold">{{ number_format($totalOrders, 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">المدفوعات</span>
                <span class="text-green-400 font-bold">{{ number_format($totalPaid, 0) }}</span>
            </div>
            <div class="flex justify-between border-t border-slate-700 pt-2">
                <span class="text-slate-400">الرصيد المتبقي</span>
                <span class="{{ $outstanding > 0 ? 'text-red-400' : 'text-green-400' }} font-bold text-lg">
                    {{ $outstanding > 0 ? '-' : '' }}{{ number_format(abs($outstanding), 0) }}
                </span>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('customer-payments.create') }}?customer_id={{ $customer->id }}"
                class="w-full btn-primary text-white px-4 py-2 rounded-lg text-sm font-bold block text-center">
                <i class="fas fa-hand-holding-usd"></i> تسجيل دفعة
            </a>
        </div>
    </div>

</div>

{{-- Orders Table --}}
<div class="card mt-6 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-bold flex items-center gap-2">
            <i class="fas fa-file-alt text-slate-400"></i> سجل الطلبات ({{ $customer->orders->count() }})
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">#</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الكمية</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الاسمنت المخصوم</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الإجمالي</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الدفع</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الحالة</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($customer->orders->take(15) as $order)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $order->id }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $order->delivery_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-white font-medium">{{ $order->quantity_m3 }} م³</td>
                    <td class="px-4 py-3 text-amber-400">
                        {{ $order->cement_deducted ? number_format($order->cement_deducted,2).' طن' : '-' }}
                    </td>
                    <td class="px-4 py-3 text-white">{{ $order->total_amount ? number_format($order->total_amount,0) : '-' }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $order->payment_type_label }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('orders.show', $order) }}" class="text-blue-400 hover:text-blue-300 text-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">لا توجد طلبات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

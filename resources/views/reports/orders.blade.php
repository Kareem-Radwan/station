@extends('layouts.app')
@section('title', 'تقرير الطلبات')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الطلبات', 'icon' => 'fa-box'])

<div class="card p-6 mb-6">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}" class="input-field px-3 py-2 text-sm w-full">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date', now()->toDateString()) }}" class="input-field px-3 py-2 text-sm w-full">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">العميل</label>
                <select name="customer_id" class="input-field px-3 py-2 text-sm w-full">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                <select name="status" class="input-field px-3 py-2 text-sm w-full">
                    <option value="">الكل</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدول</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">نوع الخرسانة</label>
                <select name="concrete_type" class="input-field px-3 py-2 text-sm w-full">
                    <option value="">الكل</option>
                    <option value="operational" {{ request('concrete_type') == 'operational' ? 'selected' : '' }}>تشغيلية</option>
                    <option value="complete" {{ request('concrete_type') == 'complete' ? 'selected' : '' }}>متكامل</option>
                </select>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-5 py-2 rounded-lg text-sm">
                <i class="fas fa-filter ml-1"></i> تصفية
            </button>
            <button type="submit" name="export" value="excel" class="bg-blue-600 hover:bg-green-700 text-white font-bold px-5 py-2 rounded-lg text-sm transition">
                <i class="fas fa-file-excel ml-1"></i> تصدير Excel
            </button>
            <a href="{{ route('reports.orders') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2 rounded-lg border border-slate-700 transition">
                إعادة تعيين
            </a>
        </div>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">عدد الطلبات</div>
        <div class="text-white text-xl font-bold">{{ $totals['count'] }}</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">إجمالي الكمية</div>
        <div class="text-blue-400 text-xl font-bold">{{ number_format($totals['quantity'], 2) }} م³</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">الأسمنت المخصوم</div>
        <div class="text-orange-400 text-xl font-bold">{{ number_format($totals['cement'], 2) }} طن</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">المبلغ الإجمالي</div>
        <div class="text-green-400 text-xl font-bold">{{ number_format($totals['total_amount'], 0) }} ج</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">النقدي</div>
        <div class="text-amber-400 text-xl font-bold">{{ number_format($totals['cash'], 0) }} ج</div>
    </div>
</div>

{{-- Orders Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">#</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">التاريخ</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">وقت التسليم</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">العميل</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">النوع</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">الخلطة</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">الكمية م³</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">الأسمنت طن</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">الموقع</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">سعر الوحدة</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">المبلغ</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">نقدي</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">آجل</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">نوع الدفع</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">تاريخ الاستحقاق</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-normal">الحالة</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-normal">ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition">
                    <td class="px-3 py-3 text-slate-400">#{{ $order->id }}</td>
                    <td class="px-3 py-3 text-white whitespace-nowrap">{{ $order->delivery_date->format('Y-m-d') }}</td>
                    <td class="px-3 py-3 text-slate-300 whitespace-nowrap">{{ $order->delivery_time ?? '-' }}</td>
                    <td class="px-3 py-3">
                        <div class="text-white font-medium">{{ $order->customer->name }}</div>
                        <div class="text-slate-400 text-xs">{{ $order->customer->phone }}</div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge {{ $order->concrete_type === 'operational' ? 'badge-blue' : 'badge-purple' }}">
                            {{ $order->concrete_type_label }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-slate-300">{{ $order->concreteMix->name ?? '-' }}</td>
                    <td class="px-3 py-3 text-center text-blue-400 font-bold">{{ number_format($order->quantity_m3, 2) }}</td>
                    <td class="px-3 py-3 text-center text-orange-400">{{ $order->cement_deducted > 0 ? number_format($order->cement_deducted, 3) : '-' }}</td>
                    <td class="px-3 py-3 text-slate-300 max-w-[120px] truncate" title="{{ $order->location ?? '' }}">{{ $order->location ?? '-' }}</td>
                    <td class="px-3 py-3 text-center text-slate-300">{{ $order->unit_price ? number_format($order->unit_price, 0) : '-' }}</td>
                    <td class="px-3 py-3 text-center text-green-400 font-bold">{{ number_format($order->total_amount ?? 0, 0) }}</td>
                    <td class="px-3 py-3 text-center text-amber-400">{{ number_format($order->cash_amount ?? 0, 0) }}</td>
                    <td class="px-3 py-3 text-center text-red-400">{{ number_format(($order->total_amount ?? 0) - ($order->cash_amount ?? 0), 0) }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge badge-gray text-xs">{{ $order->payment_type_label }}</span>
                    </td>
                    <td class="px-3 py-3 text-center text-slate-300 whitespace-nowrap">
                        @if($order->credit_due_date)
                            <span class="{{ $order->credit_due_date->isPast() && $order->status !== 'delivered' ? 'text-red-400 font-bold' : 'text-slate-300' }}">
                                {{ $order->credit_due_date->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </td>
                    <td class="px-3 py-3 text-slate-400 text-xs max-w-[120px] truncate" title="{{ $order->notes ?? '' }}">{{ $order->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="px-4 py-8 text-center text-slate-500">
                        <i class="fas fa-inbox text-4xl mb-2 block"></i>
                        لا توجد طلبات بالفلاتر المحددة
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($orders->count() > 0)
            <tfoot>
                <tr class="bg-slate-800/50 border-t-2 border-slate-700 font-bold text-xs">
                    <td colspan="6" class="px-3 py-3 text-white">الإجمالي</td>
                    <td class="px-3 py-3 text-center text-blue-400">{{ number_format($totals['quantity'], 2) }}</td>
                    <td class="px-3 py-3 text-center text-orange-400">{{ number_format($totals['cement'], 2) }}</td>
                    <td colspan="2" class="px-3 py-3"></td>
                    <td class="px-3 py-3 text-center text-green-400">{{ number_format($totals['total_amount'], 0) }}</td>
                    <td class="px-3 py-3 text-center text-amber-400">{{ number_format($totals['cash'], 0) }}</td>
                    <td class="px-3 py-3 text-center text-red-400">{{ number_format($totals['credit'], 0) }}</td>
                    <td colspan="4" class="px-3 py-3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection


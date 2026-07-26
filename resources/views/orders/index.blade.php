@extends('layouts.app')
@section('title', 'الطلبات')
@section('content')

@include('partials.page-header', [
    'title' => 'إدارة الطلبات',
    'icon'  => 'fa-file-alt',
    'createRoute' => 'orders.create',
    'createLabel' => 'طلب جديد',
])

{{-- Filters --}}
<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-40 flex-1">
            <label class="text-slate-400 text-xs mb-1 block">العميل</label>
            <select name="customer_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">كل العملاء</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ request('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">الحالة</label>
            <select name="status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(['pending'=>'معلق','scheduled'=>'مجدول','delivered'=>'تم التسليم','cancelled'=>'ملغي'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">من</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">إلى</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-search"></i> بحث
        </button>
        <a href="{{ route('orders.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','العميل','النوع','الكمية','موعد التسليم','الإجمالي','دفع العميل','سعر الطلب','الدفع','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($orders as $order)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $order->id }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('customers.show', $order->customer) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $order->customer->name }}</a>
                        @if($order->location)<p class="text-slate-500 text-xs">{{ $order->location }}</p>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $order->concrete_type==='operational'?'badge-blue':'badge-green' }}">
                            {{ $order->concrete_type_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-white font-bold">{{ $order->quantity_m3 }} م³</td>
                    <td class="px-4 py-3 text-slate-300">
                        {{ $order->delivery_date->format('d/m/Y') }}
                        @if($order->delivery_time)<p class="text-slate-500 text-xs">{{ $order->delivery_time }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-red-400 font-medium">
                        {{ $order->total_amount ? number_format($order->total_amount,0) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-blue-400 font-medium">
                        {{ $order->cash_amount ? number_format($order->cash_amount,0) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-amber-400 font-bold">
                        {{ $order->total_amount ? number_format($order->total_amount - ($order->cash_amount ?? 0), 0) : '-' }}
                    </td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $order->payment_type_label }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('orders.show', $order) }}" class="text-blue-400 hover:text-blue-300 px-2 py-1 border border-blue-400/30 rounded text-xs transition" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('orders.edit', $order) }}" class="text-amber-400 hover:text-amber-300 px-2 py-1 border border-amber-400/30 rounded text-xs transition" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟ سيتم حذف جميع تكاليف المواد والحركات المرتبطة من الخزينة والمخزون')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 px-2 py-1 border border-red-400/30 rounded text-xs transition" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @if($order->status === 'pending')
                            <form action="{{ route('orders.update-status', $order) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="text-green-400 hover:text-green-300 px-2 py-1 border border-green-400/30 rounded text-xs transition" title="تأكيد التسليم">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="px-4 py-12 text-center text-slate-500">
                    <i class="fas fa-file-alt text-4xl mb-3 opacity-30"></i><br>لا توجد طلبات
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">{{ $orders->links() }}</div>
    @endif
</div>
@endsection

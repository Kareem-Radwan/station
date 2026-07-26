@extends('layouts.app')
@section('title', 'العملاء')
@section('content')

@include('partials.page-header', [
    'title' => 'إدارة العملاء',
    'icon'  => 'fa-users',
    'createRoute' => 'customers.create',
    'createLabel' => 'إضافة عميل',
])

{{-- Filters --}}
<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="text-slate-400 text-xs mb-1 block">بحث</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم أو هاتف..."
                class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">نوع الخرسانة</label>
            <select name="type" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="operational" {{ request('type')=='operational'?'selected':'' }}>تشغيلية</option>
                <option value="complete"    {{ request('type')=='complete'?'selected':'' }}>متكامل</option>
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">الحالة</label>
            <select name="status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="active"   {{ request('status')=='active'?'selected':'' }}>نشط</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>موقف</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-search"></i> بحث
        </button>
        <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 bg-slate-800/50">
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">#</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">العميل</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">النوع</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الدفع</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">رصيد الاسمنت</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الطلبات</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الحالة</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($customers as $customer)
                <tr class="table-row transition-colors">
                    <td class="px-4 py-3 text-slate-500">{{ $customer->id }}</td>
                    <td class="px-4 py-3">
                        <div class="text-white font-medium">{{ $customer->name }}</div>
                        <div class="text-slate-500 text-xs">{{ $customer->phone }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $customer->concrete_type === 'operational' ? 'badge-blue' : 'badge-green' }}">
                            {{ $customer->concrete_type_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-300">{{ $customer->payment_type_label }}</td>
                    <td class="px-4 py-3">
                        @if($customer->isOperational())
                        <span class="{{ (float)$customer->cement_balance < 500 ? 'text-red-400' : 'text-amber-400' }} font-medium">
                            {{ number_format($customer->cement_balance, 2) }} طن
                        </span>
                        @else
                        <span class="text-slate-500 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-300">{{ $customer->orders_count }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $customer->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $customer->is_active ? 'نشط' : 'موقف' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('customers.show', $customer) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 rounded border border-blue-400/30 hover:border-blue-400/60 transition">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 rounded border border-amber-400/30 hover:border-amber-400/60 transition">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}" class="text-green-400 hover:text-green-300 text-xs px-2 py-1 rounded border border-green-400/30 hover:border-green-400/60 transition" title="طلب جديد">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                        <i class="fas fa-users text-4xl mb-3 opacity-30"></i>
                        <p>لا يوجد عملاء مسجلون</p>
                        <a href="{{ route('customers.create') }}" class="text-amber-400 text-sm mt-2 block hover:text-amber-300">
                            إضافة أول عميل ←
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection

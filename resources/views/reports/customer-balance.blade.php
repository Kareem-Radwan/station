@extends('layouts.app')
@section('title', 'تقرير كشف حساب عميل')
@section('content')

    @include('partials.page-header', ['title' => 'تقرير كشف حساب عميل', 'icon' => 'fa-file-invoice'])

    <div class="card p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-slate-400 text-xs mb-1">العميل</label>
                <select name="customer_id" class="input-field w-full px-3 py-2 text-sm">
                    <option value="">عرض جميع العملاء</option>
                    @foreach (\App\Models\Customer::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
                <input type="date" name="from_date"
                    value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}"
                    class="input-field w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date', today()->toDateString()) }}"
                    class="input-field w-full px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i
                        class="fas fa-filter"></i> عرض التقرير</button>
                <button type="submit" name="export" value="excel"
                    class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i
                        class="fas fa-file-excel"></i> إكسل</button>
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
     SINGLE CUSTOMER DETAILED VIEW
═══════════════════════════════════════════════════════════════════════ --}}
    @if (request('customer_id') && isset($customer))

        {{-- Customer Info Banner --}}
        <div class="card p-5 mb-5 border border-slate-700/50">
            <div class="flex flex-wrap items-start gap-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-amber-400 text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-white font-bold text-xl">{{ $customer->name }}</h2>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="badge {{ $customer->is_active ? 'badge-green' : 'badge-gray' }}">{{ $customer->is_active ? 'نشط' : 'موقف' }}</span>
                                <span class="badge badge-blue">{{ $customer->concrete_type_label }}</span>
                                <span class="badge badge-purple">{{ $customer->payment_type_label }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        @if ($customer->phone)
                            <div class="flex items-center gap-2 text-slate-300">
                                <i class="fas fa-phone text-slate-500 text-xs w-4"></i>
                                <span>{{ $customer->phone }}</span>
                            </div>
                        @endif
                        @if ($customer->location)
                            <div class="flex items-center gap-2 text-slate-300">
                                <i class="fas fa-map-marker-alt text-slate-500 text-xs w-4"></i>
                                <span>{{ $customer->location }}</span>
                            </div>
                        @endif
                        @if ($customer->address)
                            <div class="flex items-center gap-2 text-slate-300">
                                <i class="fas fa-home text-slate-500 text-xs w-4"></i>
                                <span>{{ $customer->address }}</span>
                            </div>
                        @endif
                        @if ($customer->concrete_strength)
                            <div class="flex items-center gap-2 text-slate-300">
                                <i class="fas fa-industry text-slate-500 text-xs w-4"></i>
                                <span>مقاومة: {{ $customer->concrete_strength }} </span>
                            </div>
                        @endif
                    </div>
                    @if ($customer->notes)
                        <div class="mt-2 text-slate-400 text-xs bg-slate-800/50 rounded-lg px-3 py-2">
                            {{ $customer->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-{{ $customer->isOperational() ? '6' : '5' }} gap-4 mb-6">
            <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
                <p class="text-slate-400 text-xs mb-1">إجمالي المبيعات</p>
                <p class="text-xl font-bold text-white">{{ number_format($totalOrders, 0) }}</p>
            </div>
            <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
                <p class="text-slate-400 text-xs mb-1">إجمالي المقبوضات</p>
                <p class="text-xl font-bold text-green-400">{{ number_format($totalPayments, 0) }}</p>
            </div>
            <div
                class="stat-card rounded-2xl p-4 border {{ $filteredBalance > 0 ? 'border-red-500/30' : ($filteredBalance < 0 ? 'border-green-500/30' : 'border-slate-700/50') }}">
                <p class="text-slate-400 text-xs mb-1">الرصيد المالي</p>
                <p
                    class="text-xl font-bold {{ $filteredBalance > 0 ? 'text-red-400' : ($filteredBalance < 0 ? 'text-green-400' : 'text-slate-400') }}">
                    @if ($filteredBalance > 0)
                        مديون {{ number_format(abs($filteredBalance), 0) }}
                    @elseif($filteredBalance < 0)
                        دائن {{ number_format(abs($filteredBalance), 0) }}
                    @else
                        متعادل
                    @endif
                </p>
            </div>
            <div class="stat-card rounded-2xl p-4 border border-blue-500/30">
                <p class="text-slate-400 text-xs mb-1">إجمالي الكمية</p>
                <p class="text-xl font-bold text-blue-400">{{ number_format($totalQuantityM3, 2) }} م³</p>
            </div>
            @if ($customer->isOperational())
                <div class="stat-card rounded-2xl p-4 border border-orange-500/30">
                    <p class="text-slate-400 text-xs mb-1">الأسمنت المخصوم</p>
                    <p class="text-xl font-bold text-orange-400">{{ number_format($totalCementDeducted, 2) }} طن</p>
                </div>
                <div
                    class="stat-card rounded-2xl p-4 border {{ (float) $customer->cement_balance < 0.5 ? 'border-red-500/30' : 'border-amber-500/30' }}">
                    <p class="text-slate-400 text-xs mb-1">رصيد الأسمنت الحالي</p>
                    <p
                        class="text-xl font-bold {{ (float) $customer->cement_balance < 0.5 ? 'text-red-400' : 'text-amber-400' }}">
                        {{ number_format($customer->cement_balance, 3) }} طن</p>
                </div>
            @else
                <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
                    <p class="text-slate-400 text-xs mb-1">نوع الخرسانة</p>
                    <p class="text-xl font-bold text-purple-400">{{ $customer->concrete_type_label }}</p>
                </div>
            @endif
        </div>

        {{-- Transaction Table --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-list text-amber-400"></i>
                    حركات الحساب — {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} إلى
                    {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                </h3>
                <span class="text-slate-400 text-xs">{{ $transactions->count() }} حركة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                            <th class="px-3 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                            <th class="px-3 py-3 text-right text-slate-400 font-medium">البيان</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">الكمية م³</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">سعر المتر</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">مبلغ الطلب</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">نقدي فوري</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">آجل</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">سداد</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">الرصيد المالي</th>
                            @if ($customer->isOperational())
                                <th class="px-3 py-3 text-center text-slate-400 font-medium">أسمنت مخصوم (طن)</th>
                                <th class="px-3 py-3 text-center text-slate-400 font-medium">رصيد أسمنت (طن)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($transactions as $t)
                            <tr
                                class="hover:bg-slate-800/30 transition {{ ($t->type ?? '') === 'payment' ? 'bg-green-900/5' : '' }}">
                                <td class="px-3 py-3 text-slate-300 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                                <td class="px-3 py-3 text-white">{{ $t->description }}</td>
                                <td class="px-3 py-3 text-center text-blue-400">
                                    {{ $t->quantity_m3 > 0 ? number_format($t->quantity_m3, 2) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-purple-400">
                                    {{ $t->unit_price > 0 ? number_format($t->unit_price, 2) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-red-400">
                                    {{ $t->debit > 0 ? number_format($t->debit, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-sky-400">
                                    {{ $t->cash_paid > 0 ? number_format($t->cash_paid, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-amber-400 font-bold">
                                    {{ $t->order_price > 0 ? number_format($t->order_price, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-green-400">
                                    {{ $t->credit > 0 ? number_format($t->credit, 0) : '-' }}</td>
                                <td
                                    class="px-3 py-3 text-center font-bold {{ $t->running_balance > 0 ? 'text-red-400' : ($t->running_balance < 0 ? 'text-green-400' : 'text-slate-400') }}">
                                    {{ $t->running_balance > 0 ? 'مديون ' : ($t->running_balance < 0 ? 'دائن ' : '') }}{{ number_format(abs($t->running_balance), 0) }}
                                </td>
                                @if ($customer->isOperational())
                                    <td class="px-3 py-3 text-center text-orange-400">
                                        {{ $t->cement_deducted > 0 ? number_format($t->cement_deducted, 3) : '-' }}</td>
                                    <td
                                        class="px-3 py-3 text-center font-bold {{ isset($t->running_cement) && $t->running_cement < 0.5 ? 'text-red-400' : 'text-amber-400' }}">
                                        {{ number_format($t->running_cement ?? 0, 3) }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $customer->isOperational() ? 11 : 9 }}"
                                    class="px-4 py-12 text-center text-slate-500">لا توجد حركات في هذه الفترة</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($transactions->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                                <td colspan="2" class="px-3 py-3 text-white">الإجمالي</td>
                                <td class="px-3 py-3 text-center text-blue-400">{{ number_format($totalQuantityM3, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center text-slate-400">-</td>
                                <td class="px-3 py-3 text-center text-red-400">{{ number_format($totalOrders, 0) }}</td>
                                <td class="px-3 py-3 text-center text-sky-400">
                                    {{ number_format($transactions->sum('cash_paid'), 0) }}</td>
                                <td class="px-3 py-3 text-center text-amber-400">
                                    {{ number_format($transactions->sum('order_price'), 0) }}</td>
                                <td class="px-3 py-3 text-center text-green-400">{{ number_format($totalPayments, 0) }}
                                </td>
                                <td
                                    class="px-3 py-3 text-center {{ $filteredBalance > 0 ? 'text-red-400' : 'text-green-400' }}">
                                    {{ number_format(abs($filteredBalance), 0) }}</td>
                                @if ($customer->isOperational())
                                    <td class="px-3 py-3 text-center text-orange-400">
                                        {{ number_format($totalCementDeducted, 3) }}</td>
                                    <td class="px-3 py-3 text-center text-amber-400">
                                        {{ number_format($customer->cement_balance, 3) }}</td>
                                @endif
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════
     ALL CUSTOMERS SUMMARY VIEW
═══════════════════════════════════════════════════════════════════════ --}}
    @elseif(isset($data))
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-users text-amber-400"></i> ملخص أرصدة جميع العملاء
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                            <th class="px-3 py-3 text-right text-slate-400 font-medium">اسم العميل</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">الهاتف</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">نوع الخرسانة</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">نوع الدفع</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">عدد الطلبات</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">إجمالي م³</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">إجمالي الطلبات</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">المدفوعات</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">الرصيد المتبقي</th>
                            <th class="px-3 py-3 text-center text-slate-400 font-medium">رصيد الأسمنت (طن)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($data as $r)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-3 py-3">
                                    <a href="{{ route('reports.customer-balance') }}?customer_id={{ $r['customer']->id }}&from_date={{ request('from_date') }}&to_date={{ request('to_date') }}"
                                        class="text-white font-medium hover:text-amber-400 transition">
                                        {{ $r['customer']->name }}
                                    </a>
                                    @if ($r['customer']->address)
                                        <div class="text-slate-500 text-xs mt-0.5">{{ $r['customer']->address }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-slate-300">{{ $r['customer']->phone ?? '-' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span
                                        class="badge {{ $r['customer']->concrete_type === 'operational' ? 'badge-blue' : 'badge-purple' }}">
                                        {{ $r['customer']->concrete_type_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="badge badge-gray">{{ $r['customer']->payment_type_label }}</span>
                                </td>
                                <td class="px-3 py-3 text-center text-white font-bold">{{ $r['order_count'] }}</td>
                                <td class="px-3 py-3 text-center text-blue-400">
                                    {{ number_format($r['total_concrete_m3'], 2) }}</td>
                                <td class="px-3 py-3 text-center text-white">{{ number_format($r['total_orders'], 0) }}
                                </td>
                                <td class="px-3 py-3 text-center text-green-400">
                                    {{ number_format($r['total_payments'], 0) }}</td>
                                <td
                                    class="px-3 py-3 text-center font-bold {{ $r['outstanding'] > 0 ? 'text-red-400' : ($r['outstanding'] < 0 ? 'text-green-400' : 'text-slate-400') }}">
                                    @if ($r['outstanding'] > 0)
                                        مديون {{ number_format(abs($r['outstanding']), 0) }}
                                    @elseif($r['outstanding'] < 0)
                                        دائن {{ number_format(abs($r['outstanding']), 0) }}
                                    @else
                                        <span class="text-slate-500">متعادل</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if ($r['customer']->concrete_type === 'operational')
                                        <span
                                            class="{{ $r['cement_balance'] < 0.5 ? 'text-red-400 font-bold' : 'text-amber-400' }}">
                                            {{ number_format($r['cement_balance'], 3) }}
                                        </span>
                                    @else
                                        <span class="text-slate-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-slate-500">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (isset($data) && $data->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                                <td colspan="4" class="px-3 py-3 text-white">الإجمالي</td>
                                <td class="px-3 py-3 text-center text-white">{{ $data->sum('order_count') }}</td>
                                <td class="px-3 py-3 text-center text-blue-400">
                                    {{ number_format($data->sum('total_concrete_m3'), 2) }}</td>
                                <td class="px-3 py-3 text-center text-white">
                                    {{ number_format($data->sum('total_orders'), 0) }}</td>
                                <td class="px-3 py-3 text-center text-green-400">
                                    {{ number_format($data->sum('total_payments'), 0) }}</td>
                                <td
                                    class="px-3 py-3 text-center {{ $data->sum('outstanding') > 0 ? 'text-red-400' : 'text-green-400' }}">
                                    {{ number_format(abs($data->sum('outstanding')), 0) }}</td>
                                <td class="px-3 py-3 text-center text-amber-400">
                                    {{ number_format($data->where('customer.concrete_type', 'operational')->sum('cement_balance'), 3) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

@endsection

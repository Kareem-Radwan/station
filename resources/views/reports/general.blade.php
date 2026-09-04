@extends('layouts.app')
@section('title', 'التقرير العام')
@section('content')

    @include('partials.page-header', ['title' => 'التقرير العام', 'icon' => 'fa-chart-pie'])

    {{-- Filter + Export --}}
    <div class="card p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="input-field px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn-primary text-white px-5 py-2 rounded-lg text-sm">
                <i class="fas fa-filter ml-1"></i> تحديث
            </button>
            <button type="submit" name="export" value="excel"
                class="btn-accent text-slate-900 font-bold px-5 py-2 rounded-lg text-sm">
                <i class="fas fa-file-excel ml-1"></i> تصدير
            </button>
            <span class="text-slate-500 text-xs self-end pb-1">
                التقرير يغطي الفترة: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} →
                {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
            </span>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     SECTION 1 — Financial Summary
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-amber-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-coins"></i> الملخص المالي
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
        <div class="card p-5 border border-green-500/30">
            <p class="text-slate-400 text-xs mb-1">إيرادات الطلبات</p>
            <p class="text-green-400 text-2xl font-bold">{{ number_format($financials['revenue'], 0) }}</p>
        </div>
        <div class="card p-5 border border-blue-500/30">
            <p class="text-slate-400 text-xs mb-1">الوارد للخزينة خلال الفترة</p>
            <p class="text-blue-400 text-2xl font-bold">{{ number_format($financials['cash_in'], 0) }}</p>
        </div>
        <div class="card p-5 border border-red-500/30">
            <p class="text-slate-400 text-xs mb-1">إجمالي المصروفات</p>
            <p class="text-red-400 text-2xl font-bold">{{ number_format($financials['expenses'], 0) }}</p>
        </div>
        <div class="card p-5 border {{ $financials['net_profit'] >= 0 ? 'border-emerald-500/30' : 'border-red-500/30' }}">
            <p class="text-slate-400 text-xs mb-1">صافي الربح</p>
            <p class="{{ $financials['net_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }} text-2xl font-bold">
                {{ number_format($financials['net_profit'], 0) }}
            </p>
        </div>
        <div class="card p-5 border border-amber-500/30">
            <p class="text-slate-400 text-xs mb-1">رصيد الخزينة الحالي</p>
            <p class="text-amber-400 text-2xl font-bold">{{ number_format($financials['treasury_balance'], 0) }}</p>
        </div>
    </div>

    {{-- Treasury IN Detail Table --}}
    <div class="card overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-arrow-down text-green-400"></i>
                الوارد للخزينة خلال الفترة
                <span class="text-slate-400 font-normal">({{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }})</span>
            </h3>
            <span class="text-green-400 font-bold text-sm">{{ number_format($financials['cash_in'], 0) }}</span>
        </div>
        @if($treasuryInRows->isEmpty())
        <div class="px-5 py-8 text-center text-slate-500 text-sm">لا توجد حركات واردة في هذه الفترة</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                        <th class="px-3 py-3 text-right text-slate-400 font-medium">البيان</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">التصنيف</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">المبلغ الوارد</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">الرصيد بعد العملية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @php
                    $categoryLabels = [
                        'customer_payment'   => 'دفعة عميل',
                        'supplier_payment'   => 'دفعة مورد',
                        'contributor_payment'        => 'دفعة مساهم',
                        'employee_borrow_repayment'        => 'سلفة موظف',
                        'neighboring_station_incoming'        => 'وارد محطات مجاورة',
                        'neighboring_station_outgoing'        => 'صادر محطات مجاورة',
                        'payroll'            => 'رواتب',
                        'salary'             => 'راتب',
                        'expense'            => 'مصروف',
                        'land_rent'          => 'إيجار أرض',
                        'equipment_rent'     => 'إيجار معدة',
                        'rental'             => 'إيجار',
                        'maintenance'        => 'صيانة',
                        'fuel'               => 'وقود',
                        'other'              => 'أخرى',
                        'income'             => 'إيراد',
                        'refund'             => 'استرداد',
                        'transfer'           => 'تحويل',
                        'deposit'            => 'إيداع',
                        'withdrawal'         => 'سحب',
                        'order_payment'      => 'دفعة طلب',
                        'advance'            => 'سلفة',
                        'credit_payment'     => 'سداد دين',
                        'purchase'           => 'مشتريات',
                        'purchase_payment'   => 'دفعة مشتريات',
                    ];
                @endphp
                @foreach($treasuryInRows as $tx)
                @php $catLabel = $categoryLabels[$tx->category] ?? $tx->category; @endphp
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-3 py-3 text-slate-300 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-3 text-white">{{ $tx->description ?? '-' }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge badge-gray text-xs">{{ $catLabel }}</span>
                    </td>
                        <td class="px-3 py-3 text-center text-green-400 font-bold">
                            {{ number_format($tx->amount, 0) }}
                        </td>
                        <td class="px-3 py-3 text-center text-amber-400">
                            {{ number_format($tx->balance_after, 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                        <td colspan="3" class="px-3 py-3 text-white">الإجمالي</td>
                        <td class="px-3 py-3 text-center text-green-400">{{ number_format($financials['cash_in'], 0) }}</td>
                        <td class="px-3 py-3 text-center text-amber-400">{{ number_format($financials['treasury_balance'], 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    {{-- Treasury OUT Detail Table --}}
    <div class="card overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-arrow-up text-red-400"></i>
                الصادر من الخزينة خلال الفترة
                <span class="text-slate-400 font-normal">({{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }})</span>
            </h3>
            <span class="text-red-400 font-bold text-sm">{{ number_format($financials['expenses'], 0) }}</span>
        </div>
        @if($treasuryOutRows->isEmpty())
        <div class="px-5 py-8 text-center text-slate-500 text-sm">لا توجد حركات صادرة في هذه الفترة</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                        <th class="px-3 py-3 text-right text-slate-400 font-medium">البيان</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">التصنيف</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">المبلغ الصادر</th>
                        <th class="px-3 py-3 text-center text-slate-400 font-medium">الرصيد بعد العملية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @php
                    $categoryLabels = [
                        'customer_payment'    => 'دفعة من عميل',
                        'supplier_payment'    => 'دفعة لمورد',
                        'inventory_purchase' => 'شراء مخزون',
                        'inventory_sale'     => 'بيع مخزون',
                        'receipt_in'          => 'سند قبض',
                        'material_cost'         => 'تكلفة المواد',
                        'receipt_out'         => 'سند صرف',
                        'rental'              => 'مصاريف إيجار',
                        'expense'             => 'مصروفات عامة',
                        'customer_deduction'   => 'خصم من عميل',
                        'contributor_payment_out'             => 'دفعة لمساهم',
                        'credit_payment'      => 'سداد ديون',
                        'rental_maintenance'  => 'صيانة المعدات المستأجرة',
                        'vehicle_equipment'   => 'مصاريف مركبات ومعدات',
                        'plant_maintenance'   => 'صيانة المحطة وقطع الغيار',
                        'salary'            => 'الرواتب',
                        'overtime'            => 'العمل الإضافي',
                        'employee_deductions' => 'خصومات الموظفين',
                        'employee_borrow'     => 'سلفة موظف',
                        'employee_borrow_repayment' => 'سداد سلفة موظف',
                        'contributor_payment' => 'دفعة من مساهم',
                        'employee_borrow_return' => 'إلغاء سلفة موظف',
                        'land_rent'           => 'إيجار الأرض',
                        'payroll'            => 'رواتب',
                        'equipment_rent'     => 'إيجار معدة',
                        'maintenance'        => 'صيانة',
                        'fuel'               => 'وقود',
                        'other'              => 'أخرى',
                        'income'             => 'إيراد',
                        'refund'             => 'استرداد',
                        'transfer'           => 'تحويل',
                        'deposit'            => 'إيداع',
                        'withdrawal'         => 'سحب',
                        'order_payment'      => 'دفعة طلب',
                        'advance'            => 'سلفة',
                        'purchase'           => 'مشتريات',
                        'purchase_payment'   => 'دفعة مشتريات',
                        'sales'              => 'مبيعات',
                        'expenses'           => 'مصروفات',
                    ];
                @endphp
                @foreach($treasuryOutRows as $tx)
                @php $catLabel = $categoryLabels[$tx->category] ?? $tx->category; @endphp
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-3 py-3 text-slate-300 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-3 text-white">{{ $tx->description ?? '-' }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge badge-gray text-xs">{{ $catLabel }}</span>
                    </td>
                        <td class="px-3 py-3 text-center text-red-400 font-bold">
                            {{ number_format($tx->amount, 0) }}
                        </td>
                        <td class="px-3 py-3 text-center text-amber-400">
                            {{ number_format($tx->balance_after, 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                        <td colspan="3" class="px-3 py-3 text-white">الإجمالي</td>
                        <td class="px-3 py-3 text-center text-red-400">{{ number_format($financials['expenses'], 0) }}</td>
                        <td class="px-3 py-3 text-center text-amber-400">{{ number_format($financials['treasury_balance'], 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>


    {{-- ═══════════════════════════════════════════════════════
     SECTION 2 — Orders Summary
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-blue-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-box"></i> ملخص الطلبات
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="card p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">عدد الطلبات</p>
            <p class="text-white text-xl font-bold">{{ $ordersSummary['count'] }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">إجمالي الكمية</p>
            <p class="text-blue-400 text-xl font-bold">{{ number_format($ordersSummary['total_m3'], 2) }} م³</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">المبلغ الإجمالي</p>
            <p class="text-green-400 text-xl font-bold">{{ number_format($ordersSummary['total_amount'], 0) }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-slate-400 text-xs mb-1">النقدي المحصل</p>
            <p class="text-amber-400 text-xl font-bold">{{ number_format($ordersSummary['cash_collected'], 0) }}</p>
        </div>
        <div class="card p-4 text-center border border-red-500/20">
            <p class="text-slate-400 text-xs mb-1">الآجل غير المحصل</p>
            <p class="text-red-400 text-xl font-bold">{{ number_format($ordersSummary['outstanding'], 0) }}</p>
        </div>
    </div>

    {{-- Customers Summary Table --}}
    <div class="card overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-700">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-users text-blue-400"></i> ملخص أرصدة العملاء
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-2 text-right text-slate-400">العميل</th>
                        <th class="px-3 py-2 text-center text-slate-400">عدد الطلبات</th>
                        <th class="px-3 py-2 text-center text-slate-400">الكمية م³</th>
                        <th class="px-3 py-2 text-center text-slate-400">الإجمالي</th>
                        <th class="px-3 py-2 text-center text-slate-400">المدفوع</th>
                        <th class="px-3 py-2 text-center text-slate-400">الرصيد</th>
                        <th class="px-3 py-2 text-center text-slate-400">أسمنت (طن)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($customersSummary as $r)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-3 py-2 text-white font-medium">{{ $r['customer']->name }}</td>
                            <td class="px-3 py-2 text-center text-slate-300">{{ $r['order_count'] }}</td>
                            <td class="px-3 py-2 text-center text-blue-400">{{ number_format($r['total_concrete_m3'], 2) }}
                            </td>
                            <td class="px-3 py-2 text-center text-white">{{ number_format($r['total_orders'], 0) }}</td>
                            <td class="px-3 py-2 text-center text-green-400">{{ number_format($r['total_payments'], 0) }}
                            </td>
                            <td
                                class="px-3 py-2 text-center font-bold {{ $r['outstanding'] > 0 ? 'text-red-400' : ($r['outstanding'] < 0 ? 'text-green-400' : 'text-slate-500') }}">
                                {{ $r['outstanding'] > 0 ? 'مديون ' . number_format(abs($r['outstanding']), 0) : ($r['outstanding'] < 0 ? 'دائن ' . number_format(abs($r['outstanding']), 0) : 'متعادل') }}
                            </td>
                            <td class="px-3 py-2 text-center">
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
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-800/50 border-t border-slate-700 font-bold text-xs">
                        <td class="px-3 py-2 text-white">الإجمالي</td>
                        <td class="px-3 py-2 text-center text-white">{{ collect($customersSummary)->sum('order_count') }}
                        </td>
                        <td class="px-3 py-2 text-center text-blue-400">
                            {{ number_format(collect($customersSummary)->sum('total_concrete_m3'), 2) }}</td>
                        <td class="px-3 py-2 text-center text-white">
                            {{ number_format(collect($customersSummary)->sum('total_orders'), 0) }}</td>
                        <td class="px-3 py-2 text-center text-green-400">
                            {{ number_format(collect($customersSummary)->sum('total_payments'), 0) }}</td>
                        <td class="px-3 py-2 text-center text-red-400">
                            {{ number_format(collect($customersSummary)->sum('outstanding'), 0) }}</td>
                        <td class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     SECTION 3 — Inventory & Suppliers
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-orange-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-boxes"></i> المخزون والموردون
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Inventory --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-boxes text-orange-400"></i> حالة المخزون
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-2 text-right text-slate-400">المادة</th>
                        <th class="px-3 py-2 text-center text-slate-400">الرصيد</th>
                        <th class="px-3 py-2 text-center text-slate-400">الحد الأدنى</th>
                        <th class="px-3 py-2 text-center text-slate-400">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($inventory as $r)
                        <tr class="{{ $r['is_low'] ? 'bg-red-900/10' : '' }} hover:bg-slate-800/30 transition">
                            <td class="px-3 py-2 text-white">{{ $r['item']->name }}</td>
                            <td
                                class="px-3 py-2 text-center {{ $r['is_low'] ? 'text-red-400 font-bold' : 'text-white' }}">
                                {{ number_format($r['current_stock'], 2) }} {{ $r['item']->unit }}
                            </td>
                            <td class="px-3 py-2 text-center text-slate-400">{{ number_format($r['threshold'], 2) }}</td>
                            <td class="px-3 py-2 text-center">
                                @if ($r['is_low'])
                                    <span class="badge badge-red text-xs">منخفض ⚠️</span>
                                @else
                                    <span class="badge badge-green text-xs">جيد</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Suppliers --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-truck text-blue-400"></i> أرصدة الموردين
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-2 text-right text-slate-400">المورد</th>
                        <th class="px-3 py-2 text-center text-slate-400">المشتريات</th>
                        <th class="px-3 py-2 text-center text-slate-400">المدفوعات</th>
                        <th class="px-3 py-2 text-center text-slate-400">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($suppliers as $r)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-3 py-2 text-white">{{ $r['supplier']->name }}</td>
                            <td class="px-3 py-2 text-center text-slate-300">{{ number_format($r['total_purchases'], 0) }}
                            </td>
                            <td class="px-3 py-2 text-center text-green-400">{{ number_format($r['total_payments'], 0) }}
                            </td>
                            <td
                                class="px-3 py-2 text-center font-bold {{ $r['outstanding'] > 0 ? 'text-amber-400' : 'text-green-400' }}">
                                {{ number_format($r['outstanding'], 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-800/50 border-t border-slate-700 font-bold text-xs">
                        <td class="px-3 py-2 text-white">الإجمالي</td>
                        <td class="px-3 py-2 text-center text-slate-300">
                            {{ number_format(collect($suppliers)->sum('total_purchases'), 0) }}</td>
                        <td class="px-3 py-2 text-center text-green-400">
                            {{ number_format(collect($suppliers)->sum('total_payments'), 0) }}</td>
                        <td class="px-3 py-2 text-center text-amber-400">
                            {{ number_format(collect($suppliers)->sum('outstanding'), 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     SECTION 4 — HR & Equipment
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-purple-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-users-cog"></i> الموارد البشرية والمعدات
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Payroll --}}
        <div class="card p-5">
            <h3 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-cyan-400"></i> ملخص الرواتب (الشهر الحالي)
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">عدد الموظفين المدرجين</p>
                    <p class="text-white text-xl font-bold">{{ $payroll['count'] }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">إجمالي صافي الرواتب</p>
                    <p class="text-cyan-400 text-xl font-bold">{{ number_format($payroll['net_total'], 0) }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">الأساسي</p>
                    <p class="text-white text-lg font-bold">{{ number_format($payroll['base_total'], 0) }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">الإضافي</p>
                    <p class="text-amber-400 text-lg font-bold">{{ number_format($payroll['overtime_total'], 0) }}</p>
                </div>
            </div>
        </div>

        {{-- Equipment Costs --}}
        <div class="card p-5">
            <h3 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-purple-400"></i> تكاليف المعدات
            </h3>
            <div class="space-y-2">
                @foreach ($equipment as $r)
                    <div class="flex items-center justify-between py-2 border-b border-slate-800">
                        <span class="text-slate-300 text-sm">{{ $r['equipment']->name }}</span>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="text-orange-400">وقود: {{ number_format($r['fuel_cost'], 0) }}</span>
                            <span class="text-blue-400">صيانة: {{ number_format($r['maintenance_cost'], 0) }}</span>
                            <span class="text-white font-bold">{{ number_format($r['total_cost'], 0) }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="flex items-center justify-between pt-2 font-bold">
                    <span class="text-white text-sm">الإجمالي</span>
                    <span
                        class="text-purple-400 text-lg">{{ number_format(collect($equipment)->sum('total_cost'), 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     SECTION 5 — Due Credits & Schedule Summary
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-red-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-calendar-exclamation"></i> الديون المستحقة والجدولة
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Due Credits --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-400"></i> ديون مستحقة وغير مسددة
                </h3>
                <span class="text-red-400 font-bold text-sm">{{ $credits->count() }} دين</span>
            </div>
            @if ($credits->isEmpty())
                <div class="p-6 text-center text-slate-500 text-sm">لا توجد ديون مستحقة 🎉</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-800/50 border-b border-slate-700">
                                <th class="px-3 py-2 text-right text-slate-400">الطرف</th>
                                <th class="px-3 py-2 text-center text-slate-400">المبلغ</th>
                                <th class="px-3 py-2 text-center text-slate-400">تاريخ الاستحقاق</th>
                                <th class="px-3 py-2 text-center text-slate-400">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($credits->take(8) as $r)
                                <tr class="{{ $r['credit']->status === 'overdue' ? 'bg-red-900/10' : '' }}">
                                    <td class="px-3 py-2 text-white">{{ $r['party']?->name ?? '-' }} <span
                                            class="text-slate-500">({{ $r['party_type'] }})</span></td>
                                    <td class="px-3 py-2 text-center text-amber-400 font-bold">
                                        {{ number_format($r['credit']->amount, 0) }}</td>
                                    <td
                                        class="px-3 py-2 text-center {{ $r['credit']->status === 'overdue' ? 'text-red-400' : 'text-slate-300' }}">
                                        {{ \Carbon\Carbon::parse($r['credit']->due_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span
                                            class="badge {{ $r['credit']->status === 'overdue' ? 'badge-red' : 'badge-yellow' }}">
                                            {{ $r['credit']->status === 'overdue' ? 'متأخر' : 'مستحق' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($credits->count() > 8)
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="px-3 py-2 text-center text-slate-500 text-xs">+
                                        {{ $credits->count() - 8 }} ديون إضافية — انظر تقرير الديون</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>

        {{-- Weekly Schedule Summary --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-calendar-week text-amber-400"></i> ملخص الجداول الأسبوعية
                </h3>
                <span class="text-amber-400 font-bold text-sm">{{ $scheduleSummary['count'] }} أسبوع</span>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">إجمالي الإدخالات</p>
                    <p class="text-white text-xl font-bold">{{ $scheduleSummary['total_entries'] }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">إجمالي الكمية</p>
                    <p class="text-blue-400 text-xl font-bold">{{ number_format($scheduleSummary['total_m3'], 2) }} م³</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">مكتمل</p>
                    <p class="text-green-400 text-xl font-bold">{{ $scheduleSummary['completed'] }}</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-3 text-center">
                    <p class="text-slate-400 text-xs mb-1">معلق</p>
                    <p class="text-yellow-400 text-xl font-bold">{{ $scheduleSummary['pending'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
     SECTION 6 — Contributors
════════════════════════════════════════════════════════════ --}}
    <div class="mb-2 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-700"></div>
        <h2 class="text-amber-400 font-bold text-sm uppercase tracking-widest flex items-center gap-2">
            <i class="fas fa-handshake"></i> المساهمون
        </h2>
        <div class="h-px flex-1 bg-slate-700"></div>
    </div>

    <div class="card overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                        <th class="px-3 py-2 text-right text-slate-400">المساهم</th>
                        <th class="px-3 py-2 text-center text-slate-400">نسبة الحصة</th>
                        <th class="px-3 py-2 text-center text-slate-400">قيمة الحصة</th>
                        <th class="px-3 py-2 text-center text-slate-400">المدفوع</th>
                        <th class="px-3 py-2 text-center text-slate-400">المتبقي</th>
                        <th class="px-3 py-2 text-center text-slate-400">نسبة السداد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($contributors as $c)
                        @php
                            $paid = $c->payments->sum('amount');
                            $remaining = $c->share_amount - $paid;
                            $pct = $c->share_amount > 0 ? min(100, ($paid / $c->share_amount) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-3 py-2 text-white font-medium">{{ $c->name }}</td>
                            <td class="px-3 py-2 text-center text-amber-400">{{ $c->share_percentage }}%</td>
                            <td class="px-3 py-2 text-center text-white">{{ number_format($c->share_amount, 0) }}</td>
                            <td class="px-3 py-2 text-center text-green-400">{{ number_format($paid, 0) }}</td>
                            <td
                                class="px-3 py-2 text-center {{ $remaining > 0 ? 'text-red-400' : 'text-green-400' }} font-bold">
                                {{ number_format($remaining, 0) }}</td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-700 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                            style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-400 w-10">{{ number_format($pct, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection


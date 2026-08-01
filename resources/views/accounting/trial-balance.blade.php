@extends('layouts.app')

@section('title', 'ميزان المراجعة - محاسبة القيد المزدوج')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-balance-scale text-white text-sm"></i>
                    </div>
                    ميزان المراجعة
                </h1>
                <p class="text-slate-400 text-sm mt-1">قراءة حصرية من قيود اليومية — نظام القيد المزدوج</p>
            </div>

            {{-- Navigation between accounting reports --}}
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('accounting.trial-balance') }}"
                    class="btn-accent px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                    <i class="fas fa-balance-scale"></i> ميزان المراجعة
                </a>
                <a href="{{ route('accounting.general-ledger') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-book"></i> دفتر الأستاذ
            </a>
            <a href="{{ route('accounting.balance-sheet') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-file-invoice"></i> الميزانية العمومية
            </a>
            <a href="{{ route('accounting.income-statement') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-chart-line"></i> قائمة الدخل
            </a>
            <a href="{{ route('accounting.journal-book') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-scroll"></i> دفتر اليومية
            </a>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="card p-4">
            <form method="GET" action="{{ route('accounting.trial-balance') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs text-slate-400 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs text-slate-400 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $toDate }}"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn-accent px-5 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-filter"></i> عرض
            </button>
            <a href="{{ route('accounting.trial-balance.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
               class="btn-success px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-file-excel"></i> تصدير Excel
            </a>
            <button type="button" onclick="rebuildAccounting()" class="btn-warning px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> اعادة بناء
            </button>
            </form>
        </div>

        {{-- Balance Status --}}
        @if ($totals['balanced'])
            <div class="alert-success px-4 py-3 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
                <div>
                    <div class="font-bold">الميزان متوازن ✓</div>
                    <div class="text-xs opacity-80">مجموع المدين = مجموع الدائن =
                        {{ number_format($totals['total_debit'], 2) }} جنيه</div>
                </div>
            </div>
        @else
            <div class="alert-error px-4 py-3 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                <div>
                    <div class="font-bold">تحذير: الميزان غير متوازن</div>
                    <div class="text-xs opacity-80">
                        مدين: {{ number_format($totals['total_debit'], 2) }} |
                        دائن: {{ number_format($totals['total_credit'], 2) }} |
                        فرق: {{ number_format(abs($totals['total_debit'] - $totals['total_credit']), 2) }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $typeLabels = [
                    'asset' => 'أصول',
                    'liability' => 'خصوم',
                    'equity' => 'حقوق الملكية',
                    'revenue' => 'إيرادات',
                    'expense' => 'مصروفات',
                ];
                $typeColors = [
                    'asset' => 'blue',
                    'liability' => 'red',
                    'equity' => 'purple',
                    'revenue' => 'green',
                    'expense' => 'yellow',
                ];
                $typeIcons = [
                    'asset' => 'fa-building',
                    'liability' => 'fa-hand-holding-usd',
                    'equity' => 'fa-users',
                    'revenue' => 'fa-chart-line',
                    'expense' => 'fa-file-invoice-dollar',
                ];
            @endphp
            @foreach ($grouped as $type => $typeRows)
                @php $total = $typeRows->sum('net_balance'); @endphp
                <div class="stat-card p-4 rounded-xl border border-slate-700">
                    <div class="text-xs text-slate-400 mb-1">{{ $typeLabels[$type] ?? $type }}</div>
                    <div class="text-lg font-bold {{ $total >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format(abs($total), 2) }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ $typeRows->count() }} حساب</div>
                </div>
            @endforeach
        </div>

        {{-- Main Table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                <h2 class="font-bold text-white flex items-center gap-2">
                    <i class="fas fa-table text-amber-400"></i>
                    تفاصيل الحسابات
                </h2>
                <div class="text-xs text-slate-400">
                    الفترة: {{ $fromDate }} → {{ $toDate }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/50">
                            <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">رقم الحساب</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">اسم الحساب</th>
                            <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">النوع</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">مدين</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">دائن</th>
                            <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">الرصيد</th>
                            <th class="px-4 py-3 text-center text-xs text-slate-400 font-medium">طبيعة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @php $currentType = null; @endphp
                        @foreach ($rows as $row)
                            {{-- Section header when account_type changes --}}
                            @if ($currentType !== $row->account_type)
                                @php $currentType = $row->account_type; @endphp
                                <tr class="bg-slate-800/80">
                                    <td colspan="7"
                                        class="px-4 py-2 text-xs font-bold text-amber-400 uppercase tracking-wider">
                                        {{ $typeLabels[$row->account_type] ?? $row->account_type }}
                                    </td>
                                </tr>
                            @endif

                            <tr class="table-row hover:bg-slate-800/30 transition">
                                <td class="px-4 py-2.5 font-mono text-slate-300 text-xs">{{ $row->account_number }}</td>
                                <td class="px-4 py-2.5 text-white">{{ $row->account_name }}</td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="badge badge-blue text-xs">{{ $typeLabels[$row->account_type] ?? $row->account_type }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-left font-mono text-blue-300">
                                    @if ($row->total_debit > 0)
                                        {{ number_format($row->total_debit, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-left font-mono text-orange-300">
                                    @if ($row->total_credit > 0)
                                        {{ number_format($row->total_credit, 2) }}
                                    @endif
                                </td>
                                <td
                                    class="px-4 py-2.5 text-left font-mono font-bold {{ $row->net_balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ number_format(abs($row->net_balance), 2) }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span
                                        class="badge {{ $row->normal_balance === 'debit' ? 'badge-blue' : 'badge-yellow' }} text-xs">
                                        {{ $row->normal_balance === 'debit' ? 'مدين' : 'دائن' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Grand Totals --}}
                    <tfoot>
                        <tr class="border-t-2 border-amber-500/50 bg-slate-800">
                            <td colspan="3" class="px-4 py-3 font-bold text-white text-right">الإجمالي</td>
                            <td class="px-4 py-3 text-left font-bold font-mono text-blue-300 text-base">
                                {{ number_format($totals['total_debit'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-left font-bold font-mono text-orange-300 text-base">
                                {{ number_format($totals['total_credit'], 2) }}
                            </td>
                            <td colspan="2" class="px-4 py-3">
                                @if ($totals['balanced'])
                                    <span class="badge badge-green">متوازن</span>
                                @else
                                    <span class="badge badge-red">غير متوازن</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Info note --}}
        <div class="text-xs text-slate-500 text-center">
            <i class="fas fa-info-circle ml-1"></i>
            هذا الميزان يقرأ حصرياً من جدول <code
                class="text-slate-400 bg-slate-800 px-1 rounded">journal_entry_lines</code> — لا يقرأ من جداول العمليات
            مباشرةً
        </div>

    </div>
@endsection

@push('scripts')
<script>
function rebuildAccounting() {
    if (!confirm('هل أنت متأكد من إعادة بناء المحاسبة؟\n\nهذه العملية ستقوم بإعادة ترحيل جميع القيود المحاسبية من البداية.\nقد تستغرق بضع دقائق.')) {
        return;
    }

    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ إعادة البناء...';

    fetch('{{ route('accounting.rebuild') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalContent;

        if (data.success) {
            alert('✅ تم إعادة البناء بنجاح!\n\n' + 
                  'مرحّل: ' + data.posted + '\n' +
                  'متجاوز: ' + data.skipped + 
                  (data.errors > 0 ? '\nأخطاء: ' + data.errors : ''));
            location.reload();
        } else {
            alert('❌ حدث خطأ أثناء إعادة البناء:\n' + (data.message || 'خطأ غير معروف'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        alert('❌ فشل الاتصال بالخادم:\n' + error.message);
    });
}
</script>
@endpush

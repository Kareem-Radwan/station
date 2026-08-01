@extends('layouts.app')

@section('title', 'دفتر الأستاذ العام')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center shadow-lg">
                    <i class="fas fa-book text-white text-sm"></i>
                </div>
                دفتر الأستاذ العام
            </h1>
            <p class="text-slate-400 text-sm mt-1">سجل كل حركة لحساب محدد مع الرصيد الجاري</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('accounting.trial-balance') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-balance-scale"></i> ميزان المراجعة
            </a>
            <a href="{{ route('accounting.balance-sheet') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-file-invoice"></i> الميزانية العمومية
            </a>
            <a href="{{ route('accounting.income-statement') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-chart-line"></i> قائمة الدخل
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('accounting.general-ledger') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs text-slate-400 mb-1">الحساب</label>
                <select name="account_id" class="input-field w-full px-3 py-2 text-sm" required>
                    <option value="">-- اختر حساباً --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                            {{ $acc->account_number }} — {{ $acc->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[170px]">
                <label class="block text-xs text-slate-400 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field w-full px-3 py-2 text-sm">
            </div>
            <div class="min-w-[170px]">
                <label class="block text-xs text-slate-400 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="input-field w-full px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn-accent px-5 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-search"></i> عرض
            </button>
            @if($accountId)
            <a href="{{ route('accounting.general-ledger.export', ['from_date' => $fromDate, 'to_date' => $toDate, 'account_id' => $accountId]) }}"
               class="btn-success px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-file-excel"></i> تصدير Excel
            </a>
            @endif
        </form>
    </div>

    @if($ledger)
        @php $account = $ledger['account']; @endphp

        {{-- Account Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card p-4 rounded-xl">
                <div class="text-xs text-slate-400 mb-1">رصيد الافتتاح</div>
                <div class="text-xl font-bold {{ $ledger['opening_balance'] >= 0 ? 'text-blue-400' : 'text-red-400' }}">
                    {{ number_format(abs($ledger['opening_balance']), 2) }}
                </div>
            </div>
            <div class="stat-card p-4 rounded-xl">
                <div class="text-xs text-slate-400 mb-1">إجمالي مدين</div>
                <div class="text-xl font-bold text-blue-400">{{ number_format($ledger['total_debit'], 2) }}</div>
            </div>
            <div class="stat-card p-4 rounded-xl">
                <div class="text-xs text-slate-400 mb-1">إجمالي دائن</div>
                <div class="text-xl font-bold text-orange-400">{{ number_format($ledger['total_credit'], 2) }}</div>
            </div>
            <div class="stat-card p-4 rounded-xl">
                <div class="text-xs text-slate-400 mb-1">رصيد الإغلاق</div>
                <div class="text-xl font-bold {{ $ledger['closing_balance'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ number_format(abs($ledger['closing_balance']), 2) }}
                    <span class="text-xs font-normal text-slate-400">
                        {{ $account->normal_balance === 'debit' ? 'مدين' : 'دائن' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Ledger Lines Table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                <h2 class="font-bold text-white">
                    {{ $account->account_number }} — {{ $account->account_name }}
                </h2>
                <span class="text-xs text-slate-400">{{ $ledger['lines']->count() }} قيد | {{ $fromDate }} → {{ $toDate }}</span>
            </div>

            @if($ledger['lines']->isEmpty())
                <div class="px-6 py-12 text-center text-slate-500">
                    <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                    <p>لا توجد حركات لهذا الحساب في الفترة المحددة</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700 bg-slate-800/50">
                                <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">التاريخ</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">رقم القيد</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">البيان</th>
                                <th class="px-4 py-3 text-right text-xs text-slate-400 font-medium">المرجع</th>
                                <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">مدين</th>
                                <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">دائن</th>
                                <th class="px-4 py-3 text-left text-xs text-slate-400 font-medium">الرصيد الجاري</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            {{-- Opening balance row --}}
                            <tr class="bg-slate-800/60">
                                <td class="px-4 py-2 text-slate-400 text-xs">{{ $fromDate }}</td>
                                <td class="px-4 py-2 font-mono text-xs text-slate-500">—</td>
                                <td class="px-4 py-2 text-slate-300 text-xs font-medium" colspan="2">رصيد أول المدة</td>
                                <td class="px-4 py-2 text-left text-slate-400">—</td>
                                <td class="px-4 py-2 text-left text-slate-400">—</td>
                                <td class="px-4 py-2 text-left font-mono font-bold text-blue-300">
                                    {{ number_format($ledger['opening_balance'], 2) }}
                                </td>
                            </tr>

                            @foreach($ledger['lines'] as $line)
                                @php
                                    $refLabel = \App\Accounting\Helpers\ReferenceTypeMapper::label($line->reference_type);
                                    $refIcon  = \App\Accounting\Helpers\ReferenceTypeMapper::icon($line->reference_type);
                                    $refColor = \App\Accounting\Helpers\ReferenceTypeMapper::color($line->reference_type);
                                    $refUrl   = \App\Accounting\Helpers\ReferenceTypeMapper::url(
                                        $line->reference_type,
                                        $line->reference_id
                                    );
                                @endphp
                                <tr class="table-row hover:bg-slate-800/30 transition">
                                    <td class="px-4 py-2.5 text-slate-300 text-xs whitespace-nowrap">{{ $line->date }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-amber-400">{{ $line->entry_no }}</td>
                                    <td class="px-4 py-2.5 text-white text-xs">{{ $line->line_description ?: $line->entry_description }}</td>
                                    <td class="px-4 py-2.5 text-xs">
                                        @if($line->reference_type)
                                            @if($refUrl)
                                                <a href="{{ $refUrl }}" target="_blank"
                                                   class="badge badge-{{ $refColor }} inline-flex items-center gap-1 hover:opacity-80 transition">
                                                    <i class="fas {{ $refIcon }} text-[9px]"></i>
                                                    {{ $refLabel }}
                                                    @if($line->reference_id)<span class="opacity-60">#{{ $line->reference_id }}</span>@endif
                                                    <i class="fas fa-external-link-alt opacity-40 text-[8px]"></i>
                                                </a>
                                            @else
                                                <span class="badge badge-{{ $refColor }} inline-flex items-center gap-1">
                                                    <i class="fas {{ $refIcon }} text-[9px]"></i>
                                                    {{ $refLabel }}
                                                    @if($line->reference_id)<span class="opacity-60">#{{ $line->reference_id }}</span>@endif
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-left font-mono text-blue-300">
                                        @if($line->debit > 0) {{ number_format($line->debit, 2) }} @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-left font-mono text-orange-300">
                                        @if($line->credit > 0) {{ number_format($line->credit, 2) }} @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-left font-mono font-bold {{ $line->running_balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ number_format(abs($line->running_balance), 2) }}
                                        <span class="text-xs font-normal text-slate-500 mr-1">
                                            {{ $line->running_balance >= 0 ? ($account->normal_balance === 'debit' ? 'م' : 'د') : ($account->normal_balance === 'debit' ? 'د' : 'م') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-amber-500/50 bg-slate-800">
                                <td colspan="4" class="px-4 py-3 font-bold text-white">الإجمالي</td>
                                <td class="px-4 py-3 text-left font-bold font-mono text-blue-300">{{ number_format($ledger['total_debit'], 2) }}</td>
                                <td class="px-4 py-3 text-left font-bold font-mono text-orange-300">{{ number_format($ledger['total_credit'], 2) }}</td>
                                <td class="px-4 py-3 text-left font-bold font-mono {{ $ledger['closing_balance'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ number_format(abs($ledger['closing_balance']), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

    @else
        <div class="card px-6 py-12 text-center text-slate-500">
            <i class="fas fa-book-open text-5xl mb-4 opacity-20"></i>
            <p class="text-lg">اختر حساباً لعرض دفتر الأستاذ</p>
        </div>
    @endif

</div>
@endsection

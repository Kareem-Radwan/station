@extends('layouts.app')
@section('title', 'تقرير المساهمين')

@section('content')

    @include('partials.page-header', [
        'title' => 'كشف حساب مساهم',
        'icon' => 'fa-handshake',
    ])

    <div class="card p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-slate-400 text-xs mb-1">المساهم</label>
                <select name="contributor_id" required class="input-field w-full px-3 py-2">
                    <option value="">اختر المساهم</option>
                    @foreach (\App\Models\Contributor::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" {{ request('contributor_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
                <input type="date" name="from_date"
                    value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}"
                    class="input-field w-full px-3 py-2">
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date', today()->toDateString()) }}"
                    class="input-field w-full px-3 py-2">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg w-full">
                    <i class="fas fa-filter"></i> عرض
                </button>
                @if (request('contributor_id'))
                    <button type="submit" name="export" value="excel"
                        class="btn-accent text-slate-900 px-4 py-2 rounded-lg">
                        <i class="fas fa-file-excel"></i>
                    </button>
                @endif
            </div>
        </form>
    </div>

    @if (isset($contributor))

        {{-- Contributor Info Banner --}}
        <div class="card p-5 mb-5 border border-slate-700/50">
            <div class="flex flex-wrap items-start gap-6">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-handshake text-amber-400 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-white font-bold text-xl">{{ $contributor->name }}</h2>
                        <span class="badge {{ $contributor->is_active ? 'badge-green' : 'badge-gray' }}">{{ $contributor->is_active ? 'نشط' : 'موقف' }}</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm mb-2">
                        @if($contributor->phone)
                        <div class="flex items-center gap-2 text-slate-300">
                            <i class="fas fa-phone text-slate-500 text-xs w-4"></i>
                            <span>{{ $contributor->phone }}</span>
                        </div>
                        @endif
                        @if($contributor->national_id)
                        <div class="flex items-center gap-2 text-slate-300">
                            <i class="fas fa-id-card text-slate-500 text-xs w-4"></i>
                            <span>{{ $contributor->national_id }}</span>
                        </div>
                        @endif
                        @if($contributor->address)
                        <div class="flex items-center gap-2 text-slate-300">
                            <i class="fas fa-map-marker-alt text-slate-500 text-xs w-4"></i>
                            <span>{{ $contributor->address }}</span>
                        </div>
                        @endif
                    </div>
                    @if($contributor->notes)
                    <div class="text-slate-400 text-xs bg-slate-800/50 rounded-lg px-3 py-2">{{ $contributor->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div class="stat-card p-5 border border-slate-700/50 rounded-2xl">
                <p class="text-slate-400 text-xs">قيمة الحصة الإجمالية</p>
                <p class="text-white text-2xl font-bold">{{ number_format($shareAmount, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">الرصيد الحالي المستحق</p>
            </div>
            <div class="stat-card p-5 border border-green-500/30 rounded-2xl">
                <p class="text-slate-400 text-xs">إجمالي المدفوع له</p>
                <p class="text-green-400 text-2xl font-bold">{{ number_format($totalPaid, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">ما دفعناه للمساهم</p>
            </div>
            <div class="stat-card p-5 border {{ $remaining > 0 ? 'border-amber-500/30' : 'border-slate-700/50' }} rounded-2xl">
                <p class="text-slate-400 text-xs">المتبقي</p>
                <p class="{{ $remaining > 0 ? 'text-amber-400' : 'text-slate-400' }} text-2xl font-bold">{{ number_format($remaining, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">{{ $remaining > 0 ? 'مطلوب دفعه' : 'تم السداد' }}</p>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-list text-amber-400"></i> سجل الدفعات
                </h3>
                <span class="text-slate-400 text-xs">{{ $transactions->count() }} حركة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                            <th class="px-4 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                            <th class="px-4 py-3 text-right text-slate-400 font-medium">البيان</th>
                            <th class="px-4 py-3 text-center text-slate-400 font-medium">النوع</th>
                            <th class="px-4 py-3 text-center text-slate-400 font-medium">المبلغ</th>
                            <th class="px-4 py-3 text-center text-slate-400 font-medium">طريقة الدفع</th>
                            <th class="px-4 py-3 text-right text-slate-400 font-medium">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            @php
                                $methodLabel = match($t->method ?? '') {
                                    'cash'          => 'نقدي',
                                    'bank_transfer' => 'تحويل بنكي',
                                    'check'         => 'شيك',
                                    default         => $t->method ?? '-',
                                };
                            @endphp
                            <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition {{ $t->type === 'out' ? 'bg-red-900/5' : 'bg-green-900/5' }}">
                                <td class="px-4 py-3 text-slate-300 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-white">{{ $t->description ?? 'دفعة' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($t->type === 'out')
                                        <span class="badge badge-red text-xs">دفعة للمساهم</span>
                                    @else
                                        <span class="badge badge-green text-xs">دفعة من المساهم</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold {{ $t->type === 'out' ? 'text-red-400' : 'text-green-400' }}">
                                    {{ number_format($t->amount, 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-gray text-xs">{{ $methodLabel }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $t->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">لا توجد حركات</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot>
                        <tr class="bg-slate-800/50 border-t-2 border-slate-600 font-bold text-xs">
                            <td colspan="2" class="px-4 py-3 text-white">إجمالي المساهمة</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-center text-red-400">{{ number_format($remaining, 0) }}</td>
                            <td colspan="2" class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    @endif

@endsection


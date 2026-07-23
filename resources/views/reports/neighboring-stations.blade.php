@extends('layouts.app')
@section('title', 'تقرير المحطات المجاورة')
@section('content')

    @include('partials.page-header', [
        'title' => 'تقرير المحطات المجاورة',
        'icon' => 'fa-industry',
    ])

    {{-- Filters --}}
    <div class="card p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">المحطة</label>
                <select name="station_id" class="input-field w-full px-4 py-2">
                    <option value="">جميع المحطات</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date', $fromDate) }}"
                    class="input-field w-full px-4 py-2">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date', $toDate) }}"
                    class="input-field w-full px-4 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg flex-1">
                    <i class="fas fa-filter ml-2"></i>
                    فلترة
                </button>
                <button type="submit" name="export" value="excel" class="btn-accent px-6 py-2 rounded-lg">
                    <i class="fas fa-file-excel ml-2"></i>
                    Excel
                </button>
            </div>
        </form>
    </div>

    @if(request('station_id'))
        {{-- Single Station Detailed Report --}}
        @php
            $station = $stations->firstWhere('id', request('station_id'));
        @endphp

        <div class="card p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-4">{{ $station->name }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                    <div class="text-green-400 text-xs mb-1">إجمالي الوارد</div>
                    <div class="text-2xl font-bold text-white">{{ number_format($totalIncoming, 2) }}</div>
                    <div class="text-xs text-slate-400">جنية</div>
                </div>

                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                    <div class="text-red-400 text-xs mb-1">إجمالي الصادر</div>
                    <div class="text-2xl font-bold text-white">{{ number_format($totalOutgoing, 2) }}</div>
                    <div class="text-xs text-slate-400">جنية</div>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                    <div class="text-blue-400 text-xs mb-1">المدفوع</div>
                    <div class="text-2xl font-bold text-white">{{ number_format($totalPaid, 2) }}</div>
                    <div class="text-xs text-slate-400">جنية</div>
                </div>

                <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4">
                    <div class="text-amber-400 text-xs mb-1">الرصيد الحالي</div>
                    <div class="text-2xl font-bold {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format(abs($balance), 2) }}
                    </div>
                    <div class="text-xs text-slate-400">{{ $balance >= 0 ? 'لصالحنا' : 'علينا' }}</div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">تفاصيل المعاملات</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-slate-700">
                        <tr class="text-slate-400 text-sm">
                            <th class="text-right py-3 px-4">التاريخ</th>
                            <th class="text-right py-3 px-4">النوع</th>
                            <th class="text-right py-3 px-4">الاتجاه</th>
                            <th class="text-right py-3 px-4">الوصف</th>
                            <th class="text-right py-3 px-4">المبلغ</th>
                            <th class="text-right py-3 px-4">المدفوع</th>
                            <th class="text-right py-3 px-4">المتبقي</th>
                            <th class="text-right py-3 px-4">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-300">
                        @forelse ($transactions as $transaction)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/30">
                                <td class="py-3 px-4">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">{{ $transaction->transaction_type_label }}</td>
                                <td class="py-3 px-4">
                                    @if($transaction->direction === 'incoming')
                                        <span class="badge badge-green">{{ $transaction->direction_label }}</span>
                                    @else
                                        <span class="badge badge-red">{{ $transaction->direction_label }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $transaction->description }}</td>
                                <td class="py-3 px-4 font-bold">{{ number_format($transaction->amount, 2) }}</td>
                                <td class="py-3 px-4">{{ number_format($transaction->paid_amount, 2) }}</td>
                                <td class="py-3 px-4">{{ number_format($transaction->getRemainingAmount(), 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($transaction->payment_status === 'paid')
                                        <span class="badge badge-green">{{ $transaction->payment_status_label }}</span>
                                    @elseif($transaction->payment_status === 'partial')
                                        <span class="badge badge-yellow">{{ $transaction->payment_status_label }}</span>
                                    @else
                                        <span class="badge badge-gray">{{ $transaction->payment_status_label }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-500">لا توجد معاملات في الفترة المحددة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- All Stations Summary --}}
        <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">ملخص جميع المحطات</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-slate-700">
                        <tr class="text-slate-400 text-sm">
                            <th class="text-right py-3 px-4">المحطة</th>
                            <th class="text-right py-3 px-4">عدد المعاملات</th>
                            <th class="text-right py-3 px-4">إجمالي الوارد</th>
                            <th class="text-right py-3 px-4">إجمالي الصادر</th>
                            <th class="text-right py-3 px-4">المدفوع</th>
                            <th class="text-right py-3 px-4">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-300">
                        @forelse ($stationsData as $data)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-bold">{{ $data['station']->name }}</td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-blue">{{ $data['transaction_count'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-green-400">{{ number_format($data['total_incoming'], 2) }}</td>
                                <td class="py-3 px-4 text-red-400">{{ number_format($data['total_outgoing'], 2) }}</td>
                                <td class="py-3 px-4">{{ number_format($data['total_paid'], 2) }}</td>
                                <td class="py-3 px-4 font-bold {{ $data['balance'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ number_format(abs($data['balance']), 2) }}
                                    <span class="text-xs text-slate-400">{{ $data['balance'] >= 0 ? 'لنا' : 'علينا' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-500">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-slate-700">
                        <tr class="text-white font-bold">
                            <td class="py-3 px-4" colspan="2">الإجمالي</td>
                            <td class="py-3 px-4 text-green-400">{{ number_format($grandTotalIncoming, 2) }}</td>
                            <td class="py-3 px-4 text-red-400">{{ number_format($grandTotalOutgoing, 2) }}</td>
                            <td class="py-3 px-4">{{ number_format($grandTotalPaid, 2) }}</td>
                            <td class="py-3 px-4 {{ $grandBalance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ number_format(abs($grandBalance), 2) }}
                                <span class="text-xs">{{ $grandBalance >= 0 ? 'لنا' : 'علينا' }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@endsection


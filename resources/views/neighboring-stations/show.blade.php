@extends('layouts.app')
@section('title', 'تفاصيل المحطة')
@section('content')

    @include('partials.page-header', [
        'title' => $neighboringStation->name,
        'icon' => 'fa-industry',
        'actions' => [
            [
                'label' => 'إضافة معاملة',
                'route' => route('neighboring-stations.create-transaction', $neighboringStation),
                'icon' => 'fa-plus',
            ],
            [
                'label' => 'تعديل المحطة',
                'route' => route('neighboring-stations.edit', $neighboringStation),
                'icon' => 'fa-edit',
            ],
        ],
    ])

    {{-- Station Info --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <div class="text-slate-400 text-xs mb-1">الشخص المسؤول</div>
            <div class="text-white font-bold">{{ $neighboringStation->contact_person ?? '-' }}</div>
        </div>
        <div class="card p-4">
            <div class="text-slate-400 text-xs mb-1">الهاتف</div>
            <div class="text-white font-bold">{{ $neighboringStation->phone ?? '-' }}</div>
        </div>
        <div class="card p-4">
            <div class="text-slate-400 text-xs mb-1">إجمالي الوارد</div>
            <div class="text-green-400 font-bold">{{ number_format($neighboringStation->getTotalIncoming(), 2) }} جنية</div>
        </div>
        <div class="card p-4">
            <div class="text-slate-400 text-xs mb-1">إجمالي الصادر</div>
            <div class="text-red-400 font-bold">{{ number_format($neighboringStation->getTotalOutgoing(), 2) }} جنية</div>
        </div>
    </div>

    {{-- Balance Summary --}}
    <div class="card p-6 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">ملخص الحساب</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-slate-400 text-sm mb-1">الرصيد الحالي</div>
                @php $balance = $neighboringStation->getBalance(); @endphp
                <div class="text-2xl font-bold {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ number_format(abs($balance), 2) }} جنية
                    <span class="text-sm text-slate-400">{{ $balance >= 0 ? '(لصالحنا)' : '(علينا)' }}</span>
                </div>
            </div>
            <div>
                <div class="text-slate-400 text-sm mb-1">المدفوع من الوارد</div>
                <div class="text-lg font-bold text-white">{{ number_format($neighboringStation->getTotalPaidIncoming(), 2) }} جنية</div>
            </div>
            <div>
                <div class="text-slate-400 text-sm mb-1">المدفوع من الصادر</div>
                <div class="text-lg font-bold text-white">{{ number_format($neighboringStation->getTotalPaidOutgoing(), 2) }} جنية</div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card p-6">
        <h3 class="text-lg font-bold text-white mb-4">المعاملات</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-slate-700">
                    <tr class="text-slate-400 text-sm">
                        <th class="text-right py-3 px-4">#</th>
                        <th class="text-right py-3 px-4">التاريخ</th>
                        <th class="text-right py-3 px-4">النوع</th>
                        <th class="text-right py-3 px-4">الاتجاه</th>
                        <th class="text-right py-3 px-4">الوصف</th>
                        <th class="text-right py-3 px-4">المبلغ</th>
                        <th class="text-right py-3 px-4">المدفوع</th>
                        <th class="text-right py-3 px-4">حالة الدفع</th>
                        <th class="text-center py-3 px-4">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300">
                    @forelse ($transactions as $transaction)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30">
                            <td class="py-3 px-4">{{ $transaction->id }}</td>
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
                            <td class="py-3 px-4">
                                @if($transaction->payment_status === 'paid')
                                    <span class="badge badge-green">{{ $transaction->payment_status_label }}</span>
                                @elseif($transaction->payment_status === 'partial')
                                    <span class="badge badge-yellow">{{ $transaction->payment_status_label }}</span>
                                @else
                                    <span class="badge badge-gray">{{ $transaction->payment_status_label }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if($transaction->payment_status !== 'paid')
                                        <button onclick="openPaymentModal({{ $transaction->id }}, {{ $transaction->getRemainingAmount() }})"
                                            class="text-green-400 hover:text-green-300" title="تسجيل دفعة">
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('neighboring-stations.edit-transaction', [$neighboringStation, $transaction]) }}"
                                        class="text-amber-400 hover:text-amber-300">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('neighboring-stations.destroy-transaction', [$neighboringStation, $transaction]) }}" 
                                        method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-500">
                                <i class="fas fa-inbox text-3xl mb-3"></i>
                                <p>لا توجد معاملات مسجلة</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    {{-- Payment Modal --}}
    <div id="paymentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="card p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-white mb-4">تسجيل دفعة</h3>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-300 mb-2">المبلغ المتبقي</label>
                    <div id="remainingAmount" class="text-2xl font-bold text-amber-400 mb-4"></div>
                    
                    <label class="block text-sm font-bold text-slate-300 mb-2">مبلغ الدفعة *</label>
                    <input type="number" name="payment_amount" step="0.01" required
                        class="input-field w-full px-4 py-2">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                        <i class="fas fa-save ml-2"></i>
                        حفظ
                    </button>
                    <button type="button" onclick="closePaymentModal()" 
                        class="btn bg-slate-700 hover:bg-slate-600 px-6 py-2 rounded-lg text-white">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openPaymentModal(transactionId, remainingAmount) {
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('remainingAmount').textContent = remainingAmount.toFixed(2) + ' جنية';
            
            // Build the proper route with both parameters
            const baseUrl = '{{ route("neighboring-stations.show", $neighboringStation) }}';
            document.getElementById('paymentForm').action = baseUrl + '/transactions/' + transactionId + '/payment';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
    </script>
    @endpush

@endsection

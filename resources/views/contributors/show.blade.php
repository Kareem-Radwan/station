@extends('layouts.app')
@section('title', $contributor->name)

@section('content')

    @php
        $totalPaid = (float) $contributor->payments()->whereNull('treasury_transaction_id')->sum('amount');
        $shareAmount = (float) $contributor->share_amount + $totalPaid;
        $remaining = max(0, $shareAmount - $totalPaid);
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('contributors.index') }}" class="text-slate-400 hover:text-white text-sm">
                    المساهمون
                </a>

                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>

                <span class="text-white font-bold">
                    {{ $contributor->name }}
                </span>
            </div>

            <span class="badge {{ $contributor->is_active ? 'badge-green' : 'badge-gray' }}">
                {{ $contributor->is_active ? 'نشط' : 'موقف' }}
            </span>
        </div>

        <div class="flex gap-3">
            <button onclick="document.getElementById('add-share-modal').classList.remove('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                زيادة رأس المال
            </button>

            <a href="{{ route('contributor-payments.create') }}?contributor_id={{ $contributor->id }}"
                class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-money-bill-wave"></i>
                تسجيل دفعة
            </a>

            <a href="{{ route('contributors.edit', $contributor) }}"
                class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-edit"></i>
                تعديل
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Contributor Info --}}
        <div class="card p-6">
            <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
                <i class="fas fa-user text-amber-400"></i>
                بيانات المساهم
            </h3>

            <div class="space-y-4 mt-4 text-sm">

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-phone text-slate-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">الهاتف</p>
                        <p class="text-white">{{ $contributor->phone ?: '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-id-card text-slate-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">الرقم القومي</p>
                        <p class="text-white">{{ $contributor->national_id ?: '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-slate-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">العنوان</p>
                        <p class="text-white">{{ $contributor->address ?: '-' }}</p>
                    </div>
                </div>

            </div>

            @if ($contributor->notes)
                <div class="bg-slate-800/50 rounded-lg p-3 text-slate-300 text-sm mt-4">
                    {{ $contributor->notes }}
                </div>
            @endif
        </div>

        {{-- Share Information --}}
        <div class="card p-6">
            <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
                <i class="fas fa-percentage text-blue-400"></i>
                بيانات المساهمة
            </h3>

            <div class="space-y-4 mt-4">

                <div class="text-center">
                    <p class="text-slate-400 text-sm">نسبة المساهمة</p>

                    <p class="text-5xl font-bold text-amber-400 mt-2">
                        {{ number_format($contributor->share_percentage, 2) }}
                    </p>

                    <p class="text-slate-500">%</p>
                </div>

                <div class="border-t border-slate-700 pt-4">
                    <div class="flex justify-between">
                        <span class="text-slate-400">قيمة المساهمة</span>

                        <span class="text-white font-bold">
                            {{ number_format($shareAmount, 2) }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="card p-6">

            <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3">
                <i class="fas fa-chart-line text-green-400"></i>
                الملخص المالي
            </h3>

            <div class="space-y-3 mt-4">

                <div class="flex justify-between">
                    <span class="text-slate-400">قيمة المساهمة</span>

                    <span class="text-white font-bold">
                        {{ number_format($shareAmount, 2) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-slate-400">إجمالي المدفوع</span>

                    <span class="text-green-400 font-bold">
                        {{ number_format($totalPaid, 2) }}
                    </span>
                </div>

                <div class="flex justify-between border-t border-slate-700 pt-3">
                    <span class="text-slate-400">المتبقي</span>

                    <span class="text-red-400 font-bold text-lg">
                        {{ number_format($remaining, 2) }}
                    </span>
                </div>

            </div>

            <div class="mt-5">
                <a href="{{ route('contributor-payments.create') }}?contributor_id={{ $contributor->id }}"
                    class="w-full btn-primary text-white px-4 py-2 rounded-lg text-sm font-bold block text-center">
                    <i class="fas fa-plus-circle"></i>
                    تسجيل دفعة جديدة
                </a>
            </div>

        </div>

    </div>

    {{-- Payments Table --}}
    <div class="card mt-6 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-white font-bold flex items-center gap-2">
                <i class="fas fa-money-check-alt text-slate-400"></i>
                سجل المدفوعات
            </h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        <th class="px-4 py-3 text-right text-slate-400">التاريخ</th>
                        <th class="px-4 py-3 text-right text-slate-400">المبلغ</th>
                        <th class="px-4 py-3 text-right text-slate-400">طريقة الدفع</th>
                        <th class="px-4 py-3 text-right text-slate-400">المرجع</th>
                        <th class="px-4 py-3 text-right text-slate-400">ملاحظات</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-800">

                    @forelse($payments as $payment)
                        <tr class="table-row">
                        
                            <td class="px-4 py-3 text-slate-300">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 {{ $payment->treasury_transaction_id === null ? 'text-red-400' : 'text-green-400' }} font-bold">
                                {{ $payment->treasury_transaction_id === null ? '-' : '+' }}{{ number_format($payment->amount, 2) }}
                            </td>

                            <td class="px-4 py-3 text-slate-300">
                                {{ $payment->payment_method }}
                            </td>

                            <td class="px-4 py-3 text-slate-300">
                                {{ $payment->reference_number ?: '-' }}
                            </td>

                            <td class="px-4 py-3 text-slate-300">
                                {{ $payment->notes ?: '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                لا توجد مدفوعات مسجلة
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($payments->hasPages())
            <div class="p-4 border-t border-slate-700">
                {{ $payments->links() }}
            </div>
        @endif

    </div>

    {{-- Add to Share Amount Modal --}}
    <div id="add-share-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-xl max-w-md w-full p-6 border border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <i class="fas fa-plus-circle text-green-400"></i>
                    زيادة رأس المال
                </h3>
                <button onclick="document.getElementById('add-share-modal').classList.add('hidden')" 
                    class="text-slate-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('contributors.add-to-share', $contributor) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="amount" required min="0.01" 
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                    <input type="date" name="payment_date" value="{{ today()->toDateString() }}" required 
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" 
                        class="input-field w-full px-3 py-2.5 text-sm"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold flex-1">
                        <i class="fas fa-check"></i> تأكيد الإضافة
                    </button>
                    <button type="button" 
                        onclick="document.getElementById('add-share-modal').classList.add('hidden')"
                        class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-lg text-sm flex-1">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

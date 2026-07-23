@extends('layouts.app')
@section('title', 'تفاصيل الجدول الأسبوعي')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-white text-sm">الجداول</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">أسبوع {{ $schedule->week_number }} ({{ $schedule->year }})</span>
        </div>
        <span class="badge {{ $schedule->status==='published'?'badge-green':($schedule->status==='draft'?'badge-yellow':'badge-gray') }}">
            {{ ['draft'=>'مسودة','published'=>'منشور','completed'=>'مكتمل'][$schedule->status]??$schedule->status }}
        </span>
    </div>
    <div class="flex gap-3">
        @if($schedule->status === 'draft')
        <form action="{{ route('schedules.update', $schedule) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="published">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm"><i class="fas fa-play"></i> تفعيل الجدول</button>
        </form>
        <a href="{{ route('schedules.edit',$schedule) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
        @endif
        @if($schedule->status === 'published')
        <form action="{{ route('schedules.update', $schedule) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="completed">
            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded-lg text-sm transition"><i class="fas fa-check-double"></i> إنهاء الأسبوع</button>
        </form>
        @endif
    </div>
</div>

<div class="card p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
        <div><span class="text-slate-400 block mb-1">من</span><span class="text-white font-medium">{{ $schedule->start_date->format('d/m/Y') }}</span></div>
        <div><span class="text-slate-400 block mb-1">إلى</span><span class="text-white font-medium">{{ $schedule->end_date->format('d/m/Y') }}</span></div>
        <div class="md:col-span-2"><span class="text-slate-400 block mb-1">ملاحظات والأهداف</span><span class="text-slate-300">{{ $schedule->notes ?? '-' }}</span></div>
    </div>
</div>

@if($schedule->status === 'published')
<div class="card p-6 mb-6">
    <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2 mb-4">
        <i class="fas fa-calendar-plus text-amber-400"></i> إضافة طلب للجدول الأسبوعي
    </h3>
    <form action="{{ route('schedules.entries.store', $schedule) }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">العميل <span class="text-red-400">*</span></label>
                <select name="customer_id" required class="input-field w-full px-3 py-2.5 text-sm" id="entry_customer_id">
                    <option value="">اختر العميل</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الطلب المرتبط (اختياري)</label>
                <select name="order_id" class="input-field w-full px-3 py-2.5 text-sm" id="entry_order_id">
                    <option value="">غير مرتبط بطلب محدد</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" data-customer-id="{{ $order->customer_id }}" data-location="{{ $order->location }}" data-quantity="{{ $order->quantity_m3 }}" data-date="{{ $order->delivery_date->toDateString() }}" data-time="{{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : '' }}">
                            طلب #{{ $order->id }} - {{ $order->customer->name }} ({{ $order->quantity_m3 }} م³)
                        </option>
                    @endforeach
                </select>
                @error('order_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">موقع الصب / التوصيل <span class="text-red-400">*</span></label>
                <input type="text" name="site_location" required class="input-field w-full px-3 py-2.5 text-sm" id="entry_location">
                @error('site_location')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الكمية المطلوبة (م³) <span class="text-red-400">*</span></label>
                <input type="number" step="0.001" name="quantity_m3" required class="input-field w-full px-3 py-2.5 text-sm" id="entry_quantity">
                @error('quantity_m3')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تاريخ التوصيل <span class="text-red-400">*</span></label>
                <input type="date" name="delivery_date" required class="input-field w-full px-3 py-2.5 text-sm" id="entry_date">
                @error('delivery_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">وقت التوصيل</label>
                <input type="time" name="delivery_time" class="input-field w-full px-3 py-2.5 text-sm" id="entry_time">
                @error('delivery_time')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-3">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات المهندس</label>
                <textarea name="engineer_notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"></textarea>
                @error('engineer_notes')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2 rounded-lg text-sm"><i class="fas fa-plus"></i> إضافة للجدول</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderSelect = document.getElementById('entry_order_id');
        const customerSelect = document.getElementById('entry_customer_id');
        const locationInput = document.getElementById('entry_location');
        const quantityInput = document.getElementById('entry_quantity');
        const dateInput = document.getElementById('entry_date');
        const timeInput = document.getElementById('entry_time');

        if (orderSelect && customerSelect) {
            // Keep a copy of all options in the order dropdown
            const allOrderOptions = Array.from(orderSelect.options);

            customerSelect.addEventListener('change', function() {
                const selectedCustomerId = customerSelect.value;
                
                // Clear order dropdown
                orderSelect.innerHTML = '';
                
                // Re-add options that match the customer or are the default option
                allOrderOptions.forEach(function(option) {
                    const optionCustomerId = option.getAttribute('data-customer-id');
                    if (!option.value || optionCustomerId === selectedCustomerId) {
                        orderSelect.appendChild(option);
                    }
                });
                
                // Reset selected order and other fields since customer changed
                orderSelect.value = '';
                locationInput.value = '';
                quantityInput.value = '';
                dateInput.value = '';
                timeInput.value = '';
            });

            orderSelect.addEventListener('change', function() {
                const selectedOption = orderSelect.options[orderSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const customerId = selectedOption.getAttribute('data-customer-id');
                    const location = selectedOption.getAttribute('data-location');
                    const quantity = selectedOption.getAttribute('data-quantity');
                    const date = selectedOption.getAttribute('data-date');
                    const time = selectedOption.getAttribute('data-time');

                    customerSelect.value = customerId;
                    locationInput.value = location || '';
                    quantityInput.value = quantity || '';
                    dateInput.value = date || '';
                    timeInput.value = time || '';
                }
            });
        }
    });
</script>
@endif

<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
        <h3 class="text-white font-bold">الطلبات المرتبطة بهذا الأسبوع</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['الطلب','العميل','التاريخ والوقت','الكمية','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($schedule->entries as $entry)
                <tr class="table-row">
                    <td class="px-4 py-3">
                        @if($entry->order_id)
                            <a href="{{ route('orders.show', $entry->order_id) }}" class="text-blue-400">#{{ $entry->order_id }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-white">{{ $entry->customer->name }}</td>
                    <td class="px-4 py-3 text-slate-300">
                        {{ $entry->delivery_date->format('d/m/Y') }}
                        @if($entry->delivery_time)
                            <span class="text-amber-400">{{ \Carbon\Carbon::parse($entry->delivery_time)->format('H:i') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-bold">{{ $entry->quantity_m3 }} م³</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $entry->status === 'completed' ? 'badge-green' : ($entry->status === 'cancelled' ? 'badge-red' : 'badge-yellow') }}">
                            {{ $entry->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2 items-center">
                            @if($entry->order_id)
                                <a href="{{ route('orders.show', $entry->order_id) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i> التفاصيل</a>
                            @endif
                            @if($schedule->status === 'published')
                                <form action="{{ route('schedules.entries.destroy', $entry) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإدخال؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs px-2 py-1 border border-red-400/30 rounded"><i class="fas fa-trash"></i> حذف</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">لا توجد طلبات مجدولة في هذا الأسبوع</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

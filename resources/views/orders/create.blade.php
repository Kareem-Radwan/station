@extends('layouts.app')
@section('title', 'طلب جديد')
@section('content')

    @include('partials.page-header', ['title' => 'إنشاء طلب جديد', 'icon' => 'fa-plus-circle'])

    <div class="max-w-4xl">
        <form action="{{ route('orders.store') }}" method="POST" class="space-y-6" id="orderForm">
            @csrf

            {{-- Customer & Type --}}
            <div class="card p-6 space-y-5">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                    <i class="fas fa-user text-amber-400 text-sm"></i> العميل والنوع
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">العميل <span class="text-red-400">*</span></label>
                        <select name="customer_id" id="customerSelect" required
                            class="input-field w-full px-3 py-2.5 text-sm" onchange="loadCustomerInfo()">
                            <option value="">اختر العميل</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" data-type="{{ $c->concrete_type }}"
                                    data-cement="{{ $c->cement_balance }}"
                                    {{ request('customer_id') == $c->id || old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} @if ($c->isOperational())
                                        (رصيد: {{ number_format($c->cement_balance, 1) }} طن)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">نوع الخرسانة <span
                                class="text-red-400">*</span></label>
                        <select name="concrete_type" id="concreteTypeSelect" required
                            class="input-field w-full px-3 py-2.5 text-sm" onchange="onTypeChange()">
                            <option value="">اختر</option>
                            <option value="operational" {{ old('concrete_type') == 'operational' ? 'selected' : '' }}>
                                تشغيلية
                            </option>
                            <option value="complete" {{ old('concrete_type') == 'complete' ? 'selected' : '' }}>متكامل
                            </option>
                        </select>
                    </div>

                    {{-- Customer Info Bar --}}
                    <div id="customerInfo"
                        class="md:col-span-2 hidden bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 flex items-center gap-4 text-sm">
                        <div class="flex-1">
                            <span class="text-slate-400">رصيد الاسمنت: </span>
                            <span id="cementBalanceDisplay" class="text-amber-400 font-bold"></span>
                            <span class="text-slate-400"> طن</span>
                        </div>
                        <div id="cementEstimate" class="text-slate-400 text-xs hidden">
                            الاسمنت المطلوب: <span id="cementRequired" class="text-white font-bold"></span> طن
                        </div>
                    </div>

                    {{-- Mix (for ALL order types) --}}
                    <div id="mixField" class="md:col-span-2 {{ old('concrete_type') ? '' : 'hidden' }}">
                        <label class="block text-slate-400 text-sm mb-1.5">خلطة الخرسانة <span
                                class="text-red-400">*</span></label>
                        <select name="concrete_mix_id" id="mixSelect" class="input-field w-full px-3 py-2.5 text-sm"
                            onchange="onMixOrQtyChange()">
                            <option value="">اختر الخلطة</option>
                            @foreach ($concreteMixes as $mix)
                                <option value="{{ $mix->id }}" data-cement="{{ $mix->cement_per_m3 }}"
                                    {{ old('concrete_mix_id') == $mix->id ? 'selected' : '' }}>
                                    {{ $mix->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('concrete_mix_id')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Quantity & Delivery --}}
            <div class="card p-6 space-y-5">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                    <i class="fas fa-truck text-amber-400 text-sm"></i> التسليم
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">الكمية (م³) <span
                                class="text-red-400">*</span></label>
                        <input type="number" step="0.001" name="quantity_m3" id="quantityInput"
                            value="{{ old('quantity_m3') }}" required min="0.001"
                            class="input-field w-full px-3 py-2.5 text-sm" onchange="onMixOrQtyChange()"
                            oninput="onMixOrQtyChange()">
                        @error('quantity_m3')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">تاريخ التسليم <span
                                class="text-red-400">*</span></label>
                        <input type="date" name="delivery_date"
                            value="{{ old('delivery_date', today()->toDateString()) }}" required
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">وقت التسليم</label>
                        <input type="time" name="delivery_time" value="{{ old('delivery_time') }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-slate-400 text-sm mb-1.5">الموقع / العنوان</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="موقع المشروع"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                        <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Material Costs Panel (shown after mix + quantity selected) --}}
            <div id="materialCostsPanel" class="hidden card p-6 space-y-4">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                    <i class="fas fa-layer-group text-amber-400 text-sm"></i> المواد المستخدمة والتكلفة (للعرض فقط)
                </h3>
                <div id="materialCostsBody">
                    <div class="text-slate-400 text-sm text-center py-4">
                        <i class="fas fa-spinner fa-spin ml-2"></i> جارٍ الحساب...
                    </div>
                </div>
                <div id="materialCostsTotal"
                    class="hidden flex justify-between items-center bg-amber-900/20 border border-amber-500/30 rounded-xl px-4 py-3">
                    <span class="text-slate-300 font-semibold">إجمالي تكلفة المواد:</span>
                    <span id="materialGrandTotal" class="text-amber-400 font-bold text-lg"></span>
                </div>
                <p class="text-slate-500 text-xs">
                    <i class="fas fa-info-circle ml-1"></i>
                    يمكنك تعديل سعر الوحدة لكل مادة لتسجيل الأسعار القديمة. سيتم خصم الكميات من المخزون والتكلفة المحسوبة من الخزينة عند التسليم.
                </p>
            </div>

            {{-- Order Expenses --}}
            <div class="card p-6 space-y-4">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                    <i class="fas fa-receipt text-amber-400 text-sm"></i> مصروفات إضافية للطلب
                </h3>
                <div id="expensesContainer" class="space-y-3">
                    <!-- Expenses will be added here dynamically -->
                </div>
                <button type="button" onclick="addExpense()"
                    class="text-amber-400 hover:text-amber-300 text-sm flex items-center gap-2 transition">
                    <i class="fas fa-plus-circle"></i> إضافة مصروف
                </button>
                <p class="text-slate-500 text-xs">
                    <i class="fas fa-info-circle ml-1"></i>
                    المصروفات الإضافية (مثل: نقل، عمولة، إلخ) سيتم خصمها من الخزينة عند تسليم الطلب.
                </p>
            </div>

            {{-- Payment (complete type only) --}}
            <div id="paymentSection" class="card p-6 space-y-5">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                    <i class="fas fa-credit-card text-amber-400 text-sm"></i> السعر والدفع
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">سعر المتر (م³)</label>
                        <input type="number" step="0.01" name="unit_price" id="unitPrice"
                            value="{{ old('unit_price') }}" class="input-field w-full px-3 py-2.5 text-sm"
                            onchange="calcTotal()" oninput="calcTotal()">
                        <p class="text-slate-500 text-xs mt-1">هذا المبلغ يضاف على تكلفة المواد لكل متر مكعب</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">إجمالي سعر الطلب</label>
                        <input type="number" step="0.01" name="total_amount" id="totalAmount"
                            value="{{ old('total_amount') }}"
                            class="input-field w-full px-3 py-2.5 text-sm bg-slate-800" readonly>
                        <p class="text-slate-500 text-xs mt-1">يتم حسابه تلقائياً</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع</label>
                        <select name="payment_type" id="paymentType" class="input-field w-full px-3 py-2.5 text-sm"
                            onchange="togglePaymentFields()">
                            <option value="">اختر</option>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
                            <option value="mixed" {{ old('payment_type') == 'mixed' ? 'selected' : '' }}>مختلط</option>
                        </select>
                    </div>
                    <div id="cashField" class="hidden">
                        <label class="block text-slate-400 text-sm mb-1.5">المبلغ النقدي</label>
                        <input type="number" step="0.01" name="cash_amount" value="{{ old('cash_amount') }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div id="creditField" class="hidden">
                        <label class="block text-slate-400 text-sm mb-1.5">المبلغ الآجل</label>
                        <input type="number" step="0.01" name="credit_amount" value="{{ old('credit_amount') }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div id="dueDateField" class="hidden">
                        <label class="block text-slate-400 text-sm mb-1.5">تاريخ الاستحقاق</label>
                        <input type="date" name="credit_due_date" value="{{ old('credit_due_date') }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-save"></i> إنشاء الطلب
                </button>
                <a href="{{ route('orders.index') }}"
                    class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const customers = @json($customers->keyBy('id')->map(fn($c) => ['type' => $c->concrete_type, 'balance' => (float) $c->cement_balance]));
            const MIX_COSTS_URL = '{{ route('orders.mix-costs') }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';

            let costsDebounce = null;
            let materialGrandTotal = 0;
            let expenseIndex = 0;

            function loadCustomerInfo() {
                const id = document.getElementById('customerSelect').value;
                const box = document.getElementById('customerInfo');
                if (!id || !customers[id]) {
                    box.classList.add('hidden');
                    return;
                }
                document.getElementById('cementBalanceDisplay').textContent = new Intl.NumberFormat('ar').format(customers[id]
                    .balance);
                box.classList.remove('hidden');
                // Auto-set type
                const typeSelect = document.getElementById('concreteTypeSelect');
                typeSelect.value = customers[id].type;
                onTypeChange();
            }

            function onTypeChange() {
                const type = document.getElementById('concreteTypeSelect').value;

                // Mix field shown for both types
                document.getElementById('mixField').classList.toggle('hidden', !type);

                calcCement();
                onMixOrQtyChange();
            }

            function calcCement() {
                const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
                const mixSel = document.getElementById('mixSelect');
                const cement = mixSel?.selectedOptions[0]?.dataset?.cement || 0;
                const required = qty * parseFloat(cement);
                const requiredTons = required / 1000;

                if (required > 0) {
                    document.getElementById('cementRequired').textContent = requiredTons.toFixed(3);
                    document.getElementById('cementEstimate').classList.remove('hidden');
                } else {
                    document.getElementById('cementEstimate').classList.add('hidden');
                }
                calcTotal();
            }

            function onMixOrQtyChange() {
                calcCement();
                const mixId = document.getElementById('mixSelect').value;
                const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
                const type = document.getElementById('concreteTypeSelect').value;

                if (!mixId || !qty || !type) {
                    document.getElementById('materialCostsPanel').classList.add('hidden');
                    return;
                }

                // Debounce AJAX call
                clearTimeout(costsDebounce);
                costsDebounce = setTimeout(() => fetchMaterialCosts(mixId, qty, type), 400);
            }

            function fetchMaterialCosts(mixId, qty, type) {
                const panel = document.getElementById('materialCostsPanel');
                panel.classList.remove('hidden');
                document.getElementById('materialCostsBody').innerHTML =
                    '<div class="text-slate-400 text-sm text-center py-4"><i class="fas fa-spinner fa-spin ml-2"></i> جارٍ الحساب...</div>';
                document.getElementById('materialCostsTotal').classList.add('hidden');

                fetch(MIX_COSTS_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            concrete_mix_id: mixId,
                            quantity_m3: qty,
                            concrete_type: type,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.materials && data.materials.length > 0) {
                            renderMaterialTable(data.materials, data.grand_total);
                        } else {
                            document.getElementById('materialCostsBody').innerHTML =
                                '<p class="text-slate-500 text-sm text-center py-2">لا توجد مواد للعرض.</p>';
                        }
                    })
                    .catch(() => {
                        document.getElementById('materialCostsBody').innerHTML =
                            '<p class="text-red-400 text-sm text-center py-2">حدث خطأ في تحميل البيانات.</p>';
                    });
            }

            function renderMaterialTable(materials, grandTotal) {
                materialGrandTotal = parseFloat(grandTotal) || 0;

                const fmt = (n, d = 3) => new Intl.NumberFormat('ar', {
                    minimumFractionDigits: d,
                    maximumFractionDigits: d
                }).format(n);
                const fmtC = (n) => new Intl.NumberFormat('ar', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(n);

                let rows = materials.map((m, idx) => {
                    const lowStock = m.in_stock < m.quantity;
                    return `<tr class="${lowStock ? 'bg-red-900/10' : ''}" data-material-index="${idx}" data-material-name="${m.name}">
            <td class="py-2 px-3 text-white font-medium">${m.name_ar}</td>
            <td class="py-2 px-3 text-slate-300">${fmt(m.quantity)} ${m.unit}</td>
            <td class="py-2 px-3">
                <input type="number" 
                    step="0.01" 
                    min="0"
                    class="input-field w-full px-2 py-1 text-sm material-price" 
                    data-material-index="${idx}"
                    data-material-name="${m.name}"
                    data-quantity="${m.quantity}"
                    placeholder="${fmtC(m.price_per_unit)}"
                    oninput="updateMaterialTotal(${idx})">
                <input type="hidden" name="material_prices[${m.name}]" class="material-price-hidden" value="${m.price_per_unit}">
                <span class="text-slate-500 text-xs block mt-0.5">السعر الحالي: ${fmtC(m.price_per_unit)} جنية</span>
            </td>
            <td class="py-2 px-3 font-semibold text-amber-400 material-total" data-material-index="${idx}">
                ${fmtC(m.total)} جنية
            </td>
            <td class="py-2 px-3 text-xs ${lowStock ? 'text-red-400 font-bold' : 'text-slate-500'}">
                ${lowStock
                    ? '<i class="fas fa-exclamation-triangle ml-1"></i>مخزون غير كافٍ (' + fmt(m.in_stock) + ')'
                    : '<i class="fas fa-check-circle ml-1 text-green-500"></i>متوفر (' + fmt(m.in_stock) + ')'}
            </td>
        </tr>`;
                }).join('');

                document.getElementById('materialCostsBody').innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400">
                        <th class="py-2 px-3 text-right font-medium">المادة</th>
                        <th class="py-2 px-3 text-right font-medium">الكمية</th>
                        <th class="py-2 px-3 text-right font-medium">سعر الوحدة</th>
                        <th class="py-2 px-3 text-right font-medium">الإجمالي</th>
                        <th class="py-2 px-3 text-right font-medium">المخزون</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">${rows}</tbody>
            </table>
        </div>`;

                document.getElementById('materialGrandTotal').textContent = fmtC(grandTotal) + ' جنية';
                document.getElementById('materialCostsTotal').classList.remove('hidden');

                calcTotal();
            }

            function updateMaterialTotal(index) {
                const row = document.querySelector(`tr[data-material-index="${index}"]`);
                const priceInput = row.querySelector('.material-price');
                const totalCell = row.querySelector('.material-total');
                const hiddenInput = row.querySelector('.material-price-hidden');
                const materialName = row.dataset.materialName;
                
                const price = parseFloat(priceInput.value) || 0;
                const quantity = parseFloat(priceInput.dataset.quantity) || 0;
                const total = price * quantity;
                
                const fmtC = (n) => new Intl.NumberFormat('ar', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(n);
                
                totalCell.textContent = fmtC(total) + ' جنية';
                hiddenInput.value = price;
                hiddenInput.name = `material_prices[${materialName}]`;
                
                // Recalculate grand total
                recalculateMaterialGrandTotal();
            }

            function recalculateMaterialGrandTotal() {
                let grandTotal = 0;
                document.querySelectorAll('.material-price').forEach(input => {
                    const price = parseFloat(input.value) || 0;
                    const quantity = parseFloat(input.dataset.quantity) || 0;
                    grandTotal += price * quantity;
                });
                
                materialGrandTotal = grandTotal;
                
                const fmtC = (n) => new Intl.NumberFormat('ar', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(n);
                
                document.getElementById('materialGrandTotal').textContent = fmtC(grandTotal) + ' جنية';
            }

            function calcTotal() {
                const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
                const markupPrice = parseFloat(document.getElementById('unitPrice')?.value) || 0;
                
                // Total = (markup per m³ × quantity) + expenses
                let total = markupPrice * qty;
                
                // Add all expenses
                const expenseInputs = document.querySelectorAll('.expense-row input[name*="[amount]"]');
                expenseInputs.forEach(input => {
                    const amount = parseFloat(input.value) || 0;
                    total += amount;
                });

                document.getElementById('totalAmount').value = total.toFixed(2);
            }

            function addExpense() {
                const container = document.getElementById('expensesContainer');
                const html = `
                    <div class="expense-row grid grid-cols-1 md:grid-cols-12 gap-3 p-3 bg-slate-800/30 rounded-lg border border-slate-700" data-index="${expenseIndex}">
                        <div class="md:col-span-4">
                            <label class="block text-slate-400 text-xs mb-1">اسم المصروف</label>
                            <input type="text" name="expenses[${expenseIndex}][name]" required
                                class="input-field w-full px-2 py-1.5 text-sm" placeholder="مثال: نقل، عمولة">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-slate-400 text-xs mb-1">المبلغ</label>
                            <input type="number" step="0.01" name="expenses[${expenseIndex}][amount]" required min="0"
                                class="input-field w-full px-2 py-1.5 text-sm" oninput="calcTotal()">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-slate-400 text-xs mb-1">ملاحظات</label>
                            <input type="text" name="expenses[${expenseIndex}][notes]"
                                class="input-field w-full px-2 py-1.5 text-sm">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" onclick="removeExpense(${expenseIndex})"
                                class="w-full px-2 py-1.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg text-sm transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
                expenseIndex++;
            }

            function removeExpense(index) {
                const row = document.querySelector(`.expense-row[data-index="${index}"]`);
                if (row) row.remove();
            }

            function togglePaymentFields() {
                const type = document.getElementById('paymentType').value;
                document.getElementById('cashField').classList.toggle('hidden', !['cash', 'mixed'].includes(type));
                document.getElementById('creditField').classList.toggle('hidden', !['credit', 'mixed'].includes(type));
                document.getElementById('dueDateField').classList.toggle('hidden', !['credit', 'mixed'].includes(type));
            }

            // Init on load
            loadCustomerInfo();
        </script>
    @endpush
@endsection

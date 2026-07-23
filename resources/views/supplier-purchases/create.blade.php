@extends('layouts.app')
@section('title', 'فاتورة مشتريات جديدة')
@section('content')

    @include('partials.page-header', ['title' => 'فاتورة مشتريات جديدة', 'icon' => 'fa-file-invoice'])

    <form action="{{ route('supplier-purchases.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
        id="purchaseForm">
        @csrf

        <div class="card p-6 space-y-4">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3">بيانات الفاتورة</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المورد <span class="text-red-400">*</span></label>
                    <select name="supplier_id" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر المورد</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}"
                                {{ old('supplier_id', request('supplier_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم الفاتورة</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="اختياري">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الشراء <span
                            class="text-red-400">*</span></label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', today()->toDateString()) }}"
                        required class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_type" id="payType" required class="input-field w-full px-3 py-2.5 text-sm"
                        onchange="togglePaymentFields()">
                        <option value="">اختر</option>
                        <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>نقدي</option>
                        <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
                        <option value="mixed" {{ old('payment_type') == 'mixed' ? 'selected' : '' }}>مختلط</option>
                    </select>
                </div>
                <div id="cashAmtField" class="hidden">
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ النقدي</label>
                    <input type="number" step="0.01" name="cash_amount" value="{{ old('cash_amount') }}"
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div id="creditAmtField" class="hidden">
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ الآجل</label>
                    <input type="number" step="0.01" name="credit_amount" value="{{ old('credit_amount') }}"
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div id="dueDateField" class="hidden">
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الاستحقاق</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
                {{-- Optional invoice image --}}
                <div class="md:col-span-3">
                    <label class="block text-slate-400 text-sm mb-1.5">
                        <i class="fas fa-camera text-amber-400 ml-1"></i>
                        صورة الفاتورة <span class="text-slate-500 text-xs">(اختياري — JPG, PNG, WEBP · حد أقصى 5MB)</span>
                    </label>
                    <label class="block cursor-pointer">
                        <div id="create-drop-zone"
                            class="border-2 border-dashed border-slate-600 hover:border-amber-500 rounded-xl p-5 text-center transition-all duration-200 group">
                            <i
                                class="fas fa-cloud-upload-alt text-2xl text-slate-500 group-hover:text-amber-400 transition mb-1"></i>
                            <p class="text-sm text-slate-400 group-hover:text-slate-200 transition" id="create-drop-label">
                                اسحب صورة الفاتورة هنا أو اضغط للاختيار
                            </p>
                        </div>
                        <input type="file" name="invoice_image" id="create-invoice-image"
                            accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden"
                            onchange="previewCreateImage(this)">
                    </label>
                    <div id="create-preview-container"
                        class="hidden mt-2 rounded-lg overflow-hidden border border-slate-700 max-h-48">
                        <img id="create-preview-img" src="" alt="معاينة"
                            class="w-full object-contain max-h-48 bg-slate-900">
                    </div>
                    @error('invoice_image')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold flex items-center gap-2"><i
                        class="fas fa-list text-amber-400 text-sm"></i> بنود الفاتورة</h3>
                <button type="button" onclick="addItem()" class="btn-primary text-white px-3 py-1.5 rounded-lg text-xs"><i
                        class="fas fa-plus"></i> إضافة بند</button>
            </div>
            <div id="itemsContainer" class="space-y-3">
                <div class="item-row grid grid-cols-12 gap-2 items-end bg-slate-800/30 rounded-xl p-3">
                    <div class="col-span-4">
                        <label class="text-slate-400 text-xs mb-1 block">المادة <span
                                class="text-red-400">*</span></label>
                        <select name="items[0][inventory_item_id]" required
                            class="input-field w-full px-2 py-2 text-sm item-select" onchange="onItemChange(this)">
                            <option value="">اختر المادة</option>
                            @foreach ($inventoryItems as $item)
                                <option value="{{ $item->id }}" data-unit="{{ $item->unit }}"
                                    data-name="{{ $item->name_ar }}">
                                    {{ $item->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="items[0][description]" class="item-description">
                    </div>
                    <div class="col-span-2">
                        <label class="text-slate-400 text-xs mb-1 block">الكمية</label>
                        <input type="number" step="0.001" name="items[0][quantity]" min="0.001" placeholder="1"
                            class="input-field w-full px-2 py-2 text-sm qty-input" oninput="calcRow(this)">
                    </div>
                    <div class="col-span-2">
                        <label class="text-slate-400 text-xs mb-1 block">الوحدة</label>
                        <input type="text" name="items[0][unit]" required readonly
                            class="input-field w-full px-2 py-2 text-sm bg-slate-800/50 text-slate-400 item-unit">
                    </div>
                    <div class="col-span-2">
                        <label class="text-slate-400 text-xs mb-1 block">سعر الوحدة</label>
                        <input type="number" step="0.01" name="items[0][unit_price]" placeholder="0"
                            class="input-field w-full px-2 py-2 text-sm price-input" oninput="calcRow(this)">
                    </div>
                    <div class="col-span-1">
                        <label class="text-slate-400 text-xs mb-1 block">الإجمالي</label>
                        <p class="row-total text-amber-400 font-bold text-sm py-2">0</p>
                    </div>
                    <div class="col-span-1 flex items-end pb-1">
                        <button type="button" onclick="removeItem(this)"
                            class="text-red-400 hover:text-red-300 text-lg"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <div class="text-sm"><span class="text-slate-400">الإجمالي الكلي: </span><span id="grandTotal"
                        class="text-amber-400 font-bold text-xl">0</span> جنية</div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i
                    class="fas fa-save"></i> حفظ الفاتورة</button>
            <a href="{{ route('supplier-purchases.index') }}"
                class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>

    @push('scripts')
        <script>
            let itemIdx = 1;
            const inventoryItemsOptions = `
    <option value="">اختر المادة</option>
    @foreach ($inventoryItems as $item)
        <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-name="{{ $item->name_ar }}">
            {{ $item->name_ar }}
        </option>
    @endforeach
`;

            function addItem() {
                const i = itemIdx++;
                const html = `<div class="item-row grid grid-cols-12 gap-2 items-end bg-slate-800/30 rounded-xl p-3">
        <div class="col-span-4">
            <label class="text-slate-400 text-xs mb-1 block">المادة <span class="text-red-400">*</span></label>
            <select name="items[${i}][inventory_item_id]" required class="input-field w-full px-2 py-2 text-sm item-select" onchange="onItemChange(this)">
                ${inventoryItemsOptions}
            </select>
            <input type="hidden" name="items[${i}][description]" class="item-description">
        </div>
        <div class="col-span-2">
            <label class="text-slate-400 text-xs mb-1 block">الكمية</label>
            <input type="number" step="0.001" name="items[${i}][quantity]" min="0.001" value="1" class="input-field w-full px-2 py-2 text-sm qty-input" oninput="calcRow(this)">
        </div>
        <div class="col-span-2">
            <label class="text-slate-400 text-xs mb-1 block">الوحدة</label>
            <input type="text" name="items[${i}][unit]" required readonly class="input-field w-full px-2 py-2 text-sm bg-slate-800/50 text-slate-400 item-unit">
        </div>
        <div class="col-span-2">
            <label class="text-slate-400 text-xs mb-1 block">سعر الوحدة</label>
            <input type="number" step="0.01" name="items[${i}][unit_price]" value="0" class="input-field w-full px-2 py-2 text-sm price-input" oninput="calcRow(this)">
        </div>
        <div class="col-span-1">
            <label class="text-slate-400 text-xs mb-1 block">الإجمالي</label>
            <p class="row-total text-amber-400 font-bold text-sm py-2">0</p>
        </div>
        <div class="col-span-1 flex items-end pb-1">
            <button type="button" onclick="removeItem(this)" class="text-red-400 hover:text-red-300 text-lg"><i class="fas fa-trash-alt"></i></button>
        </div>
    </div>`;
                document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
            }

            function removeItem(btn) {
                btn.closest('.item-row').remove();
                updateGrandTotal();
            }

            function onItemChange(selectEl) {
                const row = selectEl.closest('.item-row');
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const descInput = row.querySelector('.item-description');
                const unitInput = row.querySelector('.item-unit');

                if (selectedOption && selectedOption.value) {
                    const unit = selectedOption.getAttribute('data-unit');
                    const name = selectedOption.getAttribute('data-name');
                    descInput.value = name;
                    unitInput.value = unit;
                } else {
                    descInput.value = '';
                    unitInput.value = '';
                }
            }

            function calcRow(el) {
                const row = el.closest('.item-row');
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                row.querySelector('.row-total').textContent = (qty * price).toLocaleString('ar-SA', {
                    maximumFractionDigits: 2
                });
                updateGrandTotal();
            }

            function updateGrandTotal() {
                let total = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
                    const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
                    total += qty * price;
                });
                document.getElementById('grandTotal').textContent = total.toLocaleString('ar-SA', {
                    maximumFractionDigits: 2
                });
            }

            function togglePaymentFields() {
                const type = document.getElementById('payType').value;
                document.getElementById('cashAmtField').classList.toggle('hidden', !['cash', 'mixed'].includes(type));
                document.getElementById('creditAmtField').classList.toggle('hidden', !['credit', 'mixed'].includes(type));
                document.getElementById('dueDateField').classList.toggle('hidden', !['credit', 'mixed'].includes(type));
            }

            function previewCreateImage(input) {
                const file = input.files[0];
                if (!file) return;

                document.getElementById('create-drop-label').textContent = file.name;

                const previewContainer = document.getElementById('create-preview-container');

                if (file.type === 'application/pdf') {

                    previewContainer.innerHTML = `
                        <div class="p-6 text-center bg-slate-900">
                            <i class="fas fa-file-pdf text-red-500 text-6xl mb-3"></i>
                            <p class="text-white font-semibold">${file.name}</p>
                            <p class="text-slate-400 text-sm">
                                PDF جاهز للرفع
                            </p>
                        </div>
                    `;

                    previewContainer.classList.remove('hidden');
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img
                        src="${e.target.result}"
                        class="w-full object-contain max-h-48 bg-slate-900">
                    `;

                    previewContainer.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            } 
            
            // Drag-and-drop on create form
            document.addEventListener('DOMContentLoaded', () => {
                const dz = document.getElementById('create-drop-zone');
                if (!dz) return;
                ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => {
                    e.preventDefault();
                    dz.classList.add('border-amber-500', 'bg-amber-500/5');
                }));
                ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => {
                    e.preventDefault();
                    dz.classList.remove('border-amber-500', 'bg-amber-500/5');
                }));
                dz.addEventListener('drop', e => {
                    e.preventDefault();
                    const file = e.dataTransfer.files[0];
                    if (file) {
                        const input = document.getElementById('create-invoice-image');
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        previewCreateImage(input);
                    }
                });
            });
        </script>
    @endpush
@endsection

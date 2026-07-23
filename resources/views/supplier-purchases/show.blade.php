@extends('layouts.app')
@section('title', 'فاتورة مشتريات #' . $supplierPurchase->id)
@section('content')

    {{-- ── Top Bar ─────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('supplier-purchases.index') }}"
                    class="text-slate-400 hover:text-white text-sm">المشتريات</a>
                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
                <span class="text-white font-bold">فاتورة #{{ $supplierPurchase->id }}</span>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <span
                    class="badge {{ $supplierPurchase->status === 'paid' ? 'badge-green' : ($supplierPurchase->status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                    {{ ['pending' => 'معلق', 'partial' => 'جزئي', 'paid' => 'مسدد'][$supplierPurchase->status] ?? $supplierPurchase->status }}
                </span>
                @if ($supplierPurchase->invoice_image_path)
                    <span class="badge badge-blue">
                        <i class="fas fa-image text-xs ml-1"></i> صورة الفاتورة مرفقة
                    </span>
                @endif
            </div>
        </div>
        <div class="flex gap-2 flex-wrap justify-end">
            <a href="{{ route('supplier-payments.create') }}?supplier_id={{ $supplierPurchase->supplier_id }}&purchase_id={{ $supplierPurchase->id }}"
                class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-hand-holding-usd"></i> تسجيل دفعة
            </a>
        </div>
    </div>

    {{-- ── Info Cards Row ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Purchase Details --}}
        <div class="card p-6 space-y-3 text-sm">
            <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i
                    class="fas fa-truck text-amber-400 ml-2"></i>بيانات الفاتورة</h3>
            <div class="flex justify-between"><span class="text-slate-400">المورد</span><span
                    class="text-white font-medium">{{ $supplierPurchase->supplier->name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">رقم الفاتورة</span><span
                    class="text-white">{{ $supplierPurchase->invoice_number ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">تاريخ الشراء</span><span
                    class="text-white">{{ $supplierPurchase->purchase_date->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">نوع الدفع</span><span
                    class="text-white">{{ ['cash' => 'نقدي', 'credit' => 'آجل', 'mixed' => 'مختلط'][$supplierPurchase->payment_type] ?? '-' }}</span>
            </div>
            @if ($supplierPurchase->due_date)
                <div class="flex justify-between"><span class="text-slate-400">تاريخ الاستحقاق</span><span
                        class="text-amber-400">{{ $supplierPurchase->due_date->format('d/m/Y') }}</span></div>
            @endif
        </div>

        {{-- Financial Summary --}}
        <div class="card p-6 space-y-3 text-sm">
            <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i
                    class="fas fa-coins text-blue-400 ml-2"></i>الملخص المالي</h3>
            <div class="flex justify-between"><span class="text-slate-400">الإجمالي</span><span
                    class="text-white font-bold text-lg">{{ number_format($supplierPurchase->total_amount, 0) }}</span>
            </div>
            <div class="flex justify-between"><span class="text-slate-400">نقدي</span><span
                    class="text-green-400">{{ number_format($supplierPurchase->cash_amount, 0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">آجل</span><span
                    class="text-red-400">{{ number_format($supplierPurchase->credit_amount, 0) }}</span></div>
            <div class="flex justify-between border-t border-slate-700 pt-2"><span
                    class="text-slate-400">المدفوعات</span><span
                    class="text-green-400 font-bold">{{ number_format($supplierPurchase->payments->sum('amount'), 0) }}</span>
            </div>
        </div>

        {{-- Notes + Status Update --}}
        <div class="card p-6 text-sm">
            <h3 class="text-white font-bold border-b border-slate-700 pb-3 mb-3"><i
                    class="fas fa-sticky-note text-slate-400 ml-2"></i>ملاحظات</h3>
            <p class="text-slate-300">{{ $supplierPurchase->notes ?? 'لا توجد ملاحظات' }}</p>
            <form action="{{ route('supplier-purchases.update', $supplierPurchase) }}" method="POST"
                class="mt-4 space-y-3">
                @csrf @method('PUT')
                <select name="status" class="input-field w-full px-3 py-2 text-sm">
                    @foreach (['pending' => 'معلق', 'partial' => 'دفع جزئي', 'paid' => 'مسدد بالكامل'] as $v => $l)
                        <option value="{{ $v }}" {{ $supplierPurchase->status == $v ? 'selected' : '' }}>
                            {{ $l }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full btn-primary text-white px-4 py-2 rounded-lg text-sm">تحديث
                    الحالة</button>
            </form>
        </div>
    </div>

    @php
        $invoiceUrl = $supplierPurchase->invoice_image_path
            ? Storage::url($supplierPurchase->invoice_image_path)
            : null;

        $extension = $supplierPurchase->invoice_image_path
            ? strtolower(pathinfo($supplierPurchase->invoice_image_path, PATHINFO_EXTENSION))
            : null;

        $isPdf = $extension === 'pdf';
    @endphp

    {{-- ── Invoice Image Section ────────────────────────────────────────────────── --}}
    <div class="card p-6 mb-6">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 mb-5 flex items-center gap-2">
            <i class="fas fa-file-image text-amber-400"></i> صورة الفاتورة
        </h3>

        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- Current image or placeholder --}}
            <div class="flex-1">
                @if ($supplierPurchase->invoice_image_path)

                    @if ($isPdf)
                        <div class="rounded-xl border border-slate-700 bg-slate-900 p-8 text-center">

                            <i class="fas fa-file-pdf text-red-500 text-7xl mb-4"></i>

                            <h3 class="text-white font-bold mb-2">
                                ملف PDF
                            </h3>

                            <p class="text-slate-400 text-sm mb-6">
                                تم إرفاق فاتورة بصيغة PDF
                            </p>

                            <div class="flex justify-center gap-3">

                                <a href="{{ $invoiceUrl }}" target="_blank" class="btn-primary px-5 py-2 rounded-lg">
                                    <i class="fas fa-eye"></i>
                                    عرض PDF
                                </a>

                                <a href="{{ $invoiceUrl }}" download class="btn-accent px-5 py-2 rounded-lg">
                                    <i class="fas fa-download"></i>
                                    تحميل
                                </a>

                            </div>

                            <iframe src="{{ $invoiceUrl }}"
                                class="w-full h-[700px] mt-6 rounded-lg border border-slate-700 bg-white">
                            </iframe>

                        </div>
                    @else
                        <div class="rounded-xl overflow-hidden border border-slate-700 bg-slate-900">

                            <img src="{{ $invoiceUrl }}"
                                class="w-full object-contain max-h-72 cursor-pointer hover:opacity-90 transition"
                                onclick="document.getElementById('invoice-lightbox').classList.remove('hidden')">

                        </div>

                        <p class="text-xs text-slate-500 text-center mt-2">
                            اضغط على الصورة للعرض الكامل
                        </p>
                    @endif
                @else
                    <div
                        class="rounded-xl border-2 border-dashed border-slate-700 bg-slate-800/30 p-10 text-center text-slate-500">
                        <i class="fas fa-file-image text-5xl opacity-30 mb-3"></i>
                        <p class="text-sm">
                            لم يتم إرفاق ملف الفاتورة بعد
                        </p>
                    </div>

                @endif
            </div>

            {{-- Upload / Replace form --}}
            <div class="lg:w-80 w-full space-y-4">
                @if ($errors->has('invoice_image'))
                    <div class="alert-error px-3 py-2 text-sm flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                        {{ $errors->first('invoice_image') }}
                    </div>
                @endif

                <form action="{{ route('supplier-purchases.upload-invoice', $supplierPurchase) }}" method="POST"
                    enctype="multipart/form-data" id="inv-upload-form">
                    @csrf
                    <label class="block cursor-pointer">
                        <div id="inv-drop-zone"
                            class="border-2 border-dashed border-slate-600 hover:border-amber-500 rounded-xl p-6 text-center transition-all duration-200 group">
                            <i
                                class="fas fa-cloud-upload-alt text-3xl text-slate-500 group-hover:text-amber-400 transition mb-2"></i>
                            <p class="text-sm text-slate-400 group-hover:text-slate-200 transition" id="inv-drop-label">
                                {{ $supplierPurchase->invoice_image_path ? 'اسحب صورة جديدة أو اضغط للاستبدال' : 'اسحب صورة الفاتورة هنا أو اضغط للاختيار' }}
                            </p>
                            <p class="text-xs text-slate-600 mt-1">JPG, PNG, WEBP · الحد الأقصى 5 ميجابايت</p>
                        </div>
                        <input type="file" name="invoice_image" id="inv-invoice-image"
                            accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewInvImage(this)">
                    </label>

                    {{-- Preview before upload --}}
                    <div id="inv-preview-container"
                        class="hidden mt-3 rounded-lg overflow-hidden border border-slate-700">
                        <img id="inv-preview-img" src="" alt="معاينة"
                            class="w-full object-contain max-h-48 bg-slate-900">
                    </div>

                    <button type="submit"
                        class="w-full mt-4 btn-accent text-slate-900 font-bold py-2.5 rounded-lg text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-upload"></i>
                        {{ $supplierPurchase->invoice_image_path ? 'استبدال الصورة' : 'رفع صورة الفاتورة' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Items Table ──────────────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-white font-bold">بنود الفاتورة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        @foreach (['الوصف', 'الكمية', 'الوحدة', 'سعر الوحدة', 'الإجمالي', 'مادة مخزون'] as $h)
                            <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($supplierPurchase->items as $item)
                        <tr class="table-row">
                            <td class="px-4 py-3 text-white font-medium">{{ $item->description }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ number_format($item->quantity, 3) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $item->unit }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($item->total_price, 0) }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $item->inventoryItem?->name_ar ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-slate-800/30">
                        <td colspan="4" class="px-4 py-3 text-right text-slate-400 font-bold">الإجمالي</td>
                        <td class="px-4 py-3 text-amber-400 font-bold text-lg">
                            {{ number_format($supplierPurchase->total_amount, 0) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Lightbox ─────────────────────────────────────────────────────────────── --}}
    @if ($supplierPurchase->invoice_image_path)
        <div id="invoice-lightbox" class="hidden fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
            onclick="this.classList.add('hidden')">
            <div class="relative max-w-5xl max-h-screen p-4">
                <button
                    class="absolute top-2 left-2 text-white bg-slate-800 rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition"
                    onclick="document.getElementById('invoice-lightbox').classList.add('hidden')">
                    <i class="fas fa-times text-sm"></i>
                </button>
                <img src="{{ Storage::url($supplierPurchase->invoice_image_path) }}" alt="صورة الفاتورة"
                    class="max-w-full max-h-screen object-contain rounded-xl shadow-2xl">
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function previewInvImage(input) {
                const file = input.files[0];
                if (!file) return;
                document.getElementById('inv-drop-label').textContent = file.name;
                const reader = new FileReader();
                reader.onload = e => {
                    const c = document.getElementById('inv-preview-container');
                    document.getElementById('inv-preview-img').src = e.target.result;
                    c.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }

            // Drag-and-drop
            const dz = document.getElementById('inv-drop-zone');
            if (dz) {
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
                        const input = document.getElementById('inv-invoice-image');
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        previewInvImage(input);
                    }
                });
            }
        </script>
    @endpush

@endsection

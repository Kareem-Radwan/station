@extends('layouts.app')
@section('title', 'تفاصيل السند')
@section('content')

@push('head')
<style>
@media print {
    /* Hide everything except the receipt card */
    #sidebar, nav, .print\:hidden {
        display: none !important;
    }
    body, html, main {
        background: white !important;
        color: black !important;
        min-height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #receipt-print-area {
        border: none !important;
        box-shadow: none !important;
        padding: 20px !important;
        margin: 0 auto !important;
        width: 100% !important;
        max-width: 100% !important;
        color: #000000 !important;
        background: #ffffff !important;
    }
    .bg-slate-100 {
        background-color: #f1f5f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .border-dashed {
        border-style: dashed !important;
        border-color: #94a3b8 !important;
    }
    /* Show signed image in print if present */
    .signed-image-print { display: block !important; margin-top: 20px; }
}
.signed-image-print { display: none; }
</style>
@endpush

{{-- ── Top Bar ─────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6 print:hidden">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('receipts.index') }}" class="text-slate-400 hover:text-white text-sm">السندات</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">سند #{{ $receipt->id }}</span>
        </div>
        <div class="flex items-center gap-2 mt-1">
            <span class="badge {{ $receipt->type==='in' ? 'badge-green' : 'badge-red' }}">
                {{ $receipt->type==='in' ? 'سند قبض' : 'سند صرف' }}
            </span>
            {{-- Status badge --}}
            @if($receipt->isPending())
                <span class="badge badge-yellow">
                    <i class="fas fa-clock text-xs ml-1"></i> معلق
                </span>
            @else
                <span class="badge badge-green">
                    <i class="fas fa-check-circle text-xs ml-1"></i> منتهٍ
                </span>
            @endif
        </div>
    </div>
    <div class="flex gap-2 flex-wrap justify-end">
        {{-- Mark Done / Reopen toggle --}}
        <form action="{{ route('receipts.mark-done', $receipt) }}" method="POST">
            @csrf @method('PATCH')
            @if($receipt->isPending())
                <button type="submit"
                    onclick="return confirm('تحديد السند كمنتهٍ (تم التوقيع)؟')"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-check-double"></i> تحديد كمنتهٍ
                </button>
            @else
                <button type="submit"
                    onclick="return confirm('إعادة السند إلى حالة معلق؟')"
                    class="flex items-center gap-2 bg-slate-600 hover:bg-slate-500 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-undo"></i> إعادة فتح
                </button>
            @endif
        </form>

        <button onclick="window.print()"
            class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-print"></i> طباعة
        </button>
        <a href="{{ route('receipts.edit', $receipt) }}"
            class="btn-primary text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
    </div>
</div>

{{-- ── Status Banner ────────────────────────────────────────────────────────── --}}
@if($receipt->isDone())
<div class="mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border border-emerald-500/40 bg-emerald-500/10 text-emerald-300 print:hidden">
    <i class="fas fa-check-circle text-xl text-emerald-400"></i>
    <div>
        <p class="font-bold">السند مكتمل ومنتهٍ</p>
        <p class="text-xs text-emerald-400/80">
            {{ $receipt->signed_image_path ? 'تم رفع صورة السند الموقعة.' : 'لم يتم رفع صورة السند الموقعة بعد.' }}
        </p>
    </div>
</div>
@else
<div class="mb-6 flex items-center gap-3 px-5 py-3 rounded-xl border border-amber-500/40 bg-amber-500/10 text-amber-300 print:hidden">
    <i class="fas fa-hourglass-half text-xl text-amber-400"></i>
    <div>
        <p class="font-bold">السند في انتظار التوقيع</p>
        <p class="text-xs text-amber-400/80">بعد التوقيع، قم بتحديد السند كـ "منتهٍ" ثم ارفع صورته.</p>
    </div>
</div>
@endif

{{-- ── Main Layout: Receipt Card + Side Panel ──────────────────────────────── --}}
<div class="flex gap-6 items-start flex-col lg:flex-row">

    {{-- Receipt Card (printable) --}}
    <div id="receipt-print-area" class="flex-1 bg-white text-slate-900 p-8 rounded-xl shadow-xl print:shadow-none print:p-0">
        <div class="border-b-2 border-slate-800 pb-4 mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">{{ $receipt->type==='in' ? 'سند قبض' : 'سند صرف' }}</h1>
                <p class="text-slate-500 mt-1">Receipt Voucher</p>
            </div>
            <div class="text-left text-sm">
                <p><span class="font-bold text-slate-700">رقم السند:</span> {{ str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p><span class="font-bold text-slate-700">التاريخ:</span> {{ $receipt->receipt_date->format('d/m/Y') }}</p>
                <p class="mt-1">
                    <span class="font-bold text-slate-700">الحالة:</span>
                    {{ $receipt->isDone() ? 'منتهٍ ✓' : 'معلق' }}
                </p>
            </div>
        </div>

        <div class="space-y-6 text-lg">
            <div class="flex gap-4 p-4 bg-slate-100 rounded-lg">
                <span class="font-bold w-36 shrink-0">{{ $receipt->type==='in' ? 'استلمنا من السيد/ة:' : 'صرفنا للسيد/ة:' }}</span>
                <span class="border-b border-slate-400 flex-1 border-dashed font-medium">{{ $receipt->recipient_name }}</span>
            </div>
            <div class="flex gap-4 p-4 bg-slate-100 rounded-lg">
                <span class="font-bold w-36 shrink-0">مبلغاً وقدره:</span>
                <span class="border-b border-slate-400 flex-1 border-dashed font-bold text-xl">{{ number_format($receipt->amount, 2) }} جنيه</span>
            </div>
            <div class="flex gap-4 p-4 bg-slate-100 rounded-lg">
                <span class="font-bold w-36 shrink-0">وذلك عن:</span>
                <span class="border-b border-slate-400 flex-1 border-dashed">{{ $receipt->description }}</span>
            </div>
        </div>

        <div class="mt-16 flex justify-between px-12 pb-8">
            <div class="text-center">
                <p class="font-bold mb-8">المستلم</p>
                <p class="border-t border-slate-400 w-40 mx-auto pt-2 text-sm text-slate-500">التوقيع</p>
            </div>
            <div class="text-center">
                <p class="font-bold mb-8">المحاسب</p>
                <p class="border-t border-slate-400 w-40 mx-auto pt-2 text-sm text-slate-500">التوقيع</p>
            </div>
        </div>

        {{-- Signed image shows in print if uploaded --}}
        @if($receipt->signed_image_path)
        <div class="signed-image-print text-center mt-6">
            <p class="text-sm text-slate-500 mb-2">صورة السند الموقعة:</p>
            <img src="{{ Storage::url($receipt->signed_image_path) }}"
                 alt="صورة السند الموقعة"
                 class="max-w-full mx-auto border border-slate-300 rounded">
        </div>
        @endif
    </div>

    {{-- ── Side Panel: Image Upload (only visible when done) ────────────── --}}
    @if($receipt->isDone())
    <div class="lg:w-80 w-full space-y-4 print:hidden">

        {{-- Uploaded Image Preview --}}
        @if($receipt->signed_image_path)
        <div class="card p-4 space-y-3">
            <h3 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                <i class="fas fa-image text-emerald-400"></i> صورة السند الموقعة
            </h3>
            <div class="rounded-lg overflow-hidden border border-slate-700">
                <img src="{{ Storage::url($receipt->signed_image_path) }}"
                     alt="صورة السند"
                     class="w-full object-contain max-h-64 bg-slate-900 cursor-pointer"
                     onclick="document.getElementById('img-lightbox').classList.remove('hidden')">
            </div>
            <p class="text-xs text-slate-500 text-center">اضغط على الصورة للعرض الكامل</p>
        </div>
        @else
        <div class="card p-4 border border-dashed border-amber-500/40 text-center space-y-2">
            <i class="fas fa-image text-3xl text-slate-600"></i>
            <p class="text-sm text-slate-400">لم يتم رفع صورة السند الموقعة بعد</p>
        </div>
        @endif

        {{-- Upload Form --}}
        <div class="card p-5 space-y-4">
            <h3 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                <i class="fas fa-upload text-amber-400"></i>
                {{ $receipt->signed_image_path ? 'تحديث صورة السند' : 'رفع صورة السند الموقعة' }}
            </h3>

            @if($errors->has('signed_image'))
            <div class="alert-error px-3 py-2 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-400"></i>
                {{ $errors->first('signed_image') }}
            </div>
            @endif

            <form action="{{ route('receipts.upload-signed', $receipt) }}" method="POST"
                  enctype="multipart/form-data" id="upload-form">
                @csrf
                <label class="block cursor-pointer">
                    <div id="drop-zone"
                         class="border-2 border-dashed border-slate-600 hover:border-amber-500 rounded-xl p-6 text-center transition-all duration-200 group">
                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-500 group-hover:text-amber-400 transition mb-2"></i>
                        <p class="text-sm text-slate-400 group-hover:text-slate-200 transition" id="drop-label">
                            اسحب الصورة هنا أو اضغط للاختيار
                        </p>
                        <p class="text-xs text-slate-600 mt-1">JPG, PNG, WEBP · الحد الأقصى 5 ميجابايت</p>
                    </div>
                    <input type="file" name="signed_image" id="signed_image"
                           accept="image/jpeg,image/png,image/webp"
                           class="hidden" onchange="previewImage(this)">
                </label>

                {{-- Image preview before upload --}}
                <div id="preview-container" class="hidden mt-3 rounded-lg overflow-hidden border border-slate-700">
                    <img id="preview-img" src="" alt="معاينة" class="w-full object-contain max-h-48 bg-slate-900">
                </div>

                <button type="submit"
                    class="w-full mt-4 btn-accent text-slate-900 font-bold py-2.5 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> رفع وحفظ الصورة
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- ── Lightbox for full image view ────────────────────────────────────────── --}}
@if($receipt->signed_image_path)
<div id="img-lightbox"
     class="hidden fixed inset-0 z-50 bg-black/90 flex items-center justify-center print:hidden"
     onclick="this.classList.add('hidden')">
    <div class="relative max-w-4xl max-h-screen p-4">
        <button class="absolute top-2 left-2 text-white bg-slate-800 rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition"
                onclick="document.getElementById('img-lightbox').classList.add('hidden')">
            <i class="fas fa-times text-sm"></i>
        </button>
        <img src="{{ Storage::url($receipt->signed_image_path) }}"
             alt="صورة السند الموقعة"
             class="max-w-full max-h-screen object-contain rounded-xl shadow-2xl">
    </div>
</div>
@endif

@push('scripts')
<script>
function previewImage(input) {
    const file = input.files[0];
    if (!file) return;

    document.getElementById('drop-label').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const container = document.getElementById('preview-container');
        document.getElementById('preview-img').src = e.target.result;
        container.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

// Drag-and-drop support
const dropZone = document.getElementById('drop-zone');
if (dropZone) {
    ['dragenter','dragover'].forEach(ev => {
        dropZone.addEventListener(ev, e => {
            e.preventDefault();
            dropZone.classList.add('border-amber-500', 'bg-amber-500/5');
        });
    });
    ['dragleave','drop'].forEach(ev => {
        dropZone.addEventListener(ev, e => {
            e.preventDefault();
            dropZone.classList.remove('border-amber-500', 'bg-amber-500/5');
        });
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('signed_image');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            previewImage(input);
        }
    });
}
</script>
@endpush

@endsection

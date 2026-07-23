@extends('layouts.app')
@section('title', 'خلطة جديدة')
@section('content')

@include('partials.page-header', ['title' => 'إضافة خلطة خرسانية جديدة', 'icon' => 'fa-flask'])

<div class="max-w-xl">
    <form action="{{ route('concrete-mixes.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card p-6 space-y-5">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                <i class="fas fa-cogs text-amber-400 text-sm"></i> بيانات الخلطة
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">
                        المقاومة <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="strength" value="{{ old('strength') }}" required min="1"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: 250">
                    @error('strength')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">
                        اسمنت لكل م³ (كغ) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" step="0.001" name="cement_per_m3" value="{{ old('cement_per_m3') }}" required min="1"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: 350"
                        oninput="updatePreview()">
                    @error('cement_per_m3')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Description preview (auto-generated, read-only) --}}
            <div class="hidden">
                <label class="block text-slate-400 text-sm mb-1.5">الوصف (يُولَّد تلقائياً)</label>
                <div id="descriptionPreview"
                    class="bg-slate-800/60 border border-slate-700 rounded-lg px-3 py-2.5 text-slate-300 text-sm min-h-[40px]">
                    أدخل المقاومة وكمية الاسمنت لمعاينة الوصف
                </div>
                <p class="text-slate-600 text-xs mt-1">
                    <i class="fas fa-info-circle ml-1"></i>
                    الصيغة: "خرسانة {المقاومة} - {كغ/م³}"
                </p>
            </div>

            <div class="flex items-center gap-3 hidden">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="isActiveToggle"
                        class="sr-only peer" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <div class="w-10 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer
                        peer-checked:after:translate-x-full peer-checked:bg-amber-500
                        after:content-[''] after:absolute after:top-0.5 after:start-[2px]
                        after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all">
                    </div>
                </label>
                <span class="text-slate-300 text-sm">تفعيل الخلطة فور الإنشاء</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ الخلطة
            </button>
            <a href="{{ route('concrete-mixes.index') }}"
                class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">
                إلغاء
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function updatePreview() {
    const strength    = parseInt(document.querySelector('[name="strength"]').value) || 0;
    const cementPerM3 = parseFloat(document.querySelector('[name="cement_per_m3"]').value) || 0;
    const preview     = document.getElementById('descriptionPreview');

    if (strength > 0 && cementPerM3 > 0) {
        preview.textContent = `خرسانة ${strength} - ${Math.round(cementPerM3)} كغ/م³`;
        preview.classList.remove('text-slate-500');
        preview.classList.add('text-amber-400');
    } else {
        preview.textContent = 'أدخل المقاومة وكمية الاسمنت لمعاينة الوصف';
        preview.classList.remove('text-amber-400');
        preview.classList.add('text-slate-500');
    }
}

document.querySelector('[name="strength"]').addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush
@endsection

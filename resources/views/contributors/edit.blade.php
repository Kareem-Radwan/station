@extends('layouts.app')
@section('title', 'تعديل المساهم')
@section('content')

    @include('partials.page-header', ['title' => 'تعديل: ' . $contributor->name, 'icon' => 'fa-user-edit'])

    <div class="max-w-3xl">
        <form action="{{ route('contributors.update', $contributor) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')

            <div class="card p-6 space-y-5">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3">البيانات الأساسية</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">اسم المساهم <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $contributor->name) }}" required
                            class="input-field w-full px-3 py-2.5 text-sm">
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $contributor->phone) }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">رقم الهوية الوطنية</label>
                        <input type="text" name="national_id" value="{{ old('national_id', $contributor->national_id) }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                        <select name="is_active" class="input-field w-full px-3 py-2.5 text-sm">
                            <option value="1" {{ $contributor->is_active ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ !$contributor->is_active ? 'selected' : '' }}>موقف</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-400 text-sm mb-1.5">العنوان</label>
                        <input type="text" name="address" value="{{ old('address', $contributor->address) }}"
                            class="input-field w-full px-3 py-2.5 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                        <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $contributor->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card p-6 space-y-5">
                <h3 class="text-white font-semibold border-b border-slate-700 pb-3">بيانات الحصة في رأس المال</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">نسبة الحصة (%) <span
                                class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" name="share_percentage"
                                value="{{ old('share_percentage', $contributor->share_percentage) }}" required
                                class="input-field w-full px-3 py-2.5 text-sm pr-10">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">%</span>
                        </div>
                        @error('share_percentage')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1.5">قيمة الحصة (جنية) <span
                                class="text-red-400">*</span></label>
                        <input type="number" step="0.01" min="0" name="share_amount"
                            value="{{ old('share_amount', $contributor->share_amount) }}" required
                            class="input-field w-full px-3 py-2.5 text-sm">
                        @error('share_amount')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <a href="{{ route('contributors.show', $contributor) }}"
                    class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

@endsection

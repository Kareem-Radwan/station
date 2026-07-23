@extends('layouts.app')
@section('title', 'إضافة محطة مجاورة')
@section('content')

    @include('partials.page-header', [
        'title' => 'إضافة محطة مجاورة',
        'icon' => 'fa-industry',
    ])

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('neighboring-stations.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">اسم المحطة *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input-field w-full px-4 py-2">
                    @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">الشخص المسؤول</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                        class="input-field w-full px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="input-field w-full px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">العنوان</label>
                    <textarea name="address" rows="3" class="input-field w-full px-4 py-2">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-4 py-2">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked
                        class="w-4 h-4 text-amber-500 rounded focus:ring-amber-500">
                    <label class="text-sm text-slate-300">محطة نشطة</label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ
                </button>
                <a href="{{ route('neighboring-stations.index') }}" class="btn bg-slate-700 hover:bg-slate-600 px-6 py-2 rounded-lg text-white">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>

@endsection

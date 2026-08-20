@extends('layouts.app')
@section('title', 'إدارة وصفات الخلطات')
@section('content')

    <div class="mb-6">
        @include('partials.page-header', ['title' => 'إدارة وصفات الخلطات', 'icon' => 'fa-cog'])
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Material Densities Section -->
    <div class="card mb-6 p-6 bg-slate-800/40 border border-slate-700/60 rounded-xl">
        <h2 class="text-xl font-bold text-slate-100 mb-1">إعدادات كثافة المواد</h2>
        <p class="text-sm text-slate-400 mb-6">الكثافة تُستخدم لتحويل الكميات من كيلوغرام إلى متر مكعب</p>

        <form action="{{ route('mix-recipes.densities.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @foreach ($densities as $density)
                    <div class="bg-slate-900/50 border border-slate-700/80 rounded-lg p-4">
                        <input type="hidden" name="densities[{{ $loop->index }}][id]" value="{{ $density->id }}">

                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            {{ $density->material_name_ar }} ({{ $density->material_name }})
                        </label>

                        <div class="flex items-center gap-2">
                            <input type="number" step="0.001" name="densities[{{ $loop->index }}][density_kg_per_m3]"
                                value="{{ $density->density_kg_per_m3 }}"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                required>
                            <span class="text-xs text-slate-400 whitespace-nowrap">كجم/م³</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit"
                class="btn-primary flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>
                حفظ الكثافات
            </button>
        </form>
    </div>

    <!-- Mix Recipes Section -->
    <div class="card p-6 bg-slate-800/40 border border-slate-700/60 rounded-xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-xl font-bold text-slate-100">وصفات الخلطات الحالية</h2>
            <button onclick="toggleForm('add')"
                class="btn-accent px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i>
                إضافة وصفة جديدة
            </button>
        </div>

        <!-- Add New Recipe Form -->
        <div id="add-form" class="hidden mb-6 border border-slate-700 rounded-lg p-5 bg-slate-900/40">
            <h3 class="text-lg font-semibold text-slate-100 mb-4">إضافة وصفة جديدة</h3>
            <form action="{{ route('mix-recipes.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">كمية الاسمنت (كجم/م³) *</label>
                        <input type="number" name="cement_per_m3" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">رمل (كجم) *</label>
                        <input type="number" step="0.001" name="sand_kg" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">سن 1 (كجم) *</label>
                        <input type="number" step="0.001" name="gravel1_kg" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">سن 2 (كجم) *</label>
                        <input type="number" step="0.001" name="gravel2_kg" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">اسمنت (كجم) *</label>
                        <input type="number" step="0.001" name="cement_kg" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">ماء (م³) *</label>
                        <input type="number" step="0.001" name="water_m3" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">مضافات (لتر) *</label>
                        <input type="number" step="0.001" name="additives_liter" required
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">جاز (لتر)</label>
                        <input type="number" step="0.001" name="gaz_liter" value="0"
                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-400 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="2"
                        class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center text-sm font-semibold">
                        <i class="fas fa-save mr-2"></i> حفظ
                    </button>
                    <button type="button" onclick="toggleForm('add')"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors text-sm font-semibold">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>

        <!-- Recipes Table -->
        <div class="overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-right">
                <thead>
                    <tr class="bg-slate-900/60 border-b border-slate-700 text-slate-300">
                        <th class="px-4 py-3 font-semibold">الاسمنت (كجم/م³)</th>
                        <th class="px-4 py-3 font-semibold">رمل (كجم)</th>
                        <th class="px-4 py-3 font-semibold">سن 1 (كجم)</th>
                        <th class="px-4 py-3 font-semibold">سن 2 (كجم)</th>
                        <th class="px-4 py-3 font-semibold">اسمنت (كجم)</th>
                        <th class="px-4 py-3 font-semibold">ماء (م³)</th>
                        <th class="px-4 py-3 font-semibold">مضافات (لتر)</th>
                        <th class="px-4 py-3 font-semibold">جاز (لتر)</th>
                        <th class="px-4 py-3 font-semibold text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 bg-slate-900/10">
                    @forelse($recipes as $recipe)
                        <tr id="row-{{ $recipe->id }}" class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-4 py-3 text-amber-400 font-bold">{{ $recipe->cement_per_m3 }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->sand_kg }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->gravel1_kg }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->gravel2_kg }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->cement_kg }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->water_m3 }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->additives_liter }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $recipe->gaz_liter ?? 0 }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button onclick="toggleEditForm({{ $recipe->id }})"
                                    class="text-blue-400 hover:text-blue-300 font-medium inline-flex items-center mx-2">
                                    <i class="fas fa-edit mr-1"></i> تعديل
                                </button>
                                <form action="{{ route('mix-recipes.destroy', $recipe) }}" method="POST" class="inline"
                                    onsubmit="return confirm('هل أنت متأكد من حذف هذه الوصفة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-300 font-medium inline-flex items-center mx-2">
                                        <i class="fas fa-trash mr-1"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-form-{{ $recipe->id }}" class="hidden bg-slate-800/20">
                            <td colspan="9" class="px-6 py-4 border-b border-slate-700">
                                <form action="{{ route('mix-recipes.update', $recipe) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">رمل (كجم)</label>
                                            <input type="number" step="0.001" name="sand_kg"
                                                value="{{ $recipe->sand_kg }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">سن 1
                                                (كجم)</label>
                                            <input type="number" step="0.001" name="gravel1_kg"
                                                value="{{ $recipe->gravel1_kg }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">سن 2
                                                (كجم)</label>
                                            <input type="number" step="0.001" name="gravel2_kg"
                                                value="{{ $recipe->gravel2_kg }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">اسمنت
                                                (كجم)</label>
                                            <input type="number" step="0.001" name="cement_kg"
                                                value="{{ $recipe->cement_kg }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">ماء (م³)</label>
                                            <input type="number" step="0.001" name="water_m3"
                                                value="{{ $recipe->water_m3 }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">مضافات
                                                (لتر)</label>
                                            <input type="number" step="0.001" name="additives_liter"
                                                value="{{ $recipe->additives_liter }}" required
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-400 mb-1">جاز
                                                (لتر)</label>
                                            <input type="number" step="0.001" name="gaz_liter"
                                                value="{{ $recipe->gaz_liter ?? 0 }}"
                                                class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-xs font-medium text-slate-400 mb-1">ملاحظات</label>
                                        <textarea name="notes" rows="2"
                                            class="w-full bg-slate-900 border border-slate-700 text-slate-100 rounded-lg p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">{{ $recipe->notes }}</textarea>
                                    </div>

                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center text-xs font-semibold">
                                            <i class="fas fa-save mr-2"></i> حفظ التعديلات
                                        </button>
                                        <button type="button" onclick="toggleEditForm({{ $recipe->id }})"
                                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors text-xs font-semibold">
                                            إلغاء
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                                <i class="fas fa-flask text-3xl mb-3 block opacity-30"></i>
                                لا توجد وصفات. قم بإضافة وصفة جديدة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleForm(formId) {
                const form = document.getElementById(formId + '-form');
                form.classList.toggle('hidden');
            }

            function toggleEditForm(recipeId) {
                const row = document.getElementById('row-' + recipeId);
                const form = document.getElementById('edit-form-' + recipeId);
                row.classList.toggle('hidden');
                form.classList.toggle('hidden');
            }
        </script>
    @endpush

@endsection

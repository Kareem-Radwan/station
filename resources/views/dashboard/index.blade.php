@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">لوحة التحكم</h1>
                <p class="text-slate-400 text-sm mt-1">{{ now()->format('l، d F Y') }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('orders.create') }}"
                    class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> طلب جديد
                </a>
                <a href="{{ route('backup.download') }}"
                    class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-database"></i> تخزين قاعدة البيانات
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Treasury Balance --}}
            <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-400 text-xs mb-1">رصيد الخزينة</p>
                        <p class="text-2xl font-bold {{ $treasuryBalance >= 0 ? 'text-amber-400' : 'text-red-400' }}">
                            {{ number_format($treasuryBalance, 0) }}
                        </p>
                        <p class="text-slate-500 text-xs">جنية</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <i class="fas fa-vault text-amber-400 text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Pending Orders --}}
            <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-400 text-xs mb-1">طلبات معلقة</p>
                        <p class="text-2xl font-bold text-blue-400">{{ $pendingOrders }}</p>
                        <p class="text-slate-500 text-xs">{{ $scheduledOrders }} مجدول</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Overdue Credits --}}
            <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-red-600"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-400 text-xs mb-1">ديون متأخرة</p>
                        <p class="text-2xl font-bold text-red-400">{{ $overdueCredits }}</p>
                        <p class="text-slate-500 text-xs">{{ $pendingCredits }} معلق</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Low Stock --}}
            <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 to-orange-600"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-400 text-xs mb-1">مواد منخفضة</p>
                        <p
                            class="text-2xl font-bold {{ $lowStockItems->count() > 0 ? 'text-orange-400 low-stock-pulse' : 'text-green-400' }}">
                            {{ $lowStockItems->count() }}
                        </p>
                        <p class="text-slate-500 text-xs">من أصل 6 مواد</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center">
                        <i class="fas fa-boxes text-orange-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Credits Due Soon Alert --}}
        @if ($creditsDueSoon->count() > 0)
            <div class="card p-6 bg-orange-500/5 border border-orange-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="fas fa-clock text-orange-400"></i>
                        ديون قريبة من الاستحقاق
                        <span class="badge badge-orange">{{ $creditsDueSoon->total() }}</span>
                    </h3>
                    <a href="{{ route('credits.index') }}?status=pending"
                        class="text-orange-400 hover:text-orange-300 text-sm">
                        عرض الكل <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                </div>
                <p class="text-slate-400 text-sm mb-4">الديون التالية مستحقة خلال يومين</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($creditsDueSoon as $credit)
                        <div class="bg-slate-800/50 rounded-lg p-4 border border-orange-500/30">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-white font-medium text-sm mb-4">
                                        {{ $credit->creditable_type === 'customer' ? $credit->creditable->name : $credit->creditable->name }}
                                        | {{ $credit->creditable_type === 'customer' ? 'عميل' : 'مورد' }}
                                    </p>
                                </div>
                                <p class="text-orange-400 font-bold">{{ number_format($credit->amount, 0) }} جنيه</p>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-3 pt-3 border-t border-slate-700">
                                <span class="text-slate-500">تاريخ الاستحقاق</span>
                                <span class="text-orange-400 font-medium">{{ $credit->due_date->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($creditsDueSoon->hasPages())
                    <div class="mt-4 border-t border-slate-700 pt-4">
                        {{ $creditsDueSoon->links() }}
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Revenue Chart --}}
            <div class="card p-6 lg:col-span-2">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-amber-400"></i>
                    الإيرادات والمصروفات (آخر 6 أشهر)
                </h3>
                <canvas id="revenueChart" height="200"></canvas>
            </div>

            {{-- Low Stock Items --}}
            <div class="card p-6">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-orange-400"></i>
                    تنبيهات المخزون
                </h3>
                @if ($lowStockItems->isEmpty())
                    <div class="text-center py-6">
                        <i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i>
                        <p class="text-green-400 text-sm">المخزون في المستوى الطبيعي</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($lowStockItems as $item)
                            <div
                                class="flex items-center justify-between bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $item->name_ar }}</p>
                                    <p class="text-red-400 text-xs">{{ number_format($item->current_stock, 1) }}
                                        {{ $item->unit }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-red">منخفض</span>
                                    <p class="text-slate-500 text-xs mt-1">الحد: {{ $item->alert_threshold }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('inventory.index') }}"
                        class="block text-center text-amber-400 text-xs mt-3 hover:text-amber-300">
                        عرض المخزون كامل ←
                    </a>
                @endif
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Today's Deliveries --}}
            <div class="card p-6">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-truck text-blue-400"></i>
                    توصيلات اليوم
                </h3>
                @if ($todayOrders->isEmpty())
                    <div class="text-center py-6 text-slate-500">
                        <i class="fas fa-calendar-times text-2xl mb-2"></i>
                        <p class="text-sm">لا توجد توصيلات مجدولة اليوم</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($todayOrders as $order)
                            <div
                                class="flex items-center justify-between bg-slate-800/50 rounded-lg p-3 border border-slate-700">
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $order->customer->name }}</p>
                                    <p class="text-slate-400 text-xs">{{ $order->location ?? '-' }} |
                                        {{ $order->quantity_m3 }} م³</p>
                                </div>
                                <div class="text-left">
                                    <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                                    @if ($order->delivery_time)
                                        <p class="text-slate-400 text-xs mt-1">{{ $order->delivery_time }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upcoming Schedule --}}
            <div class="card p-6">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-week text-amber-400"></i>
                    الجدول الأسبوعي القادم
                </h3>
                @if (!$upcomingSchedule)
                    <div class="text-center py-6 text-slate-500">
                        <i class="fas fa-calendar text-2xl mb-2"></i>
                        <p class="text-sm">لا يوجد جدول معد</p>
                        <a href="{{ route('schedules.create') }}"
                            class="text-amber-400 text-xs mt-2 block hover:text-amber-300">
                            إنشاء جدول جديد ←
                        </a>
                    </div>
                @else
                    <div>
                        <p class="text-slate-400 text-xs mb-3">
                            {{ $upcomingSchedule->week_start->format('d/m') }} —
                            {{ $upcomingSchedule->week_end->format('d/m/Y') }}
                        </p>
                        <div class="space-y-2">
                            @foreach ($upcomingSchedule->entries->take(5) as $entry)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                        <span class="text-white">{{ $entry->customer->name }}</span>
                                    </div>
                                    <span class="text-slate-400 text-xs">{{ $entry->delivery_date->format('d/m') }} |
                                        {{ $entry->quantity_m3 }} م³</span>
                                </div>
                            @endforeach
                        </div>
                        @if ($upcomingSchedule->entries->count() > 5)
                            <p class="text-slate-500 text-xs mt-2">+{{ $upcomingSchedule->entries->count() - 5 }} إدخالات
                                أخرى</p>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        {{-- Expense Breakdown --}}
        @if ($expenseBreakdown->count() > 0)
            <div class="card p-6">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-blue-400"></i>
                    مصروفات هذا الشهر حسب الفئة
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php $categories = \App\Models\Expense::categoryList(); @endphp
                    @foreach ($expenseBreakdown as $exp)
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                            <p class="text-slate-400 text-xs mb-1">{{ $categories[$exp->category] ?? $exp->category }}</p>
                            <p class="text-white font-bold">{{ number_format($exp->total, 0) }}</p>
                            <p class="text-slate-500 text-xs">جنية</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', () => {
            const canvas = document.getElementById('revenueChart');

            if (!canvas || !window.Chart) return;

            const ctx = canvas.getContext('2d');
            const data = @json($chartData);

            new window.Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                            label: 'الإيرادات',
                            data: data.map(d => d.revenue),
                            backgroundColor: 'rgba(245, 158, 11, 0.7)',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'المصروفات',
                            data: data.map(d => d.expenses),
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#94a3b8',
                                font: {
                                    family: 'Cairo'
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: '#1e3a5f33'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: '#1e3a5f33'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush

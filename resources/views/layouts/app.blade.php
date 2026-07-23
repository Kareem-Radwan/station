<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام إدارة محطة الخرسانة') - محطة الخرسانة</title>
    <meta name="description" content="نظام إدارة محطة خلط الخرسانة - إدارة شاملة للعمليات والمخزون والمالية">
    <link rel="shortcut icon" href="{{asset('logo.png')}}" type="image/x-icon">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            font-family: 'Cairo', Tahoma, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #0f2044 0%, #1a3a6e 50%, #0f2044 100%);
        }

        .sidebar-link {
            transition: all 0.2s ease;
            border-radius: 0.5rem;
            margin-bottom: 2px;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: linear-gradient(90deg, #f59e0b22, #f59e0b11);
            border-right: 3px solid #f59e0b;
            color: #fcd34d;
        }

        .card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #1e3a5f44;
            border-radius: 1rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            border: 1px solid #3b82f633;
        }

        .table-row:hover {
            background: #1e3a5f33;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a9e 100%);
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2d5a9e 0%, #3b72c0 100%);
            transform: translateY(-1px);
        }

        .btn-accent {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            transition: all 0.2s;
        }

        .btn-accent:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #fcd34d 100%);
            transform: translateY(-1px);
        }

        .input-field {
            background: #0f172a;
            border: 1px solid #1e3a5f88;
            color: #e2e8f0;
            border-radius: 0.5rem;
            transition: border-color 0.2s;
        }

        .input-field:focus {
            border-color: #f59e0b;
            outline: none;
            box-shadow: 0 0 0 2px #f59e0b33;
        }

        .badge {
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
        }

        .badge-green {
            background: #10b98133;
            color: #34d399;
            border: 1px solid #10b98155;
        }

        .badge-red {
            background: #ef444433;
            color: #f87171;
            border: 1px solid #ef444455;
        }

        .badge-yellow {
            background: #f59e0b33;
            color: #fcd34d;
            border: 1px solid #f59e0b55;
        }

        .badge-blue {
            background: #3b82f633;
            color: #93c5fd;
            border: 1px solid #3b82f655;
        }

        .badge-gray {
            background: #6b728033;
            color: #9ca3af;
            border: 1px solid #6b728055;
        }

        .alert-success {
            background: #10b98122;
            border: 1px solid #10b98155;
            color: #34d399;
            border-radius: 0.5rem;
        }

        .alert-error {
            background: #ef444422;
            border: 1px solid #ef444455;
            color: #f87171;
            border-radius: 0.5rem;
        }

        .navbar {
            background: linear-gradient(90deg, #0a1628 0%, #1e3a5f 100%);
            border-bottom: 1px solid #1e3a5f66;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e3a5f;
            border-radius: 3px;
        }

        .low-stock-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .5
            }
        }

        .page-enter {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }
    </style>
    @stack('head')
</head>

<body class="text-slate-200">

    {{-- Navbar --}}
    <nav class="navbar px-6 py-3 flex items-center justify-between sticky top-0 z-50 shadow-lg">
        <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="text-slate-400 hover:text-amber-400 transition lg:hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center gap-3">
                <div class="w-[60px] h-[60px] rounded-xl p-1 bg-white flex items-center justify-center shadow-lg">
                    <img src="{{ asset('logo.png') }}" alt="">
                </div>
                <div>
                    <div class="font-bold text-white text-sm leading-tight">محطة نيو سوليد اب</div>
                    <div class="text-xs text-slate-400">للخرسانة الجاهزة</div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-slate-800 rounded-full px-3 py-1.5 border border-slate-700">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-xs text-slate-300">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </nav>

    <div class="flex" style="min-height: calc(100vh - 64px)">
        {{-- Sidebar --}}
        <aside id="sidebar" class="sidebar w-64 min-h-full p-4 flex-shrink-0 transition-all duration-300 shadow-2xl">
            @php
                $current = request()->route()?->getName() ?? '';
                $isInventoryManager = auth()->user()->role === 'inventory_manager';
            @endphp

            <nav class="space-y-0.5">

                @if ($isInventoryManager)

                    {{-- Inventory Manager Menu --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        إدارة المخزون
                    </div>

                    <a href="{{ route('orders.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'orders') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-file-alt w-4 text-center opacity-70"></i>
                        <span>الطلبات</span>
                    </a>

                    <a href="{{ route('concrete-mixes.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'concrete-mixes') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-flask w-4 text-center opacity-70"></i>
                        <span>الخلطات</span>
                    </a>

                    <a href="{{ route('inventory.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'inventory') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-boxes w-4 text-center opacity-70"></i>
                        <span>المخزون</span>
                    </a>

                    <a href="{{ route('equipment-tools.index') }}"
                        class="sidebar-link {{ request()->routeIs('equipment-tools.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-toolbox w-4 text-center opacity-70"></i>
                        <span>مخزون المعدات</span>
                    </a>

                    <a href="{{ route('supplier-purchases.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'supplier-purchases') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-shopping-cart w-4 text-center opacity-70"></i>
                        <span>المشتريات</span>
                    </a>
                @else
                    {{-- Dashboard (Visible to everyone) --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider">
                        الرئيسية
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link {{ str_starts_with($current, 'dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-chart-pie w-4 text-center opacity-70"></i>
                        <span>لوحة التحكم</span>
                    </a>

                    {{-- العمليات --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        العمليات
                    </div>

                    @foreach ([['العملاء', 'fa-users', 'customers'], ['الطلبات', 'fa-file-alt', 'orders'], ['الجدول الأسبوعي', 'fa-calendar-week', 'schedules']] as [$name, $icon, $route])
                        <a href="{{ route($route . '.index') }}"
                            class="sidebar-link {{ str_starts_with($current, $route) ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                            <i class="fas {{ $icon }} w-4 text-center opacity-70"></i>
                            <span>{{ $name }}</span>
                        </a>
                    @endforeach

                    <a href="{{ route('concrete-mixes.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'concrete-mixes') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-flask w-4 text-center opacity-70"></i>
                        <span>الخلطات</span>
                    </a>

                    {{-- المخزون والموردون --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        المخزون والموردون
                    </div>

                    @foreach ([['المخزون', 'fa-boxes', 'inventory'], ['الموردون', 'fa-truck', 'suppliers'], ['المشتريات', 'fa-shopping-cart', 'supplier-purchases'], ['السندات', 'fa-receipt', 'receipts']] as [$name, $icon, $route])
                        <a href="{{ route($route . '.index') }}"
                            class="sidebar-link {{ str_starts_with($current, $route) ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                            <i class="fas {{ $icon }} w-4 text-center opacity-70"></i>
                            <span>{{ $name }}</span>
                        </a>
                    @endforeach

                    {{-- المعدات --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        المعدات
                    </div>

                    @foreach ([['المعدات المملوكة', 'fa-cog', 'equipment'], ['المعدات المستأجرة', 'fa-tools', 'rentals'], ['مخزون المعدات', 'fa-toolbox', 'equipment-tools']] as [$name, $icon, $route])
                        <a href="{{ route($route . '.index') }}"
                            class="sidebar-link {{ request()->routeIs($route . '.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                            <i class="fas {{ $icon }} w-4 text-center opacity-70"></i>
                            <span>{{ $name }}</span>
                        </a>
                    @endforeach

                    {{-- الموارد البشرية --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        الموارد البشرية
                    </div>

                    @foreach ([['الموظفون', 'fa-user-tie', 'employees'], ['الحضور', 'fa-clock', 'attendance'], ['الرواتب', 'fa-money-bill-wave', 'payroll'], ['مساهمين رأس المال', 'fa-user-plus', 'contributors']] as [$name, $icon, $route])
                        <a href="{{ route($route . '.index') }}"
                            class="sidebar-link {{ str_starts_with($current, $route) ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                            <i class="fas {{ $icon }} w-4 text-center opacity-70"></i>
                            <span>{{ $name }}</span>
                        </a>
                    @endforeach

                    {{-- المالية --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        المالية
                    </div>

                    @foreach ([['الخزينة', 'fa-vault', 'treasury'], ['مدفوعات العملاء', 'fa-hand-holding-usd', 'customer-payments'], ['مدفوعات الموردين', 'fa-hand-holding-usd', 'supplier-payments'], ['الآجل والديون', 'fa-calendar-check', 'credits'], ['المصروفات', 'fa-file-invoice-dollar', 'expenses'], ['إيجار الأرض', 'fa-map-marker-alt', 'land-rent']] as [$name, $icon, $route])
                        <a href="{{ route($route . '.index') }}"
                            class="sidebar-link {{ str_starts_with($current, $route) ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                            <i class="fas {{ $icon }} w-4 text-center opacity-70"></i>
                            <span>{{ $name }}</span>
                        </a>
                    @endforeach

                    {{-- التقارير --}}
                    <div class="text-xs text-slate-500 px-3 py-2 uppercase tracking-wider mt-2">
                        التقارير
                    </div>

                    <a href="{{ route('neighboring-stations.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'neighboring-stations') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-industry w-4 text-center opacity-70"></i>
                        <span>المحطات المجاورة</span>
                    </a>

                    <a href="{{ route('reports.index') }}"
                        class="sidebar-link {{ str_starts_with($current, 'reports') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-slate-300 text-sm">
                        <i class="fas fa-chart-bar w-4 text-center opacity-70"></i>
                        <span>التقارير والإحصائيات</span>
                    </a>

                @endif

            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-6 overflow-auto page-enter">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert-success px-4 py-3 mb-6 flex items-center gap-3 animate-pulse-once">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error px-4 py-3 mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Live clock
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent =
                now.toLocaleDateString('ar-SA', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) +
                ' | ' + now.toLocaleTimeString('ar-SA');
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Sidebar toggle (mobile)
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            const sb = document.getElementById('sidebar');
            sb.classList.toggle('-translate-x-full');
            sb.classList.toggle('hidden');
        });

        // Auto-dismiss flash messages
        setTimeout(() => {
            document.querySelectorAll('.alert-success, .alert-error').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);
    </script>

    @stack('scripts')
</body>

</html>

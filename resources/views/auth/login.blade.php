<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة محطة الخرسانة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-[90px] h-[90px] bg-white p-1 rounded-2xl mb-4">
                <img src="{{ asset('logo.png') }}" alt="">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">نظام إدارة محطة الخرسانة</h1>
            <p class="text-slate-400 text-sm">قم بتسجيل الدخول للوصول إلى النظام</p>
        </div>

        <div class="bg-slate-800 rounded-2xl shadow-xl border border-slate-700 p-8">
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">
                        <i class="fas fa-envelope ml-1"></i> البريد الإلكتروني
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                        placeholder="أدخل البريد الإلكتروني">
                </div>

                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">
                        <i class="fas fa-lock ml-1"></i> كلمة المرور
                    </label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                        placeholder="أدخل كلمة المرور">
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-3 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>تسجيل الدخول</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>

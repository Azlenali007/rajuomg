<?php $csrfToken = \App\Core\CSRF::token(); ?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — OTP Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50/70 antialiased font-sans">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Brand Wordmark & Shield -->
        <a href="/services" class="inline-flex items-center gap-3 group">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-700 via-brand-600 to-indigo-500 text-white flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <div class="text-left">
                <span class="text-xl font-extrabold tracking-tight text-slate-900 block leading-none">OTP Doctor</span>
                <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-600 mt-1 block">SMS Verification Suite</span>
            </div>
        </a>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-slate-900">Welcome back</h2>
        <p class="mt-1 text-xs text-slate-500">Sign in to access your wallet and live verification numbers</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200/90 rounded-3xl sm:px-10">
            
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-4">
                <?= \App\Core\CSRF::field() ?>
                
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700">Email Address</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            autocomplete="email" 
                            placeholder="you@domain.com"
                            class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all shadow-xs"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                            class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all shadow-xs"
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-md shadow-brand-500/25 text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all transform hover:-translate-y-0.5"
                    >
                        Sign In to Account
                    </button>
                </div>
            </form>

            <!-- Demo Hint Box for Rapid Evaluation -->
            <div class="mt-6 p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                <span class="font-bold text-slate-800 block text-[10px] uppercase tracking-wider">Demo Quick Access:</span>
                <div>Super Admin: <code class="font-mono text-brand-700 font-bold select-all">admin@otpdoctor.in</code> / <code class="font-mono text-slate-700 select-all">Admin1234!</code></div>
                <div>User Account: <code class="font-mono text-brand-700 font-bold select-all">demo@example.com</code> / <code class="font-mono text-slate-700 select-all">User1234!</code></div>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center">
                <p class="text-xs text-slate-600">
                    Need a new account? <a href="/register" class="font-bold text-brand-600 hover:text-brand-700">Create one free</a>
                </p>
                <p class="mt-2 text-xs">
                    <a href="/services" class="text-slate-400 hover:text-slate-700">← Return to Services Marketplace</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

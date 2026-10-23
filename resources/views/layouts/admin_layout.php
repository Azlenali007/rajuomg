<?php
use App\Core\Auth;
$currentUser = Auth::user();
$csrfToken = \App\Core\CSRF::token();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title>Super Admin Suite — OTP Doctor</title>
    
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-full flex text-slate-800 bg-slate-50 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <!-- Admin Sidebar (260px) -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 min-h-screen border-r border-slate-800">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3">
            <span class="w-7 h-7 rounded-md bg-brand-600 text-white flex items-center justify-center font-bold text-xs">OD</span>
            <div>
                <h1 class="text-sm font-bold text-white tracking-tight">OTP Doctor</h1>
                <p class="text-[10px] text-brand-400 font-medium">SUPER ADMIN CONSOLE</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm font-medium">
            <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard Overview</span>
            </a>
            <a href="/admin/providers" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Provider API & Sync</span>
            </a>
            <a href="/admin/services" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span>Services & Pricing</span>
            </a>
            <a href="/admin/orders" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Orders Monitoring</span>
            </a>
            <a href="/admin/payments" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Payment Gateways & QR</span>
            </a>
            <a href="/admin/users" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users & Ledgers</span>
            </a>
        </nav>

        <!-- Exit / Return -->
        <div class="p-4 border-t border-slate-800 space-y-2">
            <a href="/dashboard" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Return to User Marketplace</span>
            </a>
            <a href="/logout" class="block text-center py-1.5 px-3 rounded bg-slate-800 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Admin Viewport -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Breadcrumb Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-30">
            <div class="text-sm font-semibold text-slate-800">
                Super Admin Console
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-mono">UTC: <?= gmdate('Y-m-d H:i') ?></span>
                <span aria-hidden="true" class="text-slate-300">·</span>
                <span class="text-xs font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded border border-brand-200">Single Super Admin</span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
            <?= $content ?>
        </main>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>

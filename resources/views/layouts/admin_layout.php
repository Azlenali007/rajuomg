<?php
use App\Core\Auth;
$currentUser = Auth::user();
$csrfToken = \App\Core\CSRF::token();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
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
                            200: '#ddd6fe',
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
    <!-- Chart.js for real-time visual trends -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-full flex flex-col md:flex-row text-slate-800 bg-slate-50 antialiased font-sans" x-data="{ mobileSidebar: false }">

    <!-- Mobile Top Header -->
    <div class="md:hidden bg-slate-900 border-b border-slate-800 px-4 py-3 flex items-center justify-between z-40">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-md">OD</span>
            <span class="font-bold text-white text-sm">Super Admin Console</span>
        </div>
        <button @click="mobileSidebar = !mobileSidebar" class="p-2 text-slate-400 hover:text-white rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Admin Sidebar (260px) -->
    <aside 
        :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-300 flex flex-col shrink-0 min-h-screen border-r border-slate-800 transition-transform duration-200 ease-in-out"
    >
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/80">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-extrabold text-xs shadow-md shadow-brand-500/20">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight leading-none">OTP Doctor</h1>
                    <span class="text-[10px] text-brand-400 font-bold uppercase tracking-wider">Super Admin</span>
                </div>
            </div>
            <button @click="mobileSidebar = false" class="md:hidden text-slate-400 hover:text-white">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links with Section Groups -->
        <nav class="flex-1 px-3 py-5 space-y-6 text-xs font-semibold overflow-y-auto">
            
            <!-- Group 1: Overview -->
            <div class="space-y-1">
                <span class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-500 block mb-1">Core Metrics</span>
                <a href="/admin/dashboard" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin', '/admin/dashboard', '/admin/dashboard.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin', '/admin/dashboard', '/admin/dashboard.php']) ? 'text-white' : 'text-brand-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>Dashboard Overview</span>
                    </div>
                    <?php if (in_array($currentUri, ['/admin', '/admin/dashboard', '/admin/dashboard.php'])): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Group 2: Operations -->
            <div class="space-y-1">
                <span class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-500 block mb-1">Operations</span>
                
                <a href="/admin/orders" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin/orders', '/admin/orders.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin/orders', '/admin/orders.php']) ? 'text-white' : 'text-emerald-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span>Orders Monitoring</span>
                    </div>
                </a>

                <a href="/admin/services" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin/services', '/admin/services.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin/services', '/admin/services.php']) ? 'text-white' : 'text-sky-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                        <span>Services & Pricing</span>
                    </div>
                </a>

                <a href="/admin/providers" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin/providers', '/admin/providers.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin/providers', '/admin/providers.php']) ? 'text-white' : 'text-amber-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="m8 19 4-4 4 4"/><path d="M12 15v8"/></svg>
                        <span>Provider API & Sync</span>
                    </div>
                </a>
            </div>

            <!-- Group 3: Finance & Users -->
            <div class="space-y-1">
                <span class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-500 block mb-1">Accounts & Finance</span>
                
                <a href="/admin/payments" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin/payments', '/admin/payments.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin/payments', '/admin/payments.php']) ? 'text-white' : 'text-purple-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span>Payment Gateways & QR</span>
                    </div>
                </a>

                <a href="/admin/users" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all <?= in_array($currentUri, ['/admin/users', '/admin/users.php']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/admin/users', '/admin/users.php']) ? 'text-white' : 'text-teal-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Users & Ledgers</span>
                    </div>
                </a>
            </div>

        </nav>

        <!-- Exit / Return Section -->
        <div class="p-4 border-t border-slate-800/80 space-y-2">
            <a href="/services" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-900 transition-colors">
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                <span>Return to Marketplace</span>
            </a>
            <a href="/logout" class="block text-center py-2 px-3 rounded-xl bg-slate-900 text-xs font-bold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 border border-slate-800 transition-colors">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Admin Viewport -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Sticky Header -->
        <header class="h-16 glass-header border-b border-slate-200/80 flex items-center justify-between px-6 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <span class="text-sm font-extrabold text-slate-900 tracking-tight">Super Admin Console</span>
                <span class="text-xs text-slate-300">|</span>
                <span class="text-xs font-mono text-slate-500">UTC <?= gmdate('Y-m-d H:i') ?></span>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-[11px] font-bold text-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Single Super Admin</span>
                </div>
                <a href="/services" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>View Marketplace</span>
                </a>
            </div>
        </header>

        <!-- Main Content Canvas -->
        <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
            <?= $content ?>
        </main>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>

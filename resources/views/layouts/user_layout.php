<?php
use App\Core\Auth;
use App\Core\Session;
$currentUser = Auth::user();
$csrfToken = \App\Core\CSRF::token();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title>OTP Doctor — Premium SMS Verification Marketplace</title>
    
    <!-- Tailwind CSS (CDN configured with brand tokens) -->
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
                            300: '#c4b5fd',
                            400: '#a78bfa',
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
    <!-- Alpine.js & SweetAlert2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-full flex flex-col text-slate-800 bg-slate-50/80 antialiased font-sans selection:bg-brand-500 selection:text-white" x-data="{ mobileMenu: false, userDropdown: false }">

    <!-- Top Sticky Glassmorphic Navigation Bar -->
    <header class="glass-header border-b border-slate-200/80 sticky top-0 z-40 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-6">
                
                <!-- Zone 1: Distinctive Brand Mark -->
                <a href="/services" class="flex items-center gap-3 text-lg font-bold tracking-tight text-slate-900 group whitespace-nowrap shrink-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-indigo-500 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold tracking-tight bg-gradient-to-r from-slate-900 via-brand-900 to-slate-900 bg-clip-text text-transparent">OTP Doctor</span>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-600 leading-none">Verified Numbers</span>
                    </div>
                </a>

                <!-- Zone 2: Navigation Links with SVG Icons & Active Indicators -->
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                    <a href="/services" class="flex items-center gap-2 px-3.5 py-2 rounded-lg transition-colors <?= in_array($currentUri, ['/', '/services', '/dashboard', '/services.php']) ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'hover:text-slate-900 hover:bg-slate-100/70' ?>">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/', '/services', '/dashboard', '/services.php']) ? 'text-brand-600' : 'text-slate-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                        </svg>
                        <span>Services Catalog</span>
                    </a>

                    <a href="/orders" class="flex items-center gap-2 px-3.5 py-2 rounded-lg transition-colors <?= in_array($currentUri, ['/orders', '/order', '/orders.php', '/order.php']) ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'hover:text-slate-900 hover:bg-slate-100/70' ?>">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/orders', '/order', '/orders.php', '/order.php']) ? 'text-brand-600' : 'text-slate-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>My Orders</span>
                    </a>

                    <a href="/wallet" class="flex items-center gap-2 px-3.5 py-2 rounded-lg transition-colors <?= in_array($currentUri, ['/wallet', '/wallet.php']) ? 'bg-brand-50 text-brand-700 font-semibold shadow-xs' : 'hover:text-slate-900 hover:bg-slate-100/70' ?>">
                        <svg class="w-4 h-4 <?= in_array($currentUri, ['/wallet', '/wallet.php']) ? 'text-brand-600' : 'text-slate-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
                        </svg>
                        <span>Wallet & Top-up</span>
                    </a>

                    <?php if (Auth::isSuperAdmin()): ?>
                        <a href="/admin/dashboard" class="flex items-center gap-1.5 px-3 py-1.5 ml-2 rounded-lg bg-slate-900 text-amber-400 font-semibold text-xs hover:bg-slate-800 transition-colors shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>Super Admin Suite</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Zone 3: User Profile & Actions -->
                <div class="flex items-center gap-3 shrink-0">
                    <?php if ($currentUser): ?>
                        <!-- Wallet Quick Balance Pill -->
                        <a href="/wallet" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 hover:bg-emerald-100/80 transition-colors group">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-glow"></span>
                            <span class="text-xs text-emerald-800 font-medium">Balance:</span>
                            <span class="text-xs font-mono font-bold text-emerald-900 group-hover:underline">
                                $<?= number_format((float)($currentUser['wallet_balance'] ?? 0), 2) ?>
                            </span>
                            <span class="text-[10px] font-bold text-white bg-emerald-600 px-1.5 py-0.5 rounded-full">+</span>
                        </a>

                        <!-- User Profile Dropdown / Details -->
                        <div class="relative" @click.away="userDropdown = false">
                            <button @click="userDropdown = !userDropdown" class="flex items-center gap-2 p-1 pl-2 pr-3 text-xs font-medium text-slate-700 bg-white border border-slate-200 hover:border-slate-300 rounded-full shadow-xs transition-colors">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs">
                                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="hidden md:inline font-semibold text-slate-900 max-w-[100px] truncate"><?= htmlspecialchars($currentUser['name']) ?></span>
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userDropdown" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 text-xs">
                                <div class="px-3.5 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-900 truncate"><?= htmlspecialchars($currentUser['name']) ?></p>
                                    <p class="text-[11px] text-slate-500 truncate font-mono"><?= htmlspecialchars($currentUser['email']) ?></p>
                                </div>
                                <a href="/wallet" class="flex items-center gap-2 px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                                    <span>Recharge Wallet</span>
                                </a>
                                <a href="/orders" class="flex items-center gap-2 px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    <span>My Activation History</span>
                                </a>
                                <?php if (Auth::isSuperAdmin()): ?>
                                    <a href="/admin/dashboard" class="flex items-center gap-2 px-3.5 py-2 text-amber-700 bg-amber-50/50 hover:bg-amber-100/50 transition-colors font-medium">
                                        <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>Super Admin Console</span>
                                    </a>
                                <?php endif; ?>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="/logout" class="flex items-center gap-2 px-3.5 py-2 text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                    <svg class="w-4 h-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    <span>Sign Out</span>
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="/login" class="px-3.5 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 transition-colors">
                            Sign In
                        </a>
                        <a href="/register" class="px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 rounded-lg shadow-sm shadow-brand-500/20 transition-all transform hover:-translate-y-0.5 whitespace-nowrap">
                            Create Account
                        </a>
                    <?php endif; ?>

                    <!-- Mobile burger button -->
                    <button @click="mobileMenu = !mobileMenu" aria-label="Toggle mobile menu" class="md:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenu" x-transition.origin.top class="md:hidden border-t border-slate-200/80 bg-white/95 backdrop-blur-md px-4 pt-3 pb-5 space-y-1 shadow-lg">
            <a href="/services" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                <span>Services Catalog</span>
            </a>
            <a href="/orders" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>My Orders</span>
            </a>
            <a href="/wallet" class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                <span>Wallet & Payments</span>
            </a>
            <?php if (Auth::isSuperAdmin()): ?>
                <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-amber-700 bg-amber-50 rounded-lg">
                    <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span>Super Admin Panel</span>
                </a>
            <?php endif; ?>

            <?php if ($currentUser): ?>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between px-3">
                    <div class="text-xs">
                        <span class="text-slate-500">Logged in as</span>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($currentUser['name']) ?></p>
                    </div>
                    <a href="/logout" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 rounded-lg">Sign Out</a>
                </div>
            <?php else: ?>
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <a href="/login" class="text-center py-2 text-xs font-bold text-slate-700 bg-slate-100 rounded-lg">Sign In</a>
                    <a href="/register" class="text-center py-2 text-xs font-bold text-white bg-brand-600 rounded-lg">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?>
    </main>

    <!-- Premium Trust & Editorial Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-slate-100 text-xs">
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-600 text-white flex items-center justify-center font-bold text-xs">OD</div>
                        <span class="font-bold text-slate-900 text-sm">OTP Doctor</span>
                    </div>
                    <p class="text-slate-500 leading-relaxed">Authorized platform providing instant virtual temporary numbers for SMS verifications worldwide.</p>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-3">Service Guarantee</h4>
                    <ul class="space-y-2 text-slate-500">
                        <li class="flex items-center gap-1.5"><span class="text-emerald-500">✔</span> 15-Minute Auto-Refund</li>
                        <li class="flex items-center gap-1.5"><span class="text-emerald-500">✔</span> Zero Unused Charges</li>
                        <li class="flex items-center gap-1.5"><span class="text-emerald-500">✔</span> Dedicated Real Carrier Lines</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-3">Payment Systems</h4>
                    <ul class="space-y-2 text-slate-500">
                        <li class="flex items-center gap-1.5"><span class="text-brand-600">●</span> Instant Razorpay Cards / UPI</li>
                        <li class="flex items-center gap-1.5"><span class="text-brand-600">●</span> Manual QR & Admin Review</li>
                        <li class="flex items-center gap-1.5"><span class="text-brand-600">●</span> Immutable Ledger Logs</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-3">System Status</h4>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>All Verification Nodes Operational</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-mono">Response latency ~ 1.2s</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© <?= date('Y') ?> OTP Doctor Platform. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <span>Authorized Verification Services Only</span>
                    <span aria-hidden="true" class="text-slate-300">·</span>
                    <span>Single Super Admin Architecture</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>

<?php
use App\Core\Auth;
use App\Core\Session;
$currentUser = Auth::user();
$csrfToken = \App\Core\CSRF::token();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title>OTP Doctor — SMS Verification Marketplace</title>
    
    <!-- Tailwind CSS (CDN for standalone PHP server mode) -->
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
    <!-- Alpine.js & SweetAlert2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-full flex flex-col text-slate-800 bg-slate-50 antialiased font-sans" x-data="{ mobileMenu: false }">

    <!-- Top Navigation Bar (Strict 3-zone contract) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-8">
                
                <!-- Zone 1: Single text element wordmark -->
                <a href="/dashboard" class="flex items-center gap-2 text-lg font-bold tracking-tight text-brand-700 whitespace-nowrap shrink-0">
                    <span class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center font-extrabold text-sm shadow-sm">OD</span>
                    <span>OTP Doctor</span>
                </a>

                <!-- Zone 2: 4-5 clean single-line nav links -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                    <a href="/services" class="hover:text-brand-600 transition-colors whitespace-nowrap shrink-0">Services Catalog</a>
                    <a href="/orders" class="hover:text-brand-600 transition-colors whitespace-nowrap shrink-0">My Orders</a>
                    <a href="/wallet" class="hover:text-brand-600 transition-colors whitespace-nowrap shrink-0">Wallet & Payments</a>
                    <?php if (Auth::isSuperAdmin()): ?>
                        <a href="/admin/dashboard" class="text-brand-700 font-semibold hover:text-brand-800 whitespace-nowrap shrink-0">Super Admin Panel</a>
                    <?php endif; ?>
                </nav>

                <!-- Zone 3: 1 primary action / account profile -->
                <div class="flex items-center gap-4 shrink-0">
                    <?php if ($currentUser): ?>
                        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-600">
                            <span class="font-medium text-slate-900"><?= htmlspecialchars($currentUser['name']) ?></span>
                            <span aria-hidden="true" class="text-slate-300">·</span>
                            <a href="/wallet" class="font-mono tabular-nums font-semibold text-brand-700 hover:underline">Recharge</a>
                        </div>
                        <a href="/logout" class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors whitespace-nowrap shrink-0">
                            Sign Out
                        </a>
                    <?php else: ?>
                        <a href="/login" class="px-4 py-2 text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors whitespace-nowrap shrink-0">
                            Sign In
                        </a>
                    <?php endif; ?>

                    <!-- Mobile burger button -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-slate-500 hover:text-slate-800 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile drawer -->
        <div x-show="mobileMenu" class="md:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-4 space-y-1">
            <a href="/services" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-brand-50 rounded-md">Services</a>
            <a href="/orders" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-brand-50 rounded-md">My Orders</a>
            <a href="/wallet" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-brand-50 rounded-md">Wallet</a>
            <?php if (Auth::isSuperAdmin()): ?>
                <a href="/admin/dashboard" class="block px-3 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50 rounded-md">Super Admin Panel</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?>
    </main>

    <!-- Quiet Editorial Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <p>© <?= date('Y') ?> OTP Doctor Platform. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <span>Authorized SMS Verification Service</span>
                <span aria-hidden="true">·</span>
                <span>15-Minute Unused Number Auto-Refund</span>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>

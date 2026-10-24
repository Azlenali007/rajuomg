<div x-data="servicesMarketplace()" class="space-y-8">

    <!-- Premium Hero Carousel / Feature Slider -->
    <div 
        class="relative overflow-hidden rounded-2xl shadow-xl bg-slate-900 border border-slate-800 text-white"
        @mouseenter="stopAutoplay()" 
        @mouseleave="startAutoplay()"
    >
        <!-- Slide Container -->
        <div class="relative min-h-[300px] sm:min-h-[280px] flex items-center transition-all duration-500">
            
            <!-- Slide 1: Fast Instant Delivery -->
            <div 
                x-show="activeSlide === 0" 
                x-transition:enter="transition ease-out duration-400"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-8"
                class="w-full p-6 sm:p-10 lg:p-12 bg-gradient-to-r from-slate-950 via-brand-950 to-indigo-950 flex flex-col md:flex-row items-center justify-between gap-8"
            >
                <div class="max-w-xl space-y-4 text-center md:text-left z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                        <span>Lightning Carrier Route</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Instant SMS Verification for <span class="bg-gradient-to-r from-brand-300 via-indigo-200 to-emerald-300 bg-clip-text text-transparent">500+ Platforms</span>
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed">
                        Dedicated real-carrier SIM numbers for WhatsApp, Telegram, Google, ChatGPT, Instagram, and more. Codes arrive in seconds.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="#services-list" class="px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 rounded-xl shadow-lg shadow-brand-600/30 transition-all transform hover:-translate-y-0.5">
                            Browse Services ↓
                        </a>
                        <a href="/wallet" class="px-5 py-2.5 text-xs font-semibold text-slate-200 bg-white/10 hover:bg-white/20 border border-white/15 rounded-xl transition-all">
                            Add Funds & Top-up
                        </a>
                    </div>
                </div>

                <!-- Slide 1 Decorative SVG Illustration -->
                <div class="hidden md:flex items-center justify-center shrink-0 relative">
                    <div class="w-64 h-56 relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-brand-500/20 rounded-full blur-2xl"></div>
                        <!-- Phone Frame SVG -->
                        <div class="relative bg-slate-900 border-2 border-slate-700 rounded-3xl p-4 shadow-2xl w-48 text-center space-y-3">
                            <div class="w-10 h-1 bg-slate-700 rounded-full mx-auto mb-2"></div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div class="text-[11px] font-mono text-slate-400">+1 (415) ••• 2981</div>
                            <div class="bg-brand-950/80 border border-brand-500/40 rounded-xl p-2.5 text-left space-y-1 shadow-inner">
                                <span class="text-[10px] text-brand-300 font-bold block">SMS Verification</span>
                                <span class="text-xs font-mono font-bold text-white tracking-widest bg-brand-600/60 px-2 py-0.5 rounded inline-block">749-204</span>
                            </div>
                            <div class="text-[10px] text-emerald-400 font-semibold flex items-center justify-center gap-1">
                                <span>✔ Delivered in 3.4s</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2: 15-Minute Auto-Refund Guarantee -->
            <div 
                x-show="activeSlide === 1" 
                x-transition:enter="transition ease-out duration-400"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-8"
                class="w-full p-6 sm:p-10 lg:p-12 bg-gradient-to-r from-slate-950 via-emerald-950 to-slate-900 flex flex-col md:flex-row items-center justify-between gap-8"
            >
                <div class="max-w-xl space-y-4 text-center md:text-left z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Zero Risk Assurance</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        100% Unused Number <span class="text-emerald-400">Automated Refunds</span>
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed">
                        If an SMS verification code does not arrive within the 15-minute validity window, your wallet balance is immediately returned to your account with zero deduction.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="/wallet" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg shadow-emerald-600/30 transition-all transform hover:-translate-y-0.5">
                            Recharge Balance
                        </a>
                        <a href="/orders" class="px-5 py-2.5 text-xs font-semibold text-slate-200 bg-white/10 hover:bg-white/20 border border-white/15 rounded-xl transition-all">
                            View Order History
                        </a>
                    </div>
                </div>

                <!-- Slide 2 Decorative SVG -->
                <div class="hidden md:flex items-center justify-center shrink-0">
                    <div class="w-56 h-56 rounded-2xl bg-slate-900/80 border border-emerald-500/30 p-6 flex flex-col items-center justify-center text-center space-y-3 shadow-2xl relative">
                        <div class="absolute inset-0 bg-emerald-500/10 rounded-2xl blur-xl"></div>
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 flex items-center justify-center text-2xl font-bold shadow-lg">
                            <svg class="w-8 h-8 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <span class="text-sm font-bold text-white">Full Cost Refund</span>
                        <p class="text-xs text-slate-400">Atomic ledger rollback with zero human intervention required.</p>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Global Coverage -->
            <div 
                x-show="activeSlide === 2" 
                x-transition:enter="transition ease-out duration-400"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-8"
                class="w-full p-6 sm:p-10 lg:p-12 bg-gradient-to-r from-slate-950 via-sky-950 to-indigo-950 flex flex-col md:flex-row items-center justify-between gap-8"
            >
                <div class="max-w-xl space-y-4 text-center md:text-left z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-300 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span>Worldwide Carrier Network</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Global Carrier Nodes in <span class="text-sky-300">50+ Countries</span>
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed">
                        Access phone numbers with local country area codes for India (+91), USA (+1), UK (+44), Indonesia (+62), and worldwide regions.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="#country-selector" class="px-5 py-2.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-lg shadow-sky-600/30 transition-all transform hover:-translate-y-0.5">
                            Filter by Country
                        </a>
                    </div>
                </div>

                <!-- Slide 3 Decorative SVG -->
                <div class="hidden md:flex items-center justify-center shrink-0">
                    <div class="grid grid-cols-2 gap-3 p-4 bg-slate-900/60 border border-sky-500/20 rounded-2xl shadow-2xl">
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10 text-center">
                            <span class="text-xl">🇮🇳</span>
                            <div class="text-xs font-bold text-white mt-1">India</div>
                            <span class="text-[10px] font-mono text-sky-400">+91 routes</span>
                        </div>
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10 text-center">
                            <span class="text-xl">🇺🇸</span>
                            <div class="text-xs font-bold text-white mt-1">USA</div>
                            <span class="text-[10px] font-mono text-sky-400">+1 routes</span>
                        </div>
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10 text-center">
                            <span class="text-xl">🇬🇧</span>
                            <div class="text-xs font-bold text-white mt-1">UK</div>
                            <span class="text-[10px] font-mono text-sky-400">+44 routes</span>
                        </div>
                        <div class="p-3 bg-white/5 rounded-xl border border-white/10 text-center">
                            <span class="text-xl">🇮🇩</span>
                            <div class="text-xs font-bold text-white mt-1">Indonesia</div>
                            <span class="text-[10px] font-mono text-sky-400">+62 routes</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Slider Navigation Controls -->
        <!-- Prev Button -->
        <button 
            @click="prevSlide()" 
            aria-label="Previous slide"
            class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/60 hover:bg-slate-800 text-white flex items-center justify-center border border-white/10 backdrop-blur-sm transition-colors"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>

        <!-- Next Button -->
        <button 
            @click="nextSlide()" 
            aria-label="Next slide"
            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-slate-900/60 hover:bg-slate-800 text-white flex items-center justify-center border border-white/10 backdrop-blur-sm transition-colors"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>

        <!-- Pagination Dots -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2">
            <template x-for="(slide, index) in totalSlides" :key="index">
                <button 
                    @click="activeSlide = index" 
                    :aria-label="'Go to slide ' + (index + 1)"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="activeSlide === index ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                ></button>
            </template>
        </div>
    </div>

    <!-- Quick Stats & Guarantees Ticker Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Instant Activation</span>
                <span class="text-[11px] text-slate-500">Auto-allocated in &lt; 3s</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">15-Min Protection</span>
                <span class="text-[11px] text-slate-500">100% refund if unused</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Global Reach</span>
                <span class="text-[11px] text-slate-500">50+ countries active</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 sm:p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Instant Wallet</span>
                <span class="text-[11px] text-slate-500">Razorpay Cards & UPI</span>
            </div>
        </div>
    </div>

    <!-- Marketplace Filter & Selection Header -->
    <div id="services-list" class="space-y-4 pt-2">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Available Verification Services</h2>
                <p class="text-xs text-slate-500">Select your target application to generate a live temporary verification number.</p>
            </div>

            <!-- Country Selection -->
            <div class="flex items-center gap-2 self-start sm:self-auto" id="country-selector">
                <label for="country-select" class="text-xs font-semibold text-slate-600 whitespace-nowrap">Country:</label>
                <div class="relative">
                    <select 
                        id="country-select"
                        x-model="selectedCountry" 
                        class="pl-3 pr-8 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-xs appearance-none cursor-pointer"
                    >
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['country_code'] === 'in' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['country_name']) ?> (<?= htmlspecialchars($c['dial_code'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Tabs & Real-Time Search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
            
            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                <button 
                    @click="selectedCategory = 'all'" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                    :class="selectedCategory === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    All Services
                </button>
                <button 
                    @click="selectedCategory = 'messaging'" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                    :class="selectedCategory === 'messaging' ? 'bg-brand-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Messaging & Chat
                </button>
                <button 
                    @click="selectedCategory = 'social'" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                    :class="selectedCategory === 'social' ? 'bg-brand-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Social Media
                </button>
                <button 
                    @click="selectedCategory = 'ai'" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                    :class="selectedCategory === 'ai' ? 'bg-brand-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    AI & Developer
                </button>
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full md:w-72">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input 
                    type="text" 
                    x-model="search" 
                    placeholder="Search WhatsApp, Telegram, Google..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all"
                >
            </div>

        </div>

    </div>

    <!-- Services Grid with Colorful Brand SVG Icons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($services as $srv): 
            $code = strtolower($srv['service_code']);
            $name = $srv['service_name'];
            $price = (float)$srv['custom_price'];

            // Determine service category tag for filtering
            $cat = 'social';
            if (in_array($code, ['wa', 'tg', 'ds', 'vb'])) $cat = 'messaging';
            elseif (in_array($code, ['oa', 'go', 'ms', 'gh'])) $cat = 'ai';
            elseif (in_array($code, ['am', 'nf', 'st', 'tt'])) $cat = 'social';
        ?>
            <div 
                x-show="matchesFilters('<?= addslashes(strtolower($name)) ?>', '<?= addslashes($code) ?>', '<?= $cat ?>')"
                class="group bg-white border border-slate-200/90 rounded-2xl p-5 flex flex-col justify-between hover:border-brand-400 hover:shadow-xl hover:shadow-brand-500/5 transition-all duration-200 transform hover:-translate-y-1 relative"
            >
                <div>
                    <!-- Top Icon + Stock Badge -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        
                        <!-- Colorful Premium SVG Icon Container -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center p-2.5 shadow-xs transition-transform group-hover:scale-110
                            <?php if ($code === 'wa'): ?> bg-emerald-50 text-emerald-600 border border-emerald-100
                            <?php elseif ($code === 'tg'): ?> bg-sky-50 text-sky-500 border border-sky-100
                            <?php elseif ($code === 'go'): ?> bg-red-50 text-red-500 border border-red-100
                            <?php elseif ($code === 'ig'): ?> bg-pink-50 text-pink-600 border border-pink-100
                            <?php elseif ($code === 'ds'): ?> bg-indigo-50 text-indigo-600 border border-indigo-100
                            <?php elseif ($code === 'oa'): ?> bg-teal-50 text-teal-600 border border-teal-100
                            <?php elseif ($code === 'tw'): ?> bg-slate-100 text-slate-800 border border-slate-200
                            <?php elseif ($code === 'ms'): ?> bg-blue-50 text-blue-600 border border-blue-100
                            <?php elseif ($code === 'tt'): ?> bg-rose-50 text-rose-600 border border-rose-100
                            <?php elseif ($code === 'am'): ?> bg-amber-50 text-amber-600 border border-amber-100
                            <?php else: ?> bg-brand-50 text-brand-600 border border-brand-100
                            <?php endif; ?>
                        ">
                            <?php if ($code === 'wa'): ?>
                                <!-- WhatsApp SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.33C9.33 7.33 9 7.4 8.75 7.68C8.49 7.95 7.78 8.62 7.78 9.97C7.78 11.32 8.77 12.63 8.9 12.81C9.04 13 10.84 15.77 13.59 16.96C14.24 17.24 14.75 17.42 15.15 17.55C15.81 17.76 16.41 17.73 16.89 17.66C17.42 17.58 18.52 17 18.75 16.35C18.98 15.71 18.98 15.16 18.91 15.05C18.84 14.94 18.66 14.88 18.39 14.74C18.12 14.61 16.8 13.96 16.55 13.87C16.31 13.78 16.13 13.74 15.96 14.01C15.78 14.28 15.28 14.88 15.12 15.05C14.97 15.23 14.82 15.25 14.55 15.12C14.28 14.99 13.42 14.7 12.4 13.79C11.61 13.08 11.08 12.21 10.92 11.94C10.77 11.67 10.9 11.52 11.04 11.39C11.16 11.27 11.31 11.08 11.45 10.92C11.59 10.76 11.63 10.65 11.72 10.46C11.82 10.28 11.77 10.12 11.7 9.99C11.63 9.85 11.11 8.57 10.89 8.04C10.68 7.53 10.46 7.6 10.3 7.59C10.15 7.59 9.98 7.58 9.81 7.58L9.53 7.33Z"/>
                                </svg>
                            <?php elseif ($code === 'tg'): ?>
                                <!-- Telegram SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.52 2.77-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                </svg>
                            <?php elseif ($code === 'go'): ?>
                                <!-- Google SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.15z"/>
                                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.93 6.72-4.93z"/>
                                </svg>
                            <?php elseif ($code === 'ig'): ?>
                                <!-- Instagram SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            <?php elseif ($code === 'ds'): ?>
                                <!-- Discord SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                            <?php elseif ($code === 'oa'): ?>
                                <!-- OpenAI / ChatGPT SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.771-4.205 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.746-7.074zm-9.022 12.608a4.475 4.475 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zm-9.66-4.24a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.762zm-1.09-10.45a4.482 4.482 0 0 1 2.345-1.97v5.679a.795.795 0 0 0 .392.682l5.839 3.368-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786a4.504 4.504 0 0 1-1.655-6.141zm15.82 4.498l-5.838-3.37 2.02-1.164a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.684zm2.01-3.023l-.141-.085-4.779-2.782a.776.776 0 0 0-.785 0L8.832 9.736V7.404a.08.08 0 0 1 .032-.062l4.896-2.828a4.5 4.5 0 0 1 6.452 3.896v.006zM8.308 13.2l2.87-1.656 2.868 1.656v3.313l-2.868 1.656-2.87-1.656z"/>
                                </svg>
                            <?php elseif ($code === 'tw'): ?>
                                <!-- Twitter/X SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            <?php elseif ($code === 'ms'): ?>
                                <!-- Microsoft SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24">
                                    <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                                    <rect x="13" y="1" width="10" height="10" fill="#7FBA00"/>
                                    <rect x="1" y="13" width="10" height="10" fill="#00A4EF"/>
                                    <rect x="13" y="13" width="10" height="10" fill="#FFB900"/>
                                </svg>
                            <?php elseif ($code === 'tt'): ?>
                                <!-- TikTok SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                            <?php elseif ($code === 'am'): ?>
                                <!-- Amazon SVG -->
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13.918 10.873c-.09-.344-.242-.644-.456-.9a2.316 2.316 0 0 0-.776-.566 2.658 2.658 0 0 0-1.056-.202c-.524 0-.974.1-1.351.3a2.027 2.027 0 0 0-.825.807 2.455 2.455 0 0 0-.295 1.189c0 .493.096.906.289 1.238.192.333.456.58.79.743.336.162.72.244 1.155.244.542 0 .99-.102 1.344-.306.353-.204.624-.48.813-.827.189-.346.284-.737.284-1.173 0-.173-.013-.356-.037-.549zm5.302 6.558a22.58 22.58 0 0 1-5.185 1.588 23.327 23.327 0 0 1-5.467.433c-1.895-.083-3.692-.544-5.393-1.385a.55.55 0 0 1-.226-.708.574.574 0 0 1 .68-.261c1.603.74 3.29 1.15 5.06 1.23 1.77.08 3.52-.08 5.25-.48 1.45-.34 2.8-.82 4.05-1.44a.43.43 0 0 1 .49.12.44.44 0 0 1 .05.51l-.31.393zM21.2 16.03c-.19.24-.46.33-.7.24-.76-.29-1.54-.53-2.34-.73a.47.47 0 0 1-.36-.45.47.47 0 0 1 .31-.46c.72-.25 1.45-.44 2.19-.57a.47.47 0 0 1 .53.37c.07.56.2 1.11.37 1.6z"/>
                                </svg>
                            <?php else: ?>
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <!-- Availability Indicator -->
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[11px] font-semibold">
                            <span class="w-2 h-2 rounded-full <?= ($srv['stock_state'] ?? 'AVAILABLE') === 'AVAILABLE' ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' ?>"></span>
                            <span class="<?= ($srv['stock_state'] ?? 'AVAILABLE') === 'AVAILABLE' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= ($srv['stock_state'] ?? 'AVAILABLE') === 'AVAILABLE' ? 'In Stock' : 'Low Stock' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Service Titles -->
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-700 transition-colors">
                        <?= htmlspecialchars($name) ?>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                        <span class="font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold"><?= htmlspecialchars($code) ?></span>
                        <span>· Real SIM Line</span>
                    </p>
                </div>

                <!-- Bottom Pricing & Action Trigger -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Rate / SMS</span>
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-xs font-bold text-slate-400">$</span>
                            <span class="text-lg font-extrabold font-mono tabular-nums text-slate-900">
                                <?= number_format($price, 2) ?>
                            </span>
                        </div>
                    </div>

                    <button 
                        @click="buyNumber(<?= $srv['id'] ?>, '<?= addslashes($name) ?>', <?= $price ?>)"
                        :disabled="loading"
                        class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 active:scale-95 disabled:opacity-50 rounded-xl shadow-xs shadow-brand-500/20 hover:shadow-md hover:shadow-brand-500/25 transition-all whitespace-nowrap flex items-center gap-1.5"
                    >
                        <span>Get Number</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
function servicesMarketplace() {
    return {
        search: '',
        selectedCategory: 'all',
        selectedCountry: <?= !empty($countries[0]['id']) ? (int)$countries[0]['id'] : 1 ?>,
        loading: false,
        isLoggedIn: <?= \App\Core\Auth::check() ? 'true' : 'false' ?>,
        
        // Slider State
        activeSlide: 0,
        totalSlides: 3,
        sliderTimer: null,

        init() {
            this.startAutoplay();
        },

        startAutoplay() {
            this.stopAutoplay();
            this.sliderTimer = setInterval(() => {
                this.nextSlide();
            }, 6000);
        },

        stopAutoplay() {
            if (this.sliderTimer) {
                clearInterval(this.sliderTimer);
                this.sliderTimer = null;
            }
        },

        nextSlide() {
            this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
        },

        prevSlide() {
            this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
        },

        matchesFilters(name, code, category) {
            // Category check
            if (this.selectedCategory !== 'all' && category !== this.selectedCategory) {
                return false;
            }

            // Search query check
            if (!this.search.trim()) return true;
            const term = this.search.toLowerCase().trim();
            return name.includes(term) || code.includes(term);
        },

        async buyNumber(serviceId, serviceName, price) {
            if (!this.isLoggedIn) {
                const prompt = await Swal.fire({
                    title: 'Sign In Required',
                    text: `Please sign in or create an account to order temporary numbers for ${serviceName}.`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#7c3aed',
                    confirmButtonText: 'Sign In Now',
                    cancelButtonText: 'Create Account',
                    background: '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
                if (prompt.isConfirmed) {
                    window.location.href = '/login';
                } else if (prompt.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = '/register';
                }
                return;
            }

            const confirm = await Swal.fire({
                title: 'Order Phone Number?',
                html: `
                    <div class="text-left text-xs text-slate-600 space-y-2 mt-2">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div>Service: <strong class="text-slate-900">${serviceName}</strong></div>
                            <div>Cost: <strong class="text-brand-700 font-mono font-bold">$${price.toFixed(2)}</strong></div>
                            <div>Validity: <span class="font-semibold text-emerald-700">15 Minutes</span></div>
                        </div>
                        <p class="text-[11px] text-slate-500">🛡️ If no SMS arrives, 100% of the funds are instantly refunded to your wallet.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                confirmButtonText: 'Confirm & Acquire',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'rounded-2xl shadow-xl'
                }
            });

            if (!confirm.isConfirmed) return;

            this.loading = true;
            Swal.fire({
                title: 'Allocating Number...',
                text: 'Connecting to provider gateway and routing carrier SIM...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('service_id', serviceId);
                formData.append('country_id', this.selectedCountry);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/api/order/create', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Number Allocated!',
                        text: `Assigned: +${data.phone_number}. Redirecting to live OTP monitor...`,
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `/order?id=${data.order_id}`;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Allocation Failed',
                        text: data.message || 'Could not acquire number from provider.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to reach backend API.'
                });
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

<div x-data="servicesMarketplace()" class="space-y-6">

    <!-- Hero / Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">SMS Verification Services</h1>
            <p class="text-sm text-slate-500">Select a platform to receive an instant temporary verification number.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($wallet !== null): ?>
                <div class="bg-white border border-slate-200 rounded-lg px-4 py-2 flex items-center gap-3 shadow-sm">
                    <span class="text-xs text-slate-500">Wallet Balance:</span>
                    <span class="font-mono tabular-nums font-bold text-brand-700">$<?= number_format((float)($wallet['balance'] ?? 0), 2) ?></span>
                </div>
                <a href="/wallet" class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                    Add Funds
                </a>
            <?php else: ?>
                <a href="/login" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                    Sign In
                </a>
                <a href="/register" class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                    Register Free
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search Input -->
        <div class="relative md:col-span-2">
            <input 
                type="text" 
                x-model="search" 
                placeholder="Search services (e.g. WhatsApp, Telegram, Google, Discord)..."
                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
            >
        </div>

        <!-- Country Selector -->
        <div>
            <select 
                x-model="selectedCountry" 
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
            >
                <?php foreach ($countries as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['country_code'] === 'in' ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['country_name']) ?> (<?= htmlspecialchars($c['dial_code'] ?? '') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($services as $srv): ?>
            <div 
                x-show="matchesSearch('<?= addslashes(strtolower($srv['service_name'])) ?>', '<?= addslashes(strtolower($srv['service_code'])) ?>')"
                class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col justify-between hover:border-brand-300 hover:shadow-md transition-all"
            >
                <div>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                            <?= strtoupper(substr($srv['service_code'], 0, 2)) ?>
                        </div>
                        <span class="text-xs font-medium text-slate-500">
                            <?= ($srv['stock_state'] ?? 'AVAILABLE') === 'AVAILABLE' ? 'In Stock' : 'Low Stock' ?>
                        </span>
                    </div>

                    <h3 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($srv['service_name']) ?></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Code: <?= htmlspecialchars($srv['service_code']) ?></p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-400 block">Price per SMS</span>
                        <span class="text-base font-bold font-mono tabular-nums text-slate-900">
                            $<?= number_format((float)$srv['custom_price'], 2) ?>
                        </span>
                    </div>

                    <button 
                        @click="buyNumber(<?= $srv['id'] ?>, '<?= addslashes($srv['service_name']) ?>', <?= (float)$srv['custom_price'] ?>)"
                        :disabled="loading"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 disabled:opacity-50 rounded-lg shadow-sm transition-colors whitespace-nowrap"
                    >
                        Get Number
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
        selectedCountry: <?= !empty($countries[0]['id']) ? $countries[0]['id'] : 1 ?>,
        loading: false,
        isLoggedIn: <?= \App\Core\Auth::check() ? 'true' : 'false' ?>,

        matchesSearch(name, code) {
            if (!this.search.trim()) return true;
            const term = this.search.toLowerCase();
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
                    confirmButtonText: 'Sign In',
                    cancelButtonText: 'Create Account'
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
                text: `Acquire temporary number for ${serviceName} for $${price.toFixed(2)}? (Unused numbers are 100% refunded after 15 mins).`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                confirmButtonText: 'Confirm & Order',
                cancelButtonText: 'Cancel'
            });

            if (!confirm.isConfirmed) return;

            this.loading = true;
            Swal.fire({
                title: 'Allocating Number...',
                text: 'Contacting OTP Doctor provider node...',
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
                        text: `Assigned: ${data.phone_number}. Redirecting to live OTP monitor...`,
                        timer: 1500,
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

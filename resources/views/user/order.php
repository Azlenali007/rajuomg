<div x-data="orderLiveMonitor(<?= (int)$order['id'] ?>, '<?= $order['status'] ?>', '<?= addslashes($order['otp_code'] ?? '') ?>')" x-init="initTimer()" class="max-w-2xl mx-auto space-y-6">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="/orders" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-brand-600 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Back to My Orders</span>
        </a>
        <a href="/services" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
            Order Another Service →
        </a>
    </div>

    <!-- Main Order & Live Tracking Card -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6 relative overflow-hidden">
        
        <!-- Header status banner -->
        <div class="flex items-start justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 block">Activation Order #<?= htmlspecialchars($order['order_number']) ?></span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-0.5">
                    <?= htmlspecialchars($order['service_name']) ?>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                    <span>Region: <strong><?= htmlspecialchars($order['country_name'] ?? 'India') ?></strong></span>
                    <span>·</span>
                    <span>Purchased: <?= date('H:i · M d, Y', strtotime($order['created_at'])) ?></span>
                </p>
            </div>

            <!-- Status Pill/Badge -->
            <div class="text-right shrink-0">
                <span 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide"
                    :class="{
                        'bg-amber-50 text-amber-700 border border-amber-200': status === 'ACTIVE' || status === 'WAITING',
                        'bg-emerald-50 text-emerald-700 border border-emerald-200': status === 'RECEIVED' || status === 'COMPLETED',
                        'bg-rose-50 text-rose-700 border border-rose-200': status === 'CANCELLED' || status === 'EXPIRED'
                    }"
                >
                    <span class="w-2 h-2 rounded-full" :class="{
                        'bg-amber-500 animate-pulse': status === 'ACTIVE' || status === 'WAITING',
                        'bg-emerald-500': status === 'RECEIVED' || status === 'COMPLETED',
                        'bg-rose-500': status === 'CANCELLED' || status === 'EXPIRED'
                    }"></span>
                    <span x-text="statusText()"></span>
                </span>
                <div class="text-[11px] text-slate-400 mt-1 font-mono tabular-nums" x-show="status === 'ACTIVE' || status === 'WAITING'">
                    Window expires in: <strong class="text-slate-700" x-text="formatTime(timeRemaining)"></strong>
                </div>
            </div>
        </div>

        <!-- Phone Number Presentation Box -->
        <div class="bg-gradient-to-r from-slate-50 to-slate-100/80 border border-slate-200 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
                <span class="text-xs text-slate-500 block font-semibold">Dedicated Carrier Number</span>
                <span class="text-2xl sm:text-3xl font-extrabold font-mono tabular-nums text-slate-900 tracking-wider block mt-0.5 select-all">
                    +<?= htmlspecialchars($order['phone_number']) ?>
                </span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Enter this number into <?= htmlspecialchars($order['service_name']) ?> to request your code</span>
            </div>
            <button 
                @click="copyText('+<?= htmlspecialchars($order['phone_number']) ?>', 'Phone number copied!')" 
                class="px-5 py-2.5 text-xs font-bold text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl shadow-xs transition-all whitespace-nowrap flex items-center gap-2"
            >
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span>Copy Number</span>
            </button>
        </div>

        <!-- Live OTP Display Canvas -->
        <div 
            class="text-center py-8 px-6 rounded-2xl border-2 transition-all duration-300" 
            :class="otpCode ? 'bg-emerald-50/80 border-emerald-400 shadow-lg shadow-emerald-500/10' : 'bg-brand-50/40 border-dashed border-brand-200'"
        >
            <template x-if="!otpCode">
                <div class="space-y-4">
                    <div class="relative w-16 h-16 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-brand-500/20 animate-ping"></div>
                        <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center relative shadow-sm">
                            <svg class="w-7 h-7 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Waiting for incoming SMS code...</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 leading-relaxed">
                            Request the SMS verification on <?= htmlspecialchars($order['service_name']) ?> now. Our node checks for incoming carrier packets every 3 seconds.
                        </p>
                    </div>
                </div>
            </template>

            <template x-if="otpCode">
                <div class="space-y-4 py-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>SMS Code Delivered Successfully</span>
                    </div>

                    <div class="text-5xl sm:text-6xl font-black font-mono tracking-widest text-emerald-700 tabular-nums py-2 select-all drop-shadow-xs" x-text="otpCode"></div>

                    <div>
                        <button 
                            @click="copyText(otpCode, 'Verification code copied!')"
                            class="px-6 py-3 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-600/25 transition-all transform hover:-translate-y-0.5 inline-flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            <span>Copy Verification Code</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Actions / Cancellation & Refund -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-xs">
            <div class="text-slate-500">
                Order Charge: <strong class="font-mono font-bold text-slate-900">$<?= number_format((float)$order['price'], 2) ?></strong>
            </div>

            <template x-if="!otpCode && (status === 'ACTIVE' || status === 'WAITING')">
                <button 
                    @click="cancelOrder()"
                    class="px-4 py-2 font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition-all"
                >
                    Cancel & Refund Balance
                </button>
            </template>
        </div>

    </div>

    <!-- Session Details & Cancellation Notice Card -->
    <div class="text-xs text-slate-600 bg-white border border-slate-200/80 rounded-2xl p-5 flex items-start gap-3.5 shadow-xs">
        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div>
            <strong class="text-slate-900 block font-bold">Verification Session & Auto-Expiry Policy:</strong>
            <p class="text-slate-500 mt-0.5 leading-relaxed">
                If no SMS code arrives before the 15-minute timer elapses, you can cancel the request at any time or let it auto-expire. The allocated amount ($<?= number_format((float)$order['price'], 2) ?>) is returned to your account balance.
            </p>
        </div>
    </div>

</div>

<script>
function orderLiveMonitor(orderId, initialStatus, initialOtp) {
    return {
        orderId: orderId,
        status: initialStatus,
        otpCode: initialOtp,
        pollTimer: null,
        timeRemaining: 900,

        initTimer() {
            if (this.status === 'ACTIVE' || this.status === 'WAITING') {
                this.startPolling();
                setInterval(() => {
                    if (this.timeRemaining > 0 && !this.otpCode) {
                        this.timeRemaining--;
                    }
                }, 1000);
            }
        },

        statusText() {
            if (this.otpCode || this.status === 'RECEIVED' || this.status === 'COMPLETED') return 'OTP DELIVERED';
            if (this.status === 'CANCELLED') return 'CANCELLED & REFUNDED';
            if (this.status === 'EXPIRED') return 'EXPIRED & REFUNDED';
            return 'WAITING FOR SMS';
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        copyText(text, msg) {
            navigator.clipboard.writeText(text);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg || 'Copied to clipboard!',
                showConfirmButton: false,
                timer: 1500
            });
        },

        startPolling() {
            this.pollTimer = setInterval(async () => {
                if (this.otpCode) {
                    clearInterval(this.pollTimer);
                    return;
                }
                try {
                    const res = await fetch(`/api/order/status?id=${this.orderId}`);
                    const data = await res.json();
                    if (data.success) {
                        this.status = data.status;
                        if (data.otp_code) {
                            this.otpCode = data.otp_code;
                            clearInterval(this.pollTimer);
                            Swal.fire({
                                icon: 'success',
                                title: 'Code Received!',
                                text: `Your verification code is: ${data.otp_code}`,
                                confirmButtonColor: '#10b981'
                            });
                        }
                    }
                } catch (e) {
                    // Silent retry on next interval
                }
            }, 3000);
        },

        async cancelOrder() {
            const confirm = await Swal.fire({
                title: 'Cancel this number?',
                text: 'Order will be cancelled and the full price refunded to your wallet balance immediately.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Yes, Cancel & Refund',
                cancelButtonText: 'Keep Waiting'
            });

            if (!confirm.isConfirmed) return;

            try {
                const formData = new FormData();
                formData.append('order_id', this.orderId);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/api/order/cancel', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    this.status = 'CANCELLED';
                    clearInterval(this.pollTimer);
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled & Refunded',
                        text: 'Your balance has been restored.',
                        confirmButtonColor: '#7c3aed'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Could not cancel order.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to cancel order.'
                });
            }
        }
    };
}
</script>

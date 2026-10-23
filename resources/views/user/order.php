<div x-data="orderLiveMonitor(<?= (int)$order['id'] ?>, '<?= $order['status'] ?>', '<?= addslashes($order['otp_code'] ?? '') ?>')" x-init="initTimer()" class="max-w-2xl mx-auto space-y-6">

    <!-- Back Navigation -->
    <div>
        <a href="/orders" class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to My Orders</span>
        </a>
    </div>

    <!-- Main Order Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <!-- Header status banner -->
        <div class="flex items-center justify-between pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs font-semibold text-brand-700 uppercase tracking-wider block">Activation Order #<?= htmlspecialchars($order['order_number']) ?></span>
                <h1 class="text-xl font-bold text-slate-900 mt-0.5"><?= htmlspecialchars($order['service_name']) ?> (<?= htmlspecialchars($order['country_name']) ?>)</h1>
            </div>

            <!-- Status Pill/Badge -->
            <div class="text-right">
                <span 
                    class="inline-block px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide"
                    :class="{
                        'bg-amber-50 text-amber-700 border border-amber-200': status === 'ACTIVE' || status === 'WAITING',
                        'bg-emerald-50 text-emerald-700 border border-emerald-200': status === 'RECEIVED' || status === 'COMPLETED',
                        'bg-rose-50 text-rose-700 border border-rose-200': status === 'CANCELLED' || status === 'EXPIRED'
                    }"
                    x-text="statusText()"
                ></span>
                <span class="block text-[11px] text-slate-400 mt-1 font-mono tabular-nums" x-show="status === 'ACTIVE' || status === 'WAITING'">
                    Expires in: <strong x-text="formatTime(timeRemaining)"></strong>
                </span>
            </div>
        </div>

        <!-- Phone Number Presentation -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-500 block font-medium">Assigned Verification Phone Number</span>
                <span class="text-2xl font-bold font-mono tabular-nums text-slate-900 tracking-wider">
                    +<?= htmlspecialchars($order['phone_number']) ?>
                </span>
            </div>
            <button 
                @click="copyText('<?= htmlspecialchars($order['phone_number']) ?>')" 
                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg shadow-sm transition-colors whitespace-nowrap"
            >
                Copy Number
            </button>
        </div>

        <!-- Live OTP Display Area -->
        <div class="text-center py-6 px-4 rounded-xl border-2" :class="otpCode ? 'bg-emerald-50 border-emerald-300' : 'bg-brand-50/50 border-dashed border-brand-200'">
            <template x-if="!otpCode">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mx-auto animate-pulse">
                        <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">Listening for incoming SMS...</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Enter the number into <?= htmlspecialchars($order['service_name']) ?> now. We check the provider node every 3 seconds.</p>
                </div>
            </template>

            <template x-if="otpCode">
                <div class="space-y-3">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-widest block">SMS Verification Code Received</span>
                    <div class="text-4xl sm:text-5xl font-extrabold font-mono tracking-widest text-emerald-700 tabular-nums py-2" x-text="otpCode"></div>
                    <button 
                        @click="copyText(otpCode)"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors"
                    >
                        Copy OTP Code
                    </button>
                </div>
            </template>
        </div>

        <!-- Action / Cancellation & Refund -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <span class="text-xs text-slate-400">Order Cost: $<?= number_format((float)$order['price'], 2) ?></span>

            <template x-if="!otpCode && (status === 'ACTIVE' || status === 'WAITING')">
                <button 
                    @click="cancelOrder()"
                    class="px-4 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors"
                >
                    Cancel & Refund Balance
                </button>
            </template>
        </div>

    </div>

    <!-- Assurance Notice -->
    <div class="text-xs text-slate-500 bg-white border border-slate-200 rounded-xl p-4 flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        <div>
            <strong class="text-slate-800">100% Unused Number Protection:</strong> If no SMS arrives within the 15-minute countdown, the order automatically closes and the full purchase amount is refunded to your wallet ledger.
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

        startPolling() {
            this.pollTimer = setInterval(async () => {
                if (this.otpCode || this.status === 'CANCELLED' || this.status === 'EXPIRED') {
                    clearInterval(this.pollTimer);
                    return;
                }

                try {
                    const res = await fetch(`/api/order/status?id=${this.orderId}`);
                    const data = await res.json();

                    if (data.success) {
                        if (data.status === 'RECEIVED' && data.otp) {
                            this.otpCode = data.otp;
                            this.status = 'RECEIVED';
                            clearInterval(this.pollTimer);
                            Swal.fire({
                                icon: 'success',
                                title: 'OTP Received!',
                                text: `Your verification code is: ${data.otp}`,
                                confirmButtonColor: '#7c3aed'
                            });
                        } else if (data.status === 'CANCELLED' || data.status === 'EXPIRED') {
                            this.status = data.status;
                            clearInterval(this.pollTimer);
                        }
                    }
                } catch (e) {
                    console.log('Polling retry...');
                }
            }, 3000);
        },

        async copyText(text) {
            try {
                await navigator.clipboard.writeText(text);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copied to clipboard',
                    showConfirmButton: false,
                    timer: 1500
                });
            } catch (e) {
                // Fallback prompt
                prompt('Copy text:', text);
            }
        },

        async cancelOrder() {
            const confirm = await Swal.fire({
                title: 'Cancel this number?',
                text: 'Your balance will be refunded immediately to your wallet.',
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
                        text: data.message,
                        confirmButtonColor: '#7c3aed'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cancellation Failed',
                        text: data.message
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Network connection failed.'
                });
            }
        }
    };
}
</script>

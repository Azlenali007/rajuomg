<div x-data="walletManager()" class="space-y-8">

    <!-- Top Balance & Financial Summary Strip -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-xl text-white">
        <!-- Decorative Background Glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Atomic Ledger Guaranteed</span>
                </div>
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Current Available Balance</div>
                <div class="text-4xl sm:text-5xl font-black font-mono tabular-nums text-white flex items-baseline gap-1">
                    <span class="text-brand-400 text-3xl font-bold">$</span>
                    <span><?= number_format((float)($wallet['balance'] ?? 0), 2) ?></span>
                    <span class="text-xs font-mono text-slate-400 ml-2 font-normal">USD</span>
                </div>
                <p class="text-xs text-slate-300 max-w-md">
                    Instantly deducts when purchasing verification numbers. Automatic 100% refund rollback if SMS does not arrive within 15 minutes.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="/services" class="px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    <span>Browse Services Catalog</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Payment Gateways Section -->
    <div class="space-y-4">
        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Add Funds / Top-up Wallet</h2>
            <p class="text-xs text-slate-500">Choose your preferred deposit method. All top-ups are cryptographically logged into your ledger.</p>
        </div>

        <?php if (!$razorpay && !$manualQr): ?>
            <!-- Both Gateways OFF -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center text-amber-800 text-xs">
                <strong>Online top-ups temporarily unavailable:</strong> The administrator has temporarily disabled top-up methods for maintenance. Please check back shortly.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 1. Razorpay Payment Card (if enabled) -->
                <?php if ($razorpay): ?>
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-6 hover:border-brand-300 transition-all">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">Instant Card / UPI (Razorpay)</h3>
                                        <span class="text-[11px] text-slate-400">Cards, UPI, Netbanking & Wallets</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 whitespace-nowrap">
                                    INSTANT CREDIT
                                </span>
                            </div>

                            <div class="mt-6 space-y-3">
                                <label class="block text-xs font-bold text-slate-700">Enter Recharge Amount (USD)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-base">$</span>
                                    <input 
                                        type="number" 
                                        min="1" 
                                        step="1" 
                                        x-model.number="razorpayAmount" 
                                        class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-base font-mono tabular-nums font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all shadow-xs"
                                        placeholder="10.00"
                                    >
                                </div>

                                <!-- Quick Presets -->
                                <div class="flex items-center gap-2 pt-1">
                                    <button @click="razorpayAmount = 5" type="button" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-700 rounded-lg text-slate-700 transition-colors">$5</button>
                                    <button @click="razorpayAmount = 10" type="button" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-700 rounded-lg text-slate-700 transition-colors">$10</button>
                                    <button @click="razorpayAmount = 25" type="button" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-700 rounded-lg text-slate-700 transition-colors">$25</button>
                                    <button @click="razorpayAmount = 50" type="button" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-700 rounded-lg text-slate-700 transition-colors">$50</button>
                                    <button @click="razorpayAmount = 100" type="button" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-700 rounded-lg text-slate-700 transition-colors">$100</button>
                                </div>
                            </div>
                        </div>

                        <button 
                            @click="payWithRazorpay()"
                            :disabled="loading"
                            class="w-full py-3 px-4 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 active:scale-95 disabled:opacity-50 rounded-xl shadow-md shadow-brand-500/20 transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            <span>Proceed to Razorpay Checkout</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- 2. Manual UPI / QR Top-Up Card (if enabled) -->
                <?php if ($manualQr): ?>
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-6 hover:border-amber-300 transition-all">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="8" x="3" y="3" rx="1"/><rect width="8" height="8" x="13" y="3" rx="1"/><rect width="8" height="8" x="3" y="13" rx="1"/><path d="M14 14h3v3h-3z"/><path d="M20 14v3h-3"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">Manual UPI / QR Top-Up</h3>
                                        <span class="text-[11px] text-slate-400">Scan & pay via any UPI app</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200 whitespace-nowrap">
                                    ADMIN REVIEW
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                                <?= htmlspecialchars($manualQr['instructions'] ?? 'Scan QR code or send payment to our UPI ID. Submit your reference number below.') ?>
                            </p>

                            <div class="mt-4 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Official UPI ID:</span>
                                    <strong class="font-mono text-slate-900 font-bold select-all bg-white px-2 py-0.5 rounded border border-slate-200"><?= htmlspecialchars($manualQr['upi_id'] ?? 'pay@otpdoctor') ?></strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Minimum Deposit:</span>
                                    <strong class="font-mono text-slate-900 font-bold">$<?= number_format((float)($manualQr['min_amount'] ?? 2), 2) ?></strong>
                                </div>
                            </div>

                            <form action="/wallet/manual" method="POST" class="mt-4 space-y-3">
                                <?= \App\Core\CSRF::field() ?>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Amount Paid (USD)</label>
                                    <input type="number" step="0.01" min="1" name="amount" required class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="10.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">UTR / Transaction Reference ID</label>
                                    <input type="text" name="transaction_reference" required class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="e.g. 329847128912">
                                </div>
                                <button type="submit" class="w-full py-2.5 px-4 text-xs font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                                    Submit Manual Payment Proof
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>

    <!-- Immutable Wallet Ledger History -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Wallet Transaction Ledger</h2>
                <p class="text-xs text-slate-500">Cryptographically recorded financial transactions and automated refunds.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                <?= count($transactions) ?> Entries
            </span>
        </div>
        
        <?php if (empty($transactions)): ?>
            <div class="bg-white border border-slate-200/90 rounded-2xl p-10 text-center text-xs text-slate-500 shadow-xs">
                No ledger transactions recorded yet.
            </div>
        <?php else: ?>
            <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">Timestamp</th>
                                <th class="px-5 py-3.5">Action Type</th>
                                <th class="px-5 py-3.5">Description</th>
                                <th class="px-5 py-3.5">Reference ID</th>
                                <th class="px-5 py-3.5 text-right">Amount</th>
                                <th class="px-5 py-3.5 text-right">Balance After</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($transactions as $tx): 
                                $type = $tx['type'];
                                $isCredit = $type === 'credit' || $type === 'refund';
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3.5 text-slate-500 font-mono">
                                        <?= date('M d, Y · H:i', strtotime($tx['created_at'])) ?>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold uppercase">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-mono
                                            <?= $type === 'credit' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' ?>
                                            <?= $type === 'debit' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' ?>
                                            <?= $type === 'refund' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' ?>
                                            <?= $type === 'admin_adjustment' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' ?>
                                        ">
                                            <?= htmlspecialchars($type) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-800 font-medium">
                                        <?= htmlspecialchars($tx['description']) ?>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-slate-500 text-[11px]">
                                        <?= htmlspecialchars($tx['reference_id']) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold <?= $isCredit ? 'text-emerald-600' : 'text-slate-800' ?>">
                                        <?= $isCredit ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 2) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums text-slate-700 font-semibold">
                                        $<?= number_format((float)$tx['balance_after'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
function walletManager() {
    return {
        razorpayAmount: 10,
        loading: false,

        async payWithRazorpay() {
            if (this.razorpayAmount < 1) {
                Swal.fire('Error', 'Minimum recharge amount is $1.00', 'error');
                return;
            }

            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('amount', this.razorpayAmount);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/api/payment/razorpay/init', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) {
                    Swal.fire('Payment Error', data.message, 'error');
                    this.loading = false;
                    return;
                }

                const options = {
                    key: data.checkout_data.key,
                    amount: data.checkout_data.amount,
                    currency: data.checkout_data.currency,
                    name: data.checkout_data.name,
                    description: data.checkout_data.description,
                    order_id: data.checkout_data.order_id,
                    handler: async (response) => {
                        Swal.fire({
                            title: 'Verifying Signature...',
                            text: 'Validating server signature and crediting wallet ledger...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        const verifyForm = new FormData();
                        verifyForm.append('payment_reference', data.payment_reference);
                        verifyForm.append('razorpay_order_id', response.razorpay_order_id);
                        verifyForm.append('razorpay_payment_id', response.razorpay_payment_id);
                        verifyForm.append('razorpay_signature', response.razorpay_signature);
                        verifyForm.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                        const vRes = await fetch('/api/payment/razorpay/verify', {
                            method: 'POST',
                            body: verifyForm
                        });
                        const vData = await vRes.json();

                        if (vData.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Confirmed',
                                text: vData.message,
                                confirmButtonColor: '#7c3aed'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Verification Failed', vData.message, 'error');
                        }
                    },
                    prefill: data.checkout_data.prefill,
                    theme: data.checkout_data.theme
                };

                const rzp = new Razorpay(options);
                rzp.open();
            } catch (err) {
                Swal.fire('Error', 'Unable to initiate Razorpay checkout.', 'error');
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

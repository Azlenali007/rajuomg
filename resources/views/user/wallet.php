<div x-data="walletManager()" class="space-y-8">

    <!-- Top Balance & Summary Strip -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <span class="text-xs font-semibold text-brand-700 uppercase tracking-wider block">Wallet Balance Ledger</span>
            <div class="text-4xl font-extrabold font-mono tabular-nums text-slate-900 mt-1">
                $<?= number_format((float)($wallet['balance'] ?? 0), 2) ?>
            </div>
            <p class="text-xs text-slate-500 mt-1">Ledger-backed balance with atomic locking & instant automated refunds.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="/services" class="px-4 py-2.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
                Browse Services
            </a>
        </div>
    </div>

    <!-- Payment Gateways Section -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900">Add Funds / Top-up Wallet</h2>

        <?php if (!$razorpay && !$manualQr): ?>
            <!-- Both Gateways OFF -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center text-amber-800 text-sm">
                <strong>Online top-ups temporarily unavailable:</strong> The administrator has temporarily disabled top-up methods for maintenance. Please check back shortly.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 1. Razorpay Payment Card (if enabled) -->
                <?php if ($razorpay): ?>
                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900">Online Card / UPI (Razorpay)</h3>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">INSTANT CREDIT</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Pay with Credit/Debit Cards, UPI, Netbanking, or Wallets with automatic signature verification.</p>

                            <div class="mt-4 space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">Enter Amount (USD)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold">$</span>
                                    <input 
                                        type="number" 
                                        min="1" 
                                        step="1" 
                                        x-model.number="razorpayAmount" 
                                        class="w-full pl-8 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-mono tabular-nums font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                        placeholder="10.00"
                                    >
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    <button @click="razorpayAmount = 5" class="px-2 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 rounded text-slate-700">$5</button>
                                    <button @click="razorpayAmount = 10" class="px-2 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 rounded text-slate-700">$10</button>
                                    <button @click="razorpayAmount = 25" class="px-2 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 rounded text-slate-700">$25</button>
                                    <button @click="razorpayAmount = 50" class="px-2 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 rounded text-slate-700">$50</button>
                                </div>
                            </div>
                        </div>

                        <button 
                            @click="payWithRazorpay()"
                            :disabled="loading"
                            class="w-full py-2.5 px-4 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2"
                        >
                            <span>Pay with Razorpay Checkout</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- 2. Manual QR / Crypto Payment (if enabled) -->
                <?php if ($manualQr): ?>
                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900">Manual UPI / QR Top-Up</h3>
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">ADMIN REVIEW</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($manualQr['instructions'] ?? 'Scan QR code or send payment to our UPI ID. Submit your reference number below.') ?></p>

                            <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-1">
                                <div>UPI ID: <strong class="font-mono text-slate-900 select-all"><?= htmlspecialchars($manualQr['upi_id'] ?? 'pay@otpdoctor') ?></strong></div>
                                <div>Min Amount: <strong class="font-mono text-slate-900">$<?= number_format((float)($manualQr['min_amount'] ?? 2), 2) ?></strong></div>
                            </div>

                            <form action="/wallet/manual" method="POST" class="mt-4 space-y-3">
                                <?= \App\Core\CSRF::field() ?>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">Amount Paid (USD)</label>
                                    <input type="number" step="0.01" min="1" name="amount" required class="mt-1 w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold" placeholder="10.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">UTR / Transaction Reference ID</label>
                                    <input type="text" name="transaction_reference" required class="mt-1 w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono" placeholder="e.g. 329847128912">
                                </div>
                                <button type="submit" class="w-full py-2 px-4 text-xs font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
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
        <h2 class="text-lg font-bold text-slate-900">Wallet Transaction Ledger</h2>
        
        <?php if (empty($transactions)): ?>
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center text-xs text-slate-500">
                No ledger transactions recorded yet.
            </div>
        <?php else: ?>
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Balance After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($transactions as $tx): ?>
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 text-slate-500 font-mono">
                                    <?= date('M d, Y H:i', strtotime($tx['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 font-semibold uppercase">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px]
                                        <?= $tx['type'] === 'credit' ? 'bg-emerald-50 text-emerald-700' : '' ?>
                                        <?= $tx['type'] === 'debit' ? 'bg-rose-50 text-rose-700' : '' ?>
                                        <?= $tx['type'] === 'refund' ? 'bg-purple-50 text-purple-700' : '' ?>
                                        <?= $tx['type'] === 'admin_adjustment' ? 'bg-amber-50 text-amber-700' : '' ?>
                                    ">
                                        <?= htmlspecialchars($tx['type']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-800 font-medium">
                                    <?= htmlspecialchars($tx['description']) ?>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-500 text-[11px]">
                                    <?= htmlspecialchars($tx['reference_id']) ?>
                                </td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums font-bold <?= $tx['type'] === 'credit' || $tx['type'] === 'refund' ? 'text-emerald-600' : 'text-slate-800' ?>">
                                    <?= $tx['type'] === 'credit' || $tx['type'] === 'refund' ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 2) ?>
                                </td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums text-slate-600 font-semibold">
                                    $<?= number_format((float)$tx['balance_after'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

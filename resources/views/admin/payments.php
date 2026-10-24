<div class="space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Payment Gateways & Manual Queue</h1>
            <p class="text-xs text-slate-500">Review pending manual payments, inspect Razorpay transactions, and manage deposit channels.</p>
        </div>
    </div>

    <!-- Manual Payments Approval Queue -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Manual Payment Verification Queue</h2>
                <p class="text-xs text-slate-400">Incoming UPI / QR top-ups awaiting Super Admin ledger credit</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full <?= !empty($manualPending) ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-600' ?>">
                <?= count($manualPending) ?> Pending Approval
            </span>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Submission Date</th>
                            <th class="px-5 py-3.5">Customer</th>
                            <th class="px-5 py-3.5">UTR / Reference ID</th>
                            <th class="px-5 py-3.5 text-right">Amount</th>
                            <th class="px-5 py-3.5 text-right">Review Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($manualPending)): ?>
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No manual payment requests waiting in queue.</td></tr>
                        <?php else: ?>
                            <?php foreach ($manualPending as $mp): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-4 text-slate-500 font-mono"><?= date('M d, H:i', strtotime($mp['created_at'])) ?></td>
                                    <td class="px-5 py-4">
                                        <span class="font-bold text-slate-900 block"><?= htmlspecialchars($mp['user_name']) ?></span>
                                        <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($mp['user_email']) ?></span>
                                    </td>
                                    <td class="px-5 py-4 font-mono font-bold text-slate-800 select-all bg-slate-50 px-3 py-1 rounded-lg">
                                        <?= htmlspecialchars($mp['transaction_reference']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-mono tabular-nums font-black text-brand-700 text-sm">
                                        $<?= number_format((float)$mp['amount'], 2) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right space-x-2">
                                        <form action="/admin/payments/review" method="POST" class="inline">
                                            <?= \App\Core\CSRF::field() ?>
                                            <input type="hidden" name="request_id" value="<?= $mp['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition-colors">
                                                Approve & Credit
                                            </button>
                                        </form>

                                        <form action="/admin/payments/review" method="POST" class="inline">
                                            <?= \App\Core\CSRF::field() ?>
                                            <input type="hidden" name="request_id" value="<?= $mp['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl text-xs border border-rose-200 transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gateway Settings & Status -->
    <div class="space-y-3">
        <h2 class="text-lg font-black text-slate-900">Configured Payment Gateways</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($gateways as $gw): ?>
                <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs">
                                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900"><?= htmlspecialchars($gw['name']) ?></h3>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full <?= $gw['is_enabled'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $gw['is_enabled'] ? 'ACTIVE & OPERATIONAL' : 'DISABLED' ?>
                        </span>
                    </div>
                    <pre class="bg-slate-50 p-4 rounded-xl text-[11px] font-mono text-slate-600 overflow-x-auto border border-slate-200 leading-relaxed"><?= htmlspecialchars($gw['config_json']) ?></pre>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

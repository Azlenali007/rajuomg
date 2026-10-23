<div class="space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payment Gateways & Manual QR Queue</h1>
            <p class="text-sm text-slate-500">Review pending manual payments, inspect Razorpay transactions, and toggle gateways.</p>
        </div>
    </div>

    <!-- Manual Payments Approval Queue -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Pending Manual Payment Requests</h2>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
                <?= count($manualPending) ?> Pending Review
            </span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Reference / UTR</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($manualPending)): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No manual payment requests waiting in queue.</td></tr>
                    <?php else: ?>
                        <?php foreach ($manualPending as $mp): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500 font-mono"><?= date('M d, H:i', strtotime($mp['created_at'])) ?></td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-slate-900 block"><?= htmlspecialchars($mp['user_name']) ?></span>
                                    <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($mp['user_email']) ?></span>
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-800 select-all"><?= htmlspecialchars($mp['transaction_reference']) ?></td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums font-bold text-brand-700 text-sm">
                                    $<?= number_format((float)$mp['amount'], 2) ?>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <form action="/admin/payments/review" method="POST" class="inline">
                                        <?= \App\Core\CSRF::field() ?>
                                        <input type="hidden" name="request_id" value="<?= $mp['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition-colors">
                                            Approve & Credit
                                        </button>
                                    </form>

                                    <form action="/admin/payments/review" method="POST" class="inline">
                                        <?= \App\Core\CSRF::field() ?>
                                        <input type="hidden" name="request_id" value="<?= $mp['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-rose-700 font-bold rounded text-xs transition-colors">
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

    <!-- Gateway Settings & Status -->
    <div class="space-y-3">
        <h2 class="text-base font-bold text-slate-900">Configured Payment Gateways</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($gateways as $gw): ?>
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900"><?= htmlspecialchars($gw['name']) ?></h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $gw['is_enabled'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $gw['is_enabled'] ? 'ENABLED' : 'DISABLED' ?>
                        </span>
                    </div>
                    <pre class="bg-slate-50 p-3 rounded-lg text-[11px] font-mono text-slate-600 overflow-x-auto border border-slate-200"><?= htmlspecialchars($gw['config_json']) ?></pre>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

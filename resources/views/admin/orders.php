<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Orders Monitoring</h1>
            <p class="text-xs text-slate-500">Global audit trail of all verification activations across all users and carrier nodes.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-xs">
                <?= count($orders) ?> Total Records
            </span>
        </div>
    </div>

    <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Order Number</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Service</th>
                        <th class="px-5 py-3.5">Country</th>
                        <th class="px-5 py-3.5">Assigned Phone</th>
                        <th class="px-5 py-3.5">Activation ID</th>
                        <th class="px-5 py-3.5">OTP Code</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Selling Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="9" class="px-5 py-10 text-center text-slate-400">No verification orders in the system.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): 
                            $status = $ord['status'];
                            $isDelivered = in_array($status, ['RECEIVED', 'COMPLETED']);
                            $isActive = in_array($status, ['ACTIVE', 'WAITING']);
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                    <span class="block text-[10px] text-slate-400 font-normal"><?= date('M d · H:i', strtotime($ord['created_at'])) ?></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900 block"><?= htmlspecialchars($ord['user_name'] ?? 'User') ?></span>
                                    <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($ord['user_email']) ?></span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-slate-900"><?= htmlspecialchars($ord['service_name']) ?></td>
                                <td class="px-5 py-3.5 text-slate-600 font-medium"><?= htmlspecialchars($ord['country_name'] ?? 'India') ?></td>
                                <td class="px-5 py-3.5 font-mono tabular-nums font-bold text-slate-900">+<?= htmlspecialchars($ord['phone_number']) ?></td>
                                <td class="px-5 py-3.5 font-mono text-slate-400 text-[11px]"><?= htmlspecialchars($ord['provider_activation_id'] ?? '—') ?></td>
                                <td class="px-5 py-3.5">
                                    <?php if (!empty($ord['otp_code'])): ?>
                                        <span class="font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                                            <?= htmlspecialchars($ord['otp_code']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold
                                        <?= $isDelivered ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' ?>
                                        <?= $isActive ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' ?>
                                        <?= in_array($status, ['CANCELLED', 'EXPIRED']) ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' ?>
                                    ">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $isDelivered ? 'bg-emerald-500' : ($isActive ? 'bg-amber-500 animate-pulse' : 'bg-slate-400') ?>"></span>
                                        <span><?= htmlspecialchars($status) ?></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums font-black text-slate-900">
                                    $<?= number_format((float)$ord['price'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="space-y-6">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Orders Monitoring</h1>
            <p class="text-sm text-slate-500">Global audit trail of all verification activations across all users and providers.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                <tr>
                    <th class="px-4 py-3">Order Number</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Service</th>
                    <th class="px-4 py-3">Country</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Activation ID</th>
                    <th class="px-4 py-3">OTP Code</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php foreach ($orders as $ord): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700">
                            <?= htmlspecialchars($ord['order_number']) ?>
                            <span class="block text-[10px] text-slate-400 font-normal"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></span>
                        </td>
                        <td class="px-4 py-3 text-slate-800 font-medium"><?= htmlspecialchars($ord['user_email']) ?></td>
                        <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($ord['service_name']) ?></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($ord['country_name']) ?></td>
                        <td class="px-4 py-3 font-mono tabular-nums font-semibold text-slate-900">+<?= htmlspecialchars($ord['phone_number']) ?></td>
                        <td class="px-4 py-3 font-mono text-slate-500 text-[11px]"><?= htmlspecialchars($ord['provider_activation_id']) ?></td>
                        <td class="px-4 py-3 font-mono font-bold text-emerald-700 text-sm"><?= htmlspecialchars($ord['otp_code'] ?? '—') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold
                                <?= in_array($ord['status'], ['RECEIVED', 'COMPLETED']) ? 'bg-emerald-50 text-emerald-700' : '' ?>
                                <?= in_array($ord['status'], ['ACTIVE', 'WAITING']) ? 'bg-amber-50 text-amber-700' : '' ?>
                                <?= in_array($ord['status'], ['CANCELLED', 'EXPIRED']) ? 'bg-slate-100 text-slate-600' : '' ?>
                            ">
                                <?= htmlspecialchars($ord['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums font-bold text-slate-900">
                            $<?= number_format((float)$ord['price'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="space-y-8">

    <!-- KPI Metrics Grid (Strict Anti-Slop, no hallucinated scoreboards, pure data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Registered Users</span>
            <div class="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-1"><?= number_format($stats['total_users']) ?></div>
            <span class="text-[11px] text-slate-400 mt-1 block">Active user accounts</span>
        </div>

        <!-- Total Orders -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total OTP Orders</span>
            <div class="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-1"><?= number_format($stats['total_orders']) ?></div>
            <span class="text-[11px] text-emerald-600 mt-1 block"><?= number_format($stats['received_orders']) ?> delivered (<?= $stats['total_orders'] > 0 ? round(($stats['received_orders'] / $stats['total_orders']) * 100) : 0 ?>% success)</span>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Deposits (Revenue)</span>
            <div class="text-2xl font-bold font-mono tabular-nums text-brand-700 mt-1">$<?= number_format($stats['total_revenue'], 2) ?></div>
            <span class="text-[11px] text-slate-400 mt-1 block">Via Razorpay & Approved QR</span>
        </div>

        <!-- Estimated Gross Profit -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Net Gross Margin</span>
            <div class="text-2xl font-bold font-mono tabular-nums text-emerald-600 mt-1">$<?= number_format($stats['estimated_profit'], 2) ?></div>
            <span class="text-[11px] text-slate-400 mt-1 block">Selling price minus provider cost</span>
        </div>
    </div>

    <!-- Provider Live Status Strip -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Configured SMS Provider: <?= htmlspecialchars($provider['name'] ?? 'OTP Doctor') ?></h3>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">ACTIVE</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5 font-mono"><?= htmlspecialchars($provider['api_url'] ?? '') ?></p>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right">
                <span class="text-[11px] text-slate-400 block">Reported Provider Balance</span>
                <span class="font-mono tabular-nums font-bold text-slate-900 text-sm">
                    <?= number_format((float)($provider['balance'] ?? 0), 2) ?> <?= htmlspecialchars($provider['currency'] ?? 'RUB') ?>
                </span>
            </div>
            <a href="/admin/providers" class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors whitespace-nowrap">
                Manage Provider API →
            </a>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Recent Platform Activations</h2>
            <a href="/admin/orders" class="text-xs font-semibold text-brand-600 hover:text-brand-800">View All Orders →</a>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Order #</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">OTP Code</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($recentOrders as $ro): ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-slate-700 font-semibold"><?= htmlspecialchars($ro['order_number']) ?></td>
                            <td class="px-4 py-3 text-slate-800"><?= htmlspecialchars($ro['user_email']) ?></td>
                            <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($ro['service_name']) ?></td>
                            <td class="px-4 py-3 font-mono tabular-nums">+<?= htmlspecialchars($ro['phone_number']) ?></td>
                            <td class="px-4 py-3 font-mono font-bold text-emerald-700"><?= htmlspecialchars($ro['otp_code'] ?? '—') ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold
                                    <?= in_array($ro['status'], ['RECEIVED', 'COMPLETED']) ? 'bg-emerald-50 text-emerald-700' : '' ?>
                                    <?= in_array($ro['status'], ['ACTIVE', 'WAITING']) ? 'bg-amber-50 text-amber-700' : '' ?>
                                    <?= in_array($ro['status'], ['CANCELLED', 'EXPIRED']) ? 'bg-slate-100 text-slate-600' : '' ?>
                                ">
                                    <?= htmlspecialchars($ro['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-slate-900">$<?= number_format((float)$ro['price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

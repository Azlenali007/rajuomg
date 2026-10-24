<div class="space-y-8">

    <!-- Top Greeting & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Dashboard Overview</h1>
            <p class="text-xs text-slate-500">Live operational telemetry, revenue analytics, and provider status.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/providers" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="m8 19 4-4 4 4"/><path d="M12 15v8"/></svg>
                <span>Sync Provider</span>
            </a>
            <a href="/admin/orders" class="px-3.5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs shadow-brand-500/20 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Live Orders</span>
            </a>
        </div>
    </div>

    <!-- KPI Metrics Grid (High-Impact Gradient SVG Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Metric 1: Registered Users -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Registered Users</span>
                <div class="text-3xl font-black font-mono tabular-nums text-slate-900 mt-1"><?= number_format($stats['total_users']) ?></div>
                <span class="text-[11px] text-teal-600 font-semibold mt-1 block">Active user accounts</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-white flex items-center justify-center shadow-md shadow-teal-500/20 shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>

        <!-- Metric 2: Total Orders -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total OTP Orders</span>
                <div class="text-3xl font-black font-mono tabular-nums text-slate-900 mt-1"><?= number_format($stats['total_orders']) ?></div>
                <span class="text-[11px] text-emerald-600 font-semibold mt-1 block"><?= number_format($stats['received_orders']) ?> delivered (<?= $stats['total_orders'] > 0 ? round(($stats['received_orders'] / $stats['total_orders']) * 100) : 0 ?>% rate)</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-brand-500/20 shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
        </div>

        <!-- Metric 3: Total Revenue -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Deposits (Revenue)</span>
                <div class="text-3xl font-black font-mono tabular-nums text-brand-700 mt-1">$<?= number_format($stats['total_revenue'], 2) ?></div>
                <span class="text-[11px] text-slate-400 mt-1 block">Completed wallet recharges</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-purple-600 to-pink-500 text-white flex items-center justify-center shadow-md shadow-purple-500/20 shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
        </div>

        <!-- Metric 4: Gross Margin -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Gross Margin</span>
                <div class="text-3xl font-black font-mono tabular-nums text-emerald-600 mt-1">$<?= number_format($stats['estimated_profit'], 2) ?></div>
                <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">Selling price minus provider cost</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>

    </div>

    <!-- Real-Time Chart Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 7-Day Order & Revenue Volume Chart -->
        <div class="lg:col-span-2 bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">7-Day Verification Activity</h3>
                    <p class="text-xs text-slate-400">Order count and gross daily deposit volumes</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span> Orders</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Revenue ($)</span>
                </div>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="analyticsChart"></canvas>
            </div>
        </div>

        <!-- Primary Provider Live Status Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Upstream SMS Supplier</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        ONLINE
                    </span>
                </div>

                <h3 class="text-base font-extrabold text-slate-900">
                    <?= htmlspecialchars($provider['name'] ?? 'OTP Doctor Primary Global API') ?>
                </h3>
                <p class="text-xs text-slate-400 font-mono mt-1 break-all select-all">
                    <?= htmlspecialchars($provider['api_url'] ?? 'https://api.otpdoctor.in/v1') ?>
                </p>

                <div class="mt-5 p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Reported Balance:</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            <?= number_format((float)($provider['balance'] ?? 0), 2) ?> <?= htmlspecialchars($provider['currency'] ?? 'USD') ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Last Synced:</span>
                        <span class="font-mono text-slate-700 font-medium text-[11px]">
                            <?= !empty($provider['last_sync_at']) ? date('H:i · M d', strtotime($provider['last_sync_at'])) : 'Just now' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <a href="/admin/providers" class="w-full py-2.5 px-4 text-xs font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span>Manage Provider Credentials</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Platform Activations Table -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Recent Activations</h2>
                <p class="text-xs text-slate-400">Live feed of all customer verification requests and codes</p>
            </div>
            <a href="/admin/orders" class="text-xs font-bold text-brand-600 hover:text-brand-800 flex items-center gap-1">
                <span>View All Orders</span>
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Order #</th>
                            <th class="px-5 py-3.5">User Account</th>
                            <th class="px-5 py-3.5">Service</th>
                            <th class="px-5 py-3.5">Assigned Phone</th>
                            <th class="px-5 py-3.5">Delivered OTP</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($recentOrders)): ?>
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No orders recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $ro): 
                                $status = $ro['status'];
                                $isDelivered = in_array($status, ['RECEIVED', 'COMPLETED']);
                                $isActive = in_array($status, ['ACTIVE', 'WAITING']);
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-5 py-3.5 font-mono text-slate-700 font-bold">
                                        <?= htmlspecialchars($ro['order_number']) ?>
                                        <span class="block text-[10px] text-slate-400 font-normal"><?= date('M d · H:i', strtotime($ro['created_at'])) ?></span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-bold text-slate-900 block"><?= htmlspecialchars($ro['user_name'] ?? 'User') ?></span>
                                        <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($ro['user_email']) ?></span>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-slate-900"><?= htmlspecialchars($ro['service_name']) ?></td>
                                    <td class="px-5 py-3.5 font-mono tabular-nums font-bold text-slate-800">+<?= htmlspecialchars($ro['phone_number']) ?></td>
                                    <td class="px-5 py-3.5">
                                        <?php if (!empty($ro['otp_code'])): ?>
                                            <span class="font-mono font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                                                <?= htmlspecialchars($ro['otp_code']) ?>
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
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-slate-900">
                                        $<?= number_format((float)$ro['price'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('analyticsChart')?.getContext('2d');
    if (!ctx) return;

    const chartData = <?= json_encode($chartData ?? [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'orders' => [0, 0, 0, 0, 0, 0, 0],
        'revenue' => [0, 0, 0, 0, 0, 0, 0]
    ]) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Orders Count',
                    data: chartData.orders,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    tension: 0.35,
                    borderWidth: 2.5,
                    fill: true,
                    pointBackgroundColor: '#7c3aed',
                    pointRadius: 4,
                    yAxisID: 'y'
                },
                {
                    label: 'Revenue ($)',
                    data: chartData.revenue,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderDash: [4, 4],
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'Plus Jakarta Sans' }, color: '#64748b' }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10 }, color: '#64748b', precision: 0 }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: { font: { size: 10 }, color: '#10b981', callback: val => '$' + val }
                }
            }
        }
    });
});
</script>

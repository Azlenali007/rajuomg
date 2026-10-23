<div class="space-y-6">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Activation Orders</h1>
            <p class="text-sm text-slate-500">Track and review all your SMS verification requests.</p>
        </div>
        <a href="/services" class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
            Get New Number
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-base font-semibold text-slate-800">No verification orders yet</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">When you request a phone number for any verification service, it will appear here with live OTP status.</p>
            <div class="pt-2">
                <a href="/services" class="inline-flex px-4 py-2 text-xs font-semibold text-white bg-brand-600 rounded-lg shadow-sm">Browse Services</a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Order Number</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Phone Number</th>
                            <th class="px-4 py-3">OTP Code</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Price</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($orders as $ord): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-3.5 font-mono text-xs font-semibold text-slate-700">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                    <span class="block text-[10px] text-slate-400 font-normal"><?= date('M d, H:i', strtotime($ord['created_at'])) ?></span>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-900">
                                    <?= htmlspecialchars($ord['service_name']) ?>
                                    <span class="text-xs text-slate-400 block"><?= htmlspecialchars($ord['country_name']) ?></span>
                                </td>
                                <td class="px-4 py-3.5 font-mono tabular-nums font-semibold text-slate-800">
                                    +<?= htmlspecialchars($ord['phone_number']) ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <?php if (!empty($ord['otp_code'])): ?>
                                        <span class="font-mono tabular-nums font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200 text-sm">
                                            <?= htmlspecialchars($ord['otp_code']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-block text-xs font-semibold font-mono px-2 py-0.5 rounded
                                        <?= in_array($ord['status'], ['RECEIVED', 'COMPLETED']) ? 'bg-emerald-50 text-emerald-700' : '' ?>
                                        <?= in_array($ord['status'], ['ACTIVE', 'WAITING']) ? 'bg-amber-50 text-amber-700' : '' ?>
                                        <?= in_array($ord['status'], ['CANCELLED', 'EXPIRED']) ? 'bg-slate-100 text-slate-600' : '' ?>
                                    ">
                                        <?= htmlspecialchars($ord['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums text-slate-900 font-semibold">
                                    $<?= number_format((float)$ord['price'], 2) ?>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="/order?id=<?= $ord['id'] ?>" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                                        View & Monitor →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

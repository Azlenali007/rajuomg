<div class="space-y-6">

    <!-- Header & Statistics Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">My Activation Orders</h1>
            <p class="text-xs text-slate-500">Track and review all your real-time SMS verification sessions and codes.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/services" class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs shadow-brand-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Acquire New Number</span>
            </a>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <!-- Premium Empty State -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-12 text-center space-y-4 shadow-xs max-w-lg mx-auto my-8">
            <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <path d="M12 7v4"/><path d="M12 15h.01"/>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-800">No verification orders yet</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    When you request a virtual phone number for WhatsApp, Telegram, Google, or any service, your live order and incoming SMS will appear here.
                </p>
            </div>
            <div class="pt-2">
                <a href="/services" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-md shadow-brand-500/20 transition-all">
                    <span>Browse 500+ Services</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Orders Table Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Order Info</th>
                            <th class="px-5 py-3.5">Service & Country</th>
                            <th class="px-5 py-3.5">Assigned Number</th>
                            <th class="px-5 py-3.5">Received OTP</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Cost</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($orders as $ord): 
                            $status = $ord['status'];
                            $isDelivered = in_array($status, ['RECEIVED', 'COMPLETED']);
                            $isActive = in_array($status, ['ACTIVE', 'WAITING']);
                            $isCancelled = in_array($status, ['CANCELLED', 'EXPIRED', 'REFUNDED']);
                        ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Order Info -->
                                <td class="px-5 py-4">
                                    <div class="font-mono font-bold text-slate-800"><?= htmlspecialchars($ord['order_number']) ?></div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5"><?= date('M d, Y · H:i', strtotime($ord['created_at'])) ?></span>
                                </td>

                                <!-- Service & Country -->
                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-900 block"><?= htmlspecialchars($ord['service_name']) ?></span>
                                    <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        <?= htmlspecialchars($ord['country_name'] ?? 'India') ?>
                                    </span>
                                </td>

                                <!-- Phone Number with Copy Button -->
                                <td class="px-5 py-4">
                                    <div class="inline-flex items-center gap-1.5 font-mono font-bold text-slate-900 bg-slate-100/80 px-2.5 py-1 rounded-lg">
                                        <span>+<?= htmlspecialchars($ord['phone_number']) ?></span>
                                        <button 
                                            onclick="navigator.clipboard.writeText('+<?= htmlspecialchars($ord['phone_number']) ?>'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Number copied!', showConfirmButton: false, timer: 1500});"
                                            title="Copy number"
                                            class="text-slate-400 hover:text-slate-700 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- OTP Code -->
                                <td class="px-5 py-4">
                                    <?php if (!empty($ord['otp_code'])): ?>
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200/90 rounded-lg">
                                            <span class="font-mono text-sm font-extrabold text-emerald-700 tracking-wider"><?= htmlspecialchars($ord['otp_code']) ?></span>
                                            <button 
                                                onclick="navigator.clipboard.writeText('<?= htmlspecialchars($ord['otp_code']) ?>'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'OTP copied!', showConfirmButton: false, timer: 1500});"
                                                title="Copy code"
                                                class="text-emerald-500 hover:text-emerald-800"
                                            >
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                            </button>
                                        </div>
                                    <?php elseif ($isActive): ?>
                                        <span class="inline-flex items-center gap-1.5 text-amber-600 font-medium">
                                            <svg class="w-3 h-3 animate-spin text-amber-500" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            <span>Waiting SMS...</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status Tag -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold font-mono
                                        <?php if ($isDelivered): ?> bg-emerald-50 text-emerald-700 border border-emerald-200
                                        <?php elseif ($isActive): ?> bg-amber-50 text-amber-700 border border-amber-200
                                        <?php else: ?> bg-slate-100 text-slate-600 border border-slate-200
                                        <?php endif; ?>
                                    ">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $isDelivered ? 'bg-emerald-500' : ($isActive ? 'bg-amber-500 animate-pulse' : 'bg-slate-400') ?>"></span>
                                        <span><?= htmlspecialchars($status) ?></span>
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">
                                    $<?= number_format((float)$ord['price'], 2) ?>
                                </td>

                                <!-- View Details CTA -->
                                <td class="px-5 py-4 text-right">
                                    <a href="/order?id=<?= $ord['id'] ?>" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-800 transition-colors">
                                        <span>Monitor</span>
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
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

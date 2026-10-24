<div x-data="userManager()" class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">User Accounts & Ledgers</h1>
            <p class="text-xs text-slate-500">Manage user accounts, inspect wallet balances, and perform audited balance adjustments.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-xs">
                <?= count($users) ?> Accounts Registered
            </span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <form action="/admin/users" method="GET" class="flex gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input 
                type="text" 
                name="q" 
                value="<?= htmlspecialchars($search ?? '') ?>"
                placeholder="Search user by name or email address..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-xs"
            >
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors shadow-xs">
            Filter
        </button>
    </form>

    <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">User ID</th>
                        <th class="px-5 py-3.5">Customer Name & Email</th>
                        <th class="px-5 py-3.5">Wallet Balance</th>
                        <th class="px-5 py-3.5">Orders Placed</th>
                        <th class="px-5 py-3.5">Account Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 font-mono text-slate-400 font-bold">#<?= $u['id'] ?></td>
                            <td class="px-5 py-4">
                                <span class="font-extrabold text-slate-900 block"><?= htmlspecialchars($u['name']) ?></span>
                                <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($u['email']) ?></span>
                            </td>
                            <td class="px-5 py-4 font-mono tabular-nums font-black text-brand-700 text-sm">
                                $<?= number_format((float)($u['balance'] ?? 0), 2) ?>
                            </td>
                            <td class="px-5 py-4 font-mono tabular-nums text-slate-600 font-semibold">
                                <?= $u['order_count'] ?> orders
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase
                                    <?= $u['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>
                                ">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['status'] === 'active' ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                                    <span><?= htmlspecialchars($u['status']) ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-2">
                                <button 
                                    @click="openAdjustModal(<?= $u['id'] ?>, '<?= addslashes($u['name']) ?>', <?= (float)($u['balance'] ?? 0) ?>)"
                                    class="px-3 py-1.5 text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg border border-brand-200 transition-colors"
                                >
                                    Adjust Balance
                                </button>

                                <form action="/admin/users/toggle" method="POST" class="inline">
                                    <?= \App\Core\CSRF::field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors">
                                        <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audited Balance Adjustment Modal -->
    <div x-show="adjustOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-4 border border-slate-100" @click.away="adjustOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">Adjust Wallet Balance</h3>
                <button @click="adjustOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <div>Customer: <strong class="text-slate-900" x-text="targetName"></strong></div>
                <div>Current Balance: <strong class="text-brand-700 font-mono font-bold">$<span x-text="targetBalance.toFixed(2)"></span></strong></div>
            </div>

            <form action="/admin/users/adjust-wallet" method="POST" class="space-y-4 text-xs">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="user_id" :value="targetId">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Adjustment Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                            <input type="radio" name="type" value="credit" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-bold text-emerald-700">Credit Funds (+)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-rose-50 hover:border-rose-200 transition-colors">
                            <input type="radio" name="type" value="debit" class="text-rose-600 focus:ring-rose-500">
                            <span class="font-bold text-rose-700">Debit Funds (-)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700">Amount (USD)</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" required placeholder="5.00" class="w-full pl-7 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700">Audit / Ledger Reason</label>
                    <input type="text" name="reason" required placeholder="e.g. Compensation for carrier line issue" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="adjustOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-colors">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function userManager() {
    return {
        adjustOpen: false,
        targetId: 0,
        targetName: '',
        targetBalance: 0,

        openAdjustModal(id, name, balance) {
            this.targetId = id;
            this.targetName = name;
            this.targetBalance = balance;
            this.adjustOpen = true;
        }
    };
}
</script>

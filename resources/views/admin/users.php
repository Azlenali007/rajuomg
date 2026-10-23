<div x-data="userManager()" class="space-y-6">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Accounts & Wallets</h1>
            <p class="text-sm text-slate-500">Manage user status, inspect ledger balances, and perform audited balance adjustments.</p>
        </div>
    </div>

    <!-- Search Bar -->
    <form action="/admin/users" method="GET" class="flex gap-3">
        <input 
            type="text" 
            name="q" 
            value="<?= htmlspecialchars($search ?? '') ?>"
            placeholder="Search by user name or email..."
            class="flex-1 max-w-md px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"
        >
        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
            Filter
        </button>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Wallet Balance</th>
                    <th class="px-4 py-3">Orders</th>
                    <th class="px-4 py-3">Account Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-mono text-slate-500">#<?= $u['id'] ?></td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($u['name']) ?></span>
                            <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($u['email']) ?></span>
                        </td>
                        <td class="px-4 py-3 font-mono tabular-nums font-bold text-brand-700 text-sm">
                            $<?= number_format((float)($u['balance'] ?? 0), 2) ?>
                        </td>
                        <td class="px-4 py-3 font-mono tabular-nums text-slate-600"><?= $u['order_count'] ?> orders</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                <?= $u['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>
                            ">
                                <?= htmlspecialchars($u['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button 
                                @click="openAdjustModal(<?= $u['id'] ?>, '<?= addslashes($u['name']) ?>', <?= (float)($u['balance'] ?? 0) ?>)"
                                class="px-2.5 py-1 text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 rounded border border-brand-200 transition-colors"
                            >
                                Adjust Balance
                            </button>

                            <form action="/admin/users/toggle" method="POST" class="inline">
                                <?= \App\Core\CSRF::field() ?>
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition-colors">
                                    <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Manual Adjustment Modal -->
    <div x-show="adjustOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
            <h3 class="text-base font-bold text-slate-900">Adjust Wallet Balance</h3>
            <p class="text-xs text-slate-500">
                User: <strong x-text="targetName"></strong> | Current: $<span x-text="targetBalance.toFixed(2)"></span>
            </p>

            <form action="/admin/users/adjust-wallet" method="POST" class="space-y-4">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="user_id" :value="targetId">

                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <input type="radio" name="type" value="credit" checked class="mr-1"> Credit Funds (+)
                    </label>
                    <label class="block">
                        <input type="radio" name="type" value="debit" class="mr-1"> Debit Funds (-)
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Amount (USD)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Audit Reason</label>
                    <input type="text" name="reason" required placeholder="e.g. Compensation for failed number" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="adjustOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm">Save Adjustment</button>
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

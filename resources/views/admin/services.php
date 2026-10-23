<div class="space-y-6">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Services & Custom Pricing</h1>
            <p class="text-sm text-slate-500">Super Admin pricing controls. Customize customer-facing selling prices and margins.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Service Name</th>
                    <th class="px-4 py-3">Provider Cost (Avg)</th>
                    <th class="px-4 py-3">Selling Price (USD)</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php foreach ($services as $srv): ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700"><?= htmlspecialchars($srv['service_code']) ?></td>
                        <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($srv['service_name']) ?></td>
                        <td class="px-4 py-3 font-mono tabular-nums text-slate-500">$<?= number_format((float)($srv['avg_cost'] ?? 0.4), 2) ?></td>
                        <td class="px-4 py-3 font-mono tabular-nums font-bold text-brand-700">
                            <form action="/admin/services/price" method="POST" class="flex items-center gap-2">
                                <?= \App\Core\CSRF::field() ?>
                                <input type="hidden" name="service_id" value="<?= $srv['id'] ?>">
                                <span class="text-slate-400">$</span>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0.1" 
                                    name="custom_price" 
                                    value="<?= number_format((float)$srv['custom_price'], 2) ?>"
                                    class="w-20 px-2 py-1 bg-slate-50 border border-slate-200 rounded font-mono font-bold text-slate-900 text-xs focus:ring-1 focus:ring-brand-500"
                                >
                        </td>
                        <td class="px-4 py-3">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" <?= $srv['is_active'] ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="relative w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-brand-600"></div>
                                    <span class="ms-2 text-[11px] font-medium text-slate-600"><?= $srv['is_active'] ? 'Enabled' : 'Disabled' ?></span>
                                </label>
                        </td>
                        <td class="px-4 py-3 text-right">
                                <button type="submit" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded transition-colors text-xs">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

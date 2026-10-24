<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Services & Custom Pricing</h1>
            <p class="text-xs text-slate-500">Super Admin pricing controls. Customize customer-facing selling prices and margins.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-700 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-xs">
                <?= count($services) ?> Total Services
            </span>
        </div>
    </div>

    <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Service Name</th>
                        <th class="px-5 py-3.5">Provider Cost (Est)</th>
                        <th class="px-5 py-3.5">Custom Selling Price</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($services as $srv): 
                        $code = strtolower($srv['service_code']);
                        $avgCost = (float)($srv['avg_cost'] ?? 0.4);
                        $sellingPrice = (float)$srv['custom_price'];
                        $margin = $sellingPrice > 0 ? round((($sellingPrice - $avgCost) / $sellingPrice) * 100) : 0;
                    ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-mono font-black text-slate-800 bg-slate-100 px-2 py-1 rounded text-xs uppercase">
                                    <?= htmlspecialchars($srv['service_code']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        <?= strtoupper(substr($srv['service_code'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block"><?= htmlspecialchars($srv['service_name']) ?></span>
                                        <span class="text-[10px] text-emerald-600 font-semibold font-mono">~<?= $margin ?>% Margin</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono tabular-nums text-slate-500 font-semibold">
                                $<?= number_format($avgCost, 2) ?>
                            </td>
                            <td class="px-5 py-4">
                                <form action="/admin/services/price" method="POST" class="flex items-center gap-1.5">
                                    <?= \App\Core\CSRF::field() ?>
                                    <input type="hidden" name="service_id" value="<?= $srv['id'] ?>">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 font-bold">$</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0.1" 
                                            name="custom_price" 
                                            value="<?= number_format($sellingPrice, 2) ?>"
                                            class="w-24 pl-6 pr-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg font-mono font-bold text-slate-900 text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white"
                                        >
                                    </div>
                            </td>
                            <td class="px-5 py-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" <?= $srv['is_active'] ? 'checked' : '' ?> class="sr-only peer">
                                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                                        <span class="ms-2 text-xs font-semibold <?= $srv['is_active'] ? 'text-emerald-700' : 'text-slate-400' ?>">
                                            <?= $srv['is_active'] ? 'Active' : 'Disabled' ?>
                                        </span>
                                    </label>
                            </td>
                            <td class="px-5 py-4 text-right">
                                    <button type="submit" class="px-3.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-lg border border-brand-200 transition-colors text-xs shadow-2xs">
                                        Save Changes
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

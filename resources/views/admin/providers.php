<div x-data="providerManager()" class="space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Provider API & Synchronization</h1>
            <p class="text-xs text-slate-500">Configure external SMS supplier (OTP Doctor handler), test credentials, and trigger catalog sync.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                @click="testConnection(1)"
                :disabled="testing"
                class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Test Handshake</span>
            </button>
            <button 
                @click="triggerSync(1)"
                :disabled="syncing"
                class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs shadow-brand-500/20 transition-all flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="m8 19 4-4 4 4"/><path d="M12 15v8"/></svg>
                <span>Sync Services & Stock</span>
            </button>
        </div>
    </div>

    <!-- Provider Configuration Card -->
    <?php foreach ($providers as $p): ?>
        <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-extrabold text-slate-900"><?= htmlspecialchars($p['name']) ?></h2>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                ACTIVE
                            </span>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">Provider ID: #<?= $p['id'] ?> · Code: <?= htmlspecialchars($p['code']) ?></span>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Reported Provider Balance</span>
                    <span class="text-2xl font-black font-mono tabular-nums text-slate-900">
                        <?= number_format((float)$p['balance'], 2) ?> <span class="text-xs font-bold text-slate-500"><?= htmlspecialchars($p['currency']) ?></span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block">API Gateway Endpoint</span>
                    <span class="font-mono text-slate-900 break-all select-all font-bold block"><?= htmlspecialchars($p['api_url']) ?></span>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block">API Authentication Secret</span>
                    <span class="font-mono text-slate-900 font-bold block">•••••••••••••••••••••••• (Encrypted in database)</span>
                </div>
            </div>

            <div class="text-xs text-slate-500 flex flex-col sm:flex-row sm:items-center justify-between pt-2 border-t border-slate-100 gap-2">
                <span>Last Synchronized: <strong class="text-slate-700 font-mono"><?= $p['last_sync_at'] ? date('M d, Y · H:i:s', strtotime($p['last_sync_at'])) : 'Never' ?></strong></span>
                <span>Operational Status: <strong class="<?= $p['is_active'] ? 'text-emerald-600' : 'text-rose-600' ?>"><?= $p['is_active'] ? 'Active & Receiving Webhooks' : 'Disabled' ?></strong></span>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Provider Diagnostic Logs (Secrets Redacted) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Provider API Telemetry Logs</h2>
                <p class="text-xs text-slate-400">Sanitized HTTP exchange logs between server and supplier nodes</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-5 py-3.5">Timestamp</th>
                            <th class="px-5 py-3.5">Action</th>
                            <th class="px-5 py-3.5">Sanitized URL</th>
                            <th class="px-5 py-3.5">HTTP Status</th>
                            <th class="px-5 py-3.5">Latency</th>
                            <th class="px-5 py-3.5">Response Snippet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        <?php if (empty($recentLogs)): ?>
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No external provider calls logged yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 text-slate-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td class="px-5 py-3 font-bold text-slate-700"><?= htmlspecialchars($log['action']) ?></td>
                                    <td class="px-5 py-3 text-slate-600 max-w-xs truncate"><?= htmlspecialchars($log['request_url']) ?></td>
                                    <td class="px-5 py-3 font-bold <?= $log['response_status'] == 200 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                        <?= $log['response_status'] ?>
                                    </td>
                                    <td class="px-5 py-3 tabular-nums text-slate-500"><?= $log['response_time_ms'] ?>ms</td>
                                    <td class="px-5 py-3 text-slate-800 max-w-sm truncate"><?= htmlspecialchars(substr($log['response_body'] ?? '', 0, 80)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function providerManager() {
    return {
        testing: false,
        syncing: false,

        async testConnection(id) {
            this.testing = true;
            Swal.fire({
                title: 'Testing Provider Handshake...',
                text: 'Calling getBalance endpoint on supplier node...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('provider_id', id);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/admin/providers/test', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Connection Successful',
                        text: `Provider responded OK! Balance: ${data.balance} ${data.currency}`,
                        confirmButtonColor: '#7c3aed'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Connection Failed', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Network Error', 'Unable to execute test.', 'error');
            } finally {
                this.testing = false;
            }
        },

        async triggerSync(id) {
            this.syncing = true;
            Swal.fire({
                title: 'Synchronizing Catalog...',
                text: 'Fetching updated service pricing and active carrier nodes...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('provider_id', id);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/admin/providers/sync', { method: 'POST', body: formData });
                const data = await res.json();

                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Sync Completed' : 'Sync Issue',
                    text: data.message,
                    confirmButtonColor: '#7c3aed'
                }).then(() => location.reload());
            } catch (e) {
                Swal.fire('Error', 'Sync failed to complete.', 'error');
            } finally {
                this.syncing = false;
            }
        }
    };
}
</script>

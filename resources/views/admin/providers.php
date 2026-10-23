<div x-data="providerManager()" class="space-y-8">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Provider API & Synchronization</h1>
            <p class="text-sm text-slate-500">Configure external SMS supplier (OTP Doctor handler), test credentials, and trigger manual sync.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                @click="testConnection(1)"
                :disabled="testing"
                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-colors"
            >
                Test Connection
            </button>
            <button 
                @click="triggerSync(1)"
                :disabled="syncing"
                class="px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors"
            >
                Sync Countries & Services
            </button>
        </div>
    </div>

    <!-- Provider Configuration Card -->
    <?php foreach ($providers as $p): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900"><?= htmlspecialchars($p['name']) ?></h2>
                    <span class="text-xs text-slate-400 font-mono">Internal ID: #<?= $p['id'] ?> (<?= htmlspecialchars($p['code']) ?>)</span>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Reported Balance</span>
                    <span class="text-lg font-bold font-mono tabular-nums text-slate-900">
                        <?= number_format((float)$p['balance'], 2) ?> <?= htmlspecialchars($p['currency']) ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <span class="text-slate-500 font-medium block">API Handler URL</span>
                    <span class="font-mono text-slate-800 break-all select-all font-semibold"><?= htmlspecialchars($p['api_url']) ?></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <span class="text-slate-500 font-medium block">API Secret Token / Key</span>
                    <span class="font-mono text-slate-800 font-semibold">•••••••••••••••• (Stored securely in server DB)</span>
                </div>
            </div>

            <div class="text-xs text-slate-500 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Last Synchronized: <strong><?= $p['last_sync_at'] ? date('M d, Y H:i:s', strtotime($p['last_sync_at'])) : 'Never' ?></strong></span>
                <span>Active Status: <strong class="<?= $p['is_active'] ? 'text-emerald-600' : 'text-rose-600' ?>"><?= $p['is_active'] ? 'Online / Operational' : 'Disabled' ?></strong></span>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Provider Diagnostic Logs (Secrets Redacted) -->
    <div class="space-y-3">
        <h2 class="text-base font-bold text-slate-900">Provider API Request Logs (Sanitized)</h2>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Sanitized URL</th>
                        <th class="px-4 py-3">HTTP Status</th>
                        <th class="px-4 py-3">Latency</th>
                        <th class="px-4 py-3">Response Snippet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    <?php if (empty($recentLogs)): ?>
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No external provider calls logged yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 text-slate-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                                <td class="px-4 py-2.5 font-bold text-slate-700"><?= htmlspecialchars($log['action']) ?></td>
                                <td class="px-4 py-2.5 text-slate-600 max-w-xs truncate"><?= htmlspecialchars($log['request_url']) ?></td>
                                <td class="px-4 py-2.5 font-bold <?= $log['response_status'] == 200 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= $log['response_status'] ?>
                                </td>
                                <td class="px-4 py-2.5 tabular-nums text-slate-500"><?= $log['response_time_ms'] ?>ms</td>
                                <td class="px-4 py-2.5 text-slate-800 max-w-sm truncate"><?= htmlspecialchars(substr($log['response_body'] ?? '', 0, 80)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
                text: 'Calling getBalance endpoint...',
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
                title: 'Synchronizing Provider Catalog...',
                text: 'Importing countries and services from OTP Doctor...',
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

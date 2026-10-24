<div x-data="providerManager()" class="space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Provider API & Synchronization</h1>
            <p class="text-xs text-slate-500">Configure external SMS supplier (OTP Doctor handler), test credentials, and import/synchronize catalog.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button 
                @click="importCatalog(1)"
                :disabled="catalogImporting"
                class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Import Standard Catalog</span>
            </button>
            <button 
                @click="testConnection(1)"
                :disabled="testing"
                class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Test Handshake</span>
            </button>
            <button 
                @click="triggerSync(1)"
                :disabled="syncing"
                class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs shadow-brand-500/20 transition-all flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="m8 19 4-4 4 4"/><path d="M12 15v8"/></svg>
                <span>Sync from Provider API</span>
            </button>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Provider Configuration & Live Settings Card -->
    <?php foreach ($providers as $p): ?>
        <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
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
                        <?= number_format((float)$p['balance'], 2) ?> <span class="text-xs font-bold text-slate-500"><?= htmlspecialchars($p['currency'] ?? 'USD') ?></span>
                    </span>
                </div>
            </div>

            <!-- Provider Settings Form -->
            <form action="/admin/providers/update" method="POST" class="space-y-4">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Provider Display Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            required 
                            value="<?= htmlspecialchars($p['name']) ?>" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">API Handler URL</label>
                        <input 
                            type="url" 
                            name="api_url" 
                            required 
                            value="<?= htmlspecialchars($p['api_url']) ?>" 
                            placeholder="https://www.otpdoctor.in/stubs/handler_api.php"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">API Secret Key / Token</label>
                        <div class="relative">
                            <input 
                                :type="showKey ? 'text' : 'password'" 
                                name="api_key" 
                                placeholder="Enter your provider API key (leave blank to keep existing key)"
                                class="w-full pl-3 pr-20 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"
                            >
                            <button 
                                type="button" 
                                @click="showKey = !showKey" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 font-semibold text-[11px]"
                            >
                                <span x-text="showKey ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Your API key is stored securely on the server and never exposed to website visitors.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500">
                        Last Synchronized: <strong class="text-slate-800 font-mono"><?= $p['last_sync_at'] ? date('M d, Y · H:i:s', strtotime($p['last_sync_at'])) : 'Never' ?></strong>
                    </span>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-xs transition-colors">
                        Save Provider Settings
                    </button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <!-- Provider Diagnostic Logs (Sanitized) -->
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
        showKey: false,
        testing: false,
        syncing: false,
        catalogImporting: false,

        async testConnection(id) {
            this.testing = true;
            Swal.fire({
                title: 'Testing Provider Handshake...',
                text: 'Calling getBalance on provider endpoint...',
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
                        title: 'Handshake Verified!',
                        text: `Provider responded OK! Account Balance: ${data.balance} ${data.currency}`,
                        confirmButtonColor: '#7c3aed'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Notice',
                        text: data.message
                    });
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
                title: 'Synchronizing from API...',
                text: 'Contacting provider endpoint to import active countries and service rates...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('provider_id', id);
                formData.append('country_code', 'in');
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/admin/providers/sync', { method: 'POST', body: formData });
                const data = await res.json();

                Swal.fire({
                    icon: data.success ? 'success' : 'info',
                    title: data.success ? 'Synchronization Complete' : 'API Response Notice',
                    text: data.message,
                    confirmButtonColor: '#7c3aed'
                }).then(() => location.reload());
            } catch (e) {
                Swal.fire('Error', 'Sync request failed.', 'error');
            } finally {
                this.syncing = false;
            }
        },

        async importCatalog(id) {
            this.catalogImporting = true;
            Swal.fire({
                title: 'Importing Verified Catalog...',
                text: 'Loading verified services and international country numbers into database...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('provider_id', id);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/admin/providers/import-catalog', { method: 'POST', body: formData });
                const data = await res.json();

                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Catalog Imported!' : 'Import Error',
                    text: data.message,
                    confirmButtonColor: '#7c3aed'
                }).then(() => location.reload());
            } catch (e) {
                Swal.fire('Error', 'Catalog import failed.', 'error');
            } finally {
                this.catalogImporting = false;
            }
        }
    };
}
</script>

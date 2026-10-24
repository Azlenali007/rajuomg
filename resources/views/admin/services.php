<?php
/**
 * Super Admin — Services & Bulk Operations
 * Supports multi-selection, bulk delete, delete all (with dual confirmation),
 * bulk margin setting (percentage & fixed), search/filtering, and catalog/provider sync.
 */
?>
<div x-data="adminServicesManager()" class="space-y-6">

    <!-- Page Header & Action Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Services & Bulk Management</h1>
            <p class="text-xs text-slate-500">Configure catalog selling prices, apply bulk profit margins, delete records, and sync provider routes.</p>
        </div>
        
        <!-- Top Quick Controls (Provider API Sync) -->
        <div class="flex flex-wrap items-center gap-2.5">
            <button 
                @click="syncFromProvider()" 
                :disabled="loading"
                class="px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-all flex items-center gap-1.5 disabled:opacity-50"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Sync from Provider API</span>
            </button>

            <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-2 rounded-xl">
                <?= count($services) ?> Total Services
            </span>
        </div>
    </div>

    <!-- Feedback alerts from session if present -->
    <?php if (!empty($success)): ?>
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Bulk Operations Toolbar & Filter Canvas -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Left: Selection status & Bulk Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Selection Counter Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-bold transition-colors"
                     :class="selectedIds.length > 0 ? 'bg-brand-50 border-brand-200 text-brand-800' : 'bg-slate-50 border-slate-200 text-slate-500'">
                    <span class="w-2 h-2 rounded-full" :class="selectedIds.length > 0 ? 'bg-brand-600 animate-pulse' : 'bg-slate-400'"></span>
                    <span x-text="selectedIds.length + ' / ' + filteredServices.length + ' Selected'"></span>
                </div>

                <!-- Bulk Margin Settings Button -->
                <button 
                    @click="openBulkMarginModal()" 
                    class="px-3 py-1.5 text-xs font-bold rounded-xl border transition-all flex items-center gap-1.5 shadow-2xs"
                    :class="selectedIds.length > 0 ? 'bg-indigo-50 border-indigo-200 text-indigo-700 hover:bg-indigo-100' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200'"
                >
                    <svg class="w-3.5 h-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Bulk Margin Settings</span>
                </button>

                <!-- Bulk Delete Selected Button -->
                <button 
                    @click="confirmBulkDelete()" 
                    :disabled="selectedIds.length === 0 || loading"
                    class="px-3 py-1.5 text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl transition-all flex items-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Delete Selected</span>
                </button>

                <!-- Clear Selection Button -->
                <button 
                    x-show="selectedIds.length > 0" 
                    @click="selectedIds = []" 
                    class="px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors"
                >
                    Clear
                </button>
            </div>

            <!-- Right: Delete All Services (Prominently Isolated & Protected) -->
            <div>
                <button 
                    @click="confirmDeleteAll()" 
                    :disabled="loading"
                    class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50/50 hover:bg-rose-100/60 border border-rose-200/80 rounded-xl transition-all flex items-center gap-1.5"
                    title="Remove all service records. Historical customer orders will remain protected."
                >
                    <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Delete All Services</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Sub-strip -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100 text-xs">
            
            <!-- Search Filter -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input 
                    type="text" 
                    x-model="search" 
                    placeholder="Filter by name or code (e.g. wa, telegram)..." 
                    class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"
                >
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center gap-1 self-start sm:self-auto">
                <button 
                    @click="statusFilter = 'all'" 
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                    :class="statusFilter === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >
                    All
                </button>
                <button 
                    @click="statusFilter = 'active'" 
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                    :class="statusFilter === 'active' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Active Only
                </button>
                <button 
                    @click="statusFilter = 'disabled'" 
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                    :class="statusFilter === 'disabled' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >
                    Disabled Only
                </button>
            </div>

        </div>
    </div>

    <!-- Services Table with Selection Checkboxes -->
    <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <!-- Select All Checkbox -->
                            <input 
                                type="checkbox" 
                                @change="toggleSelectAll()" 
                                :checked="isAllSelected()"
                                class="w-4 h-4 text-brand-600 bg-white border-slate-300 rounded focus:ring-brand-500 cursor-pointer"
                                title="Select All Visible Services"
                            >
                        </th>
                        <th class="px-4 py-3.5">Code</th>
                        <th class="px-5 py-3.5">Service Name</th>
                        <th class="px-5 py-3.5">Provider Cost</th>
                        <th class="px-5 py-3.5">Custom Selling Price</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <template x-for="srv in filteredServices" :key="srv.id">
                        <tr class="hover:bg-slate-50/70 transition-colors" :class="selectedIds.includes(srv.id) ? 'bg-brand-50/40' : ''">
                            <!-- Row Checkbox -->
                            <td class="px-4 py-4 text-center">
                                <input 
                                    type="checkbox" 
                                    :value="srv.id" 
                                    x-model="selectedIds"
                                    class="w-4 h-4 text-brand-600 bg-white border-slate-300 rounded focus:ring-brand-500 cursor-pointer"
                                >
                            </td>

                            <!-- Code -->
                            <td class="px-4 py-4">
                                <span class="font-mono font-black text-slate-800 bg-slate-100 px-2 py-1 rounded text-xs uppercase" x-text="srv.service_code"></span>
                            </td>

                            <!-- Name & Margin -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0" x-text="srv.service_code.substring(0, 2).toUpperCase()"></div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900" x-text="srv.service_name"></span>
                                            <template x-if="srv.orders_count > 0">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-blue-50 text-blue-700 border border-blue-200" :title="srv.orders_count + ' orders placed'">
                                                    <span x-text="srv.orders_count"></span> orders
                                                </span>
                                            </template>
                                        </div>
                                        <span class="text-[10px] text-emerald-600 font-semibold font-mono" x-text="'~' + calculateMargin(srv.avg_cost, srv.custom_price) + '% Margin'"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Cost -->
                            <td class="px-5 py-4 font-mono tabular-nums text-slate-500 font-semibold" x-text="'$' + srv.avg_cost.toFixed(2)"></td>

                            <!-- Price Input Form -->
                            <td class="px-5 py-4">
                                <form :action="'/admin/services/price'" method="POST" class="flex items-center gap-1.5" @submit="saveSinglePrice($event, srv)">
                                    <?= \App\Core\CSRF::field() ?>
                                    <input type="hidden" name="service_id" :value="srv.id">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 font-bold">$</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0.1" 
                                            name="custom_price" 
                                            :value="srv.custom_price.toFixed(2)"
                                            class="w-24 pl-6 pr-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg font-mono font-bold text-slate-900 text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white"
                                        >
                                    </div>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" :checked="srv.is_active == 1" class="sr-only peer">
                                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                                        <span class="ms-2 text-xs font-semibold" :class="srv.is_active == 1 ? 'text-emerald-700' : 'text-slate-400'" x-text="srv.is_active == 1 ? 'Active' : 'Disabled'"></span>
                                    </label>
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-4 text-right">
                                    <button type="submit" class="px-3.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-lg border border-brand-200 transition-colors text-xs shadow-2xs">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty Filter State -->
                    <tr x-show="filteredServices.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <p class="font-semibold text-slate-600">No services match your filters.</p>
                            <p class="text-xs mt-1">Try adjusting your search query or reset your selection.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bulk Margin Settings Modal -->
    <div 
        x-show="bulkMarginModal" 
        x-transition.opacity 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        style="display: none;"
    >
        <div 
            @click.away="bulkMarginModal = false" 
            class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-5"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Bulk Margin Settings</h3>
                        <p class="text-xs text-slate-500">Apply a uniform profit margin across target services.</p>
                    </div>
                </div>
                <button @click="bulkMarginModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <!-- Target Scope Selection -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">Target Services Scope</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            @click="marginScope = 'selected'" 
                            :disabled="selectedIds.length === 0"
                            class="p-3 rounded-xl border text-left transition-all"
                            :class="marginScope === 'selected' ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 disabled:opacity-40'"
                        >
                            <span class="block">Selected Services Only</span>
                            <span class="text-[10px] text-slate-500 font-normal" x-text="selectedIds.length + ' services selected'"></span>
                        </button>
                        <button 
                            type="button" 
                            @click="marginScope = 'all'" 
                            class="p-3 rounded-xl border text-left transition-all"
                            :class="marginScope === 'all' ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                        >
                            <span class="block">All Services</span>
                            <span class="text-[10px] text-slate-500 font-normal" x-text="services.length + ' total in database'"></span>
                        </button>
                    </div>
                </div>

                <!-- Margin Calculation Type -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-slate-700">Margin Calculation Model</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            @click="marginType = 'percentage'" 
                            class="p-3 rounded-xl border text-left transition-all"
                            :class="marginType === 'percentage' ? 'bg-indigo-50 border-indigo-300 text-indigo-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                        >
                            <span class="block">Percentage Markup (%)</span>
                            <span class="text-[10px] text-slate-500 font-normal">Markup calculated on provider cost</span>
                        </button>
                        <button 
                            type="button" 
                            @click="marginType = 'fixed'" 
                            class="p-3 rounded-xl border text-left transition-all"
                            :class="marginType === 'fixed' ? 'bg-indigo-50 border-indigo-300 text-indigo-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                        >
                            <span class="block">Fixed Margin ($)</span>
                            <span class="text-[10px] text-slate-500 font-normal">Fixed dollar addition onto cost</span>
                        </button>
                    </div>
                </div>

                <!-- Margin Value Input & Presets -->
                <div class="space-y-2">
                    <label class="block font-bold text-slate-700">
                        <span x-text="marginType === 'percentage' ? 'Markup Percentage (%)' : 'Fixed Amount Added ($)'"></span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            x-model.number="marginValue"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white"
                        >
                    </div>

                    <!-- Value Quick Presets -->
                    <div class="flex items-center gap-1.5">
                        <template x-if="marginType === 'percentage'">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="marginValue = 15" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+15%</button>
                                <button type="button" @click="marginValue = 25" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+25%</button>
                                <button type="button" @click="marginValue = 35" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+35%</button>
                                <button type="button" @click="marginValue = 50" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+50%</button>
                                <button type="button" @click="marginValue = 100" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+100%</button>
                            </div>
                        </template>
                        <template x-if="marginType === 'fixed'">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="marginValue = 0.20" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+$0.20</button>
                                <button type="button" @click="marginValue = 0.35" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+$0.35</button>
                                <button type="button" @click="marginValue = 0.50" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+$0.50</button>
                                <button type="button" @click="marginValue = 1.00" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-semibold text-slate-700">+$1.00</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Preview Calculation Card -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-slate-600">
                    <span class="font-bold text-slate-800 block">Calculation Preview:</span>
                    <p class="text-[11px] leading-relaxed">
                        For a service with provider cost of <strong class="text-slate-900">$0.40</strong>:
                    </p>
                    <p class="font-mono text-emerald-700 font-bold text-xs" x-text="getPreviewText()"></p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button 
                    type="button" 
                    @click="bulkMarginModal = false" 
                    class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    @click="applyBulkMargin()" 
                    :disabled="loading"
                    class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition-all disabled:opacity-50"
                >
                    <span x-show="!loading">Confirm & Apply Margin</span>
                    <span x-show="loading">Applying...</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function adminServicesManager() {
    return {
        services: <?= json_encode(array_map(function($s) {
            return [
                'id' => (int)$s['id'],
                'service_code' => $s['service_code'],
                'service_name' => $s['service_name'],
                'custom_price' => (float)$s['custom_price'],
                'profit_margin_percent' => (float)($s['profit_margin_percent'] ?? 20.0),
                'avg_cost' => (float)($s['avg_cost'] ?? 0.0),
                'mapped_countries' => (int)($s['mapped_countries'] ?? 0),
                'orders_count' => (int)($s['orders_count'] ?? 0),
                'is_active' => (int)$s['is_active']
            ];
        }, $services), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        
        selectedIds: [],
        search: '',
        statusFilter: 'all',
        loading: false,

        // Bulk Margin Modal State
        bulkMarginModal: false,
        marginScope: 'selected',
        marginType: 'percentage',
        marginValue: 25.0,

        get filteredServices() {
            return this.services.filter(s => {
                // Status Filter
                if (this.statusFilter === 'active' && s.is_active != 1) return false;
                if (this.statusFilter === 'disabled' && s.is_active != 0) return false;

                // Search query
                if (!this.search.trim()) return true;
                const term = this.search.toLowerCase().trim();
                return s.service_name.toLowerCase().includes(term) || s.service_code.toLowerCase().includes(term);
            });
        },

        isAllSelected() {
            if (this.filteredServices.length === 0) return false;
            return this.filteredServices.every(s => this.selectedIds.includes(s.id));
        },

        toggleSelectAll() {
            if (this.isAllSelected()) {
                const currentFilteredIds = this.filteredServices.map(s => s.id);
                this.selectedIds = this.selectedIds.filter(id => !currentFilteredIds.includes(id));
            } else {
                const newIds = this.filteredServices.map(s => s.id);
                this.selectedIds = Array.from(new Set([...this.selectedIds, ...newIds]));
            }
        },

        calculateMargin(cost, price) {
            if (price <= 0) return 0;
            if (cost <= 0) return 20;
            return Math.round(((price - cost) / price) * 100);
        },

        getPreviewText() {
            const sampleCost = 0.40;
            if (this.marginType === 'percentage') {
                const newPrice = Math.max(0.10, sampleCost * (1 + (this.marginValue / 100)));
                return `New Selling Price: $${newPrice.toFixed(2)} (+${this.marginValue}% markup)`;
            } else {
                const newPrice = Math.max(0.10, sampleCost + this.marginValue);
                return `New Selling Price: $${newPrice.toFixed(2)} (+$${this.marginValue.toFixed(2)} added)`;
            }
        },

        openBulkMarginModal() {
            if (this.selectedIds.length > 0) {
                this.marginScope = 'selected';
            } else {
                this.marginScope = 'all';
            }
            this.bulkMarginModal = true;
        },

        async confirmBulkDelete() {
            if (this.selectedIds.length === 0) return;

            const count = this.selectedIds.length;
            const prompt = await Swal.fire({
                title: `Delete ${count} Selected Services?`,
                html: `
                    <div class="text-left text-xs text-slate-600 space-y-2 mt-2">
                        <p class="font-medium">You are about to delete <strong>${count}</strong> services from the platform.</p>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800">
                            <strong>Note on Historical Integrity:</strong> Services with existing user orders will be safely deactivated and delisted to protect historical customer order logs.
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: `Delete ${count} Services`,
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });

            if (!prompt.isConfirmed) return;

            this.loading = true;
            Swal.fire({
                title: 'Deleting Services...',
                text: 'Processing bulk deletion and preserving historical references...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                this.selectedIds.forEach(id => formData.append('service_ids[]', id));
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('format', 'json');

                const res = await fetch('/admin/services/bulk-delete', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Bulk Deletion Complete',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: data.message || 'Unable to delete services.'
                    });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Server communication error.' });
            } finally {
                this.loading = false;
            }
        },

        async confirmDeleteAll() {
            const prompt1 = await Swal.fire({
                title: 'Delete ALL Services?',
                html: `
                    <div class="text-left text-xs text-slate-600 space-y-2 mt-2">
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 font-semibold">
                            ⚠️ DANGER: This will remove all service records currently configured in the database.
                        </div>
                        <p>Customer accounts, wallet balances, and historical orders will remain strictly protected and intact.</p>
                        <p class="font-bold text-slate-800">To proceed, please type <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-700">DELETE ALL</code> below:</p>
                    </div>
                `,
                input: 'text',
                inputPlaceholder: 'DELETE ALL',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Confirm & Erase All',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });

            if (!prompt1.isConfirmed || prompt1.value !== 'DELETE ALL') {
                if (prompt1.isConfirmed) {
                    Swal.fire({ icon: 'info', title: 'Action Cancelled', text: 'Keyword confirmation mismatch.' });
                }
                return;
            }

            this.loading = true;
            Swal.fire({
                title: 'Erasing All Services...',
                text: 'Safely clearing catalog while preserving customer order history...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('confirm', 'DELETE_ALL');
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('format', 'json');

                const res = await fetch('/admin/services/delete-all', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'All Services Removed',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operation Failed',
                        text: data.message || 'Failed to delete services.'
                    });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Server communication error.' });
            } finally {
                this.loading = false;
            }
        },

        async applyBulkMargin() {
            if (this.marginValue < 0) {
                Swal.fire({ icon: 'error', title: 'Invalid Value', text: 'Margin value cannot be negative.' });
                return;
            }

            const targetCount = this.marginScope === 'selected' ? this.selectedIds.length : this.services.length;
            const typeLabel = this.marginType === 'percentage' ? `${this.marginValue}%` : `$${this.marginValue.toFixed(2)}`;

            const confirm = await Swal.fire({
                title: 'Apply Bulk Margin?',
                text: `Set ${typeLabel} margin across ${targetCount} services? This will recalculate customer selling prices accordingly.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Yes, Apply Now',
                cancelButtonText: 'Cancel'
            });

            if (!confirm.isConfirmed) return;

            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('scope', this.marginScope);
                formData.append('margin_type', this.marginType);
                formData.append('margin_value', this.marginValue);
                if (this.marginScope === 'selected') {
                    this.selectedIds.forEach(id => formData.append('service_ids[]', id));
                }
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('format', 'json');

                const res = await fetch('/admin/services/bulk-margin', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    this.bulkMarginModal = false;
                    await Swal.fire({
                        icon: 'success',
                        title: 'Margins Applied',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Could not apply margins.' });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Server communication error.' });
            } finally {
                this.loading = false;
            }
        },

        async syncFromProvider() {
            this.loading = true;
            Swal.fire({
                title: 'Connecting to Provider API...',
                text: 'Fetching real countries, services, and live stock from upstream provider endpoint...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('provider_id', 1);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const res = await fetch('/admin/providers/sync', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Provider Sync Complete',
                        text: data.message,
                        timer: 2200,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({ icon: 'warning', title: 'Provider Sync Notice', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Server communication error during API sync.' });
            } finally {
                this.loading = false;
            }
        },

        saveSinglePrice(event, srv) {
            // Standard form submission proceeds normally
        }
    };
}
</script>

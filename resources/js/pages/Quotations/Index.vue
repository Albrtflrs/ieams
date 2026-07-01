<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useSettings } from '@/composables/useSettings';
import { useForm } from '@inertiajs/vue3';
import AppStatus from '@/Components/AppStatus.vue';

const props = defineProps({
    quotations: Object,
    items: Object,
    user_role: String,
    categories: Array,
    clients: Array,
    statuses: Array,
    filters: Object,
});

const { currency } = useSettings();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canViewTrash = computed(() => user.value && ['super_admin', 'admin'].includes(user.value.role));

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// ─── Filter state ──────────────────────────
const filters = ref({
    client_id: props.filters?.client_id || '',
    date_filter: props.filters?.date_filter || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || '',
});

const showCustomDate = computed(() => filters.value.date_filter === 'custom');

// ─── Debounce helper ──────────────────────────
function debounce(fn, delay) {
    let timeoutId = null;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// ─── Apply filters (called by watchers) ────────
const applyFilters = () => {
    const params = new URLSearchParams();

    if (filters.value.client_id) params.append('client_id', filters.value.client_id);
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.date_filter) params.append('date_filter', filters.value.date_filter);
    if (filters.value.start_date) params.append('start_date', filters.value.start_date);
    if (filters.value.end_date) params.append('end_date', filters.value.end_date);

    const queryString = params.toString();
    router.get(route('quotations.index') + (queryString ? '?' + queryString : ''), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const debouncedApply = debounce(applyFilters, 300);

watch(() => filters.value.client_id, debouncedApply);
watch(() => filters.value.status, debouncedApply);
watch(() => filters.value.date_filter, () => {
    if (filters.value.date_filter !== 'custom') {
        filters.value.start_date = '';
        filters.value.end_date = '';
    }
    debouncedApply();
});
watch(() => filters.value.start_date, debouncedApply);
watch(() => filters.value.end_date, debouncedApply);

// ─── Reset filters ────────────────────────────────
const resetFilters = () => {
    filters.value = { client_id: '', date_filter: '', start_date: '', end_date: '', status: '' };
    window.location.href = route('quotations.index');
};

// ─── Quotation Actions ──────────────────────────
const deleteQuotation = (id) => {
    if (confirm('Delete this quotation?')) {
        router.delete(route('quotations.destroy', id));
    }
};

const convertToIncome = (id) => {
    if (confirm('Convert this accepted quotation to Income?')) {
        router.post(route('quotations.convert-to-income', id));
    }
};

// ─── Status Mapping for AppStatus ──────────────
const statusMap = {
    draft: 'info',
    sent: 'info',
    accepted: 'success',
    rejected: 'danger',
    expired: 'warning',
    converted: 'success',
};

const getStatusType = (status) => statusMap[status] || 'info';

// ─── Item Management ────────────────────────────
const canManageItems = computed(() => ['super_admin', 'admin'].includes(props.user_role));

const showItemModal = ref(false);
const itemForm = useForm({
    name: '',
    description: '',
    default_cost_price: 0,
    default_markup_percentage: 0,
    default_selling_price: 0,
    category: '',
});

const calculateSellingPrice = () => {
    const cost = parseFloat(itemForm.default_cost_price) || 0;
    const markup = parseFloat(itemForm.default_markup_percentage) || 0;
    if (cost > 0 && markup >= 0) {
        itemForm.default_selling_price = cost * (1 + markup / 100);
    } else {
        itemForm.default_selling_price = 0;
    }
};

watch(
    () => [itemForm.default_cost_price, itemForm.default_markup_percentage],
    () => calculateSellingPrice(),
    { immediate: true, deep: true }
);

const addItem = () => {
    calculateSellingPrice();
    itemForm.post(route('items.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showItemModal.value = false;
            itemForm.reset();
        },
    });
};

const deleteItem = (id) => {
    if (confirm('Delete this item?')) {
        router.delete(route('items.destroy', id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Quotations</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Quotations</h1>
                <div class="flex items-center gap-3">
                    <!-- 👇 Trash button (admin only) -->
                    <Link
                        v-if="canViewTrash"
                        href="/quotations/trash-bin"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-800/30 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Trash
                    </Link>
                    <button
                        @click="showItemModal = true"
                        :disabled="!canManageItems"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition"
                        :class="!canManageItems ? 'opacity-50 cursor-not-allowed' : ''"
                        :title="!canManageItems ? 'Only Admin can add new items' : 'Manage item catalog'"
                    >
                        🛒 Manage Items
                    </button>
                    <Link
                        :href="route('quotations.create')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                    >
                        + New Quotation
                    </Link>
                </div>
            </div>

            <!-- ─── Filters ──────────────────────────────── -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-4 flex flex-wrap items-end gap-4">
                <!-- Client Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Client</label>
                    <select
                        v-model="filters.client_id"
                        class="w-48 border rounded px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 text-sm"
                    >
                        <option value="">All Clients</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select
                        v-model="filters.status"
                        class="w-36 border rounded px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 text-sm"
                    >
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <select
                        v-model="filters.date_filter"
                        class="w-36 border rounded px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 text-sm"
                    >
                        <option value="">All</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <!-- Custom Date Range -->
                <div v-if="showCustomDate" class="flex items-center gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">From</label>
                        <input
                            type="date"
                            v-model="filters.start_date"
                            class="w-36 border rounded px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">To</label>
                        <input
                            type="date"
                            v-model="filters.end_date"
                            class="w-36 border rounded px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 text-sm"
                        />
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="flex gap-2">
                    <button
                        @click="resetFilters"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-1.5 rounded transition text-sm"
                    >
                        Reset Filters
                    </button>
                    <span class="text-xs text-gray-500 ml-2 self-center">{{ quotations.total }} records</span>
                </div>
            </div>

            <!-- ─── Quotations Table ──────────────────── -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date Issued</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="q in quotations.data" :key="q.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2 font-mono">{{ q.quotation_number }}</td>
                            <td class="px-4 py-2">{{ q.client_name }}</td>
                            <td class="px-4 py-2">{{ q.date_issued }}</td>
                            <td class="px-4 py-2 font-semibold">{{ peso(q.total_amount) }}</td>
                            <td class="px-4 py-2">
                                <AppStatus :type="getStatusType(q.status)" :label="q.status" size="sm" />
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <Link
                                    :href="route('quotations.show', q.id)"
                                    class="text-green-600 dark:text-green-400 hover:underline mr-2"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="route('quotations.edit', q.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline mr-2"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteQuotation(q.id)"
                                    class="text-red-600 dark:text-red-400 hover:underline mr-2"
                                >
                                    Delete
                                </button>
                                <button
                                    v-if="q.status === 'accepted' && !q.converted_to_income_id"
                                    @click="convertToIncome(q.id)"
                                    class="text-green-600 dark:text-green-400 hover:underline"
                                >
                                    Convert
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!quotations.data || quotations.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No quotations found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ─── Items Catalog Section ──────────────────── -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        📦 Item Catalog
                    </h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ items?.length || 0 }} items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Name</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Cost Price</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Markup %</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Selling Price</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-3 py-1.5">
                                    <div class="font-medium">{{ item.name }}</div>
                                    <div v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</div>
                                </td>
                                <td class="px-3 py-1.5">{{ peso(item.default_cost_price) }}</td>
                                <td class="px-3 py-1.5">{{ item.default_markup_percentage }}%</td>
                                <td class="px-3 py-1.5">{{ peso(item.default_selling_price) }}</td>
                                <td class="px-3 py-1.5">
                                    <Link
                                        :href="route('items.edit', item.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:underline mr-2"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="canManageItems"
                                        @click="deleteItem(item.id)"
                                        class="text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        Delete
                                    </button>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                            </tr>
                            <tr v-if="!items || items.length === 0">
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500 text-sm">No items in catalog. Click "Manage Items" to add.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ─── Pagination ────────────────────────── -->
            <div v-if="quotations.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in quotations.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>

        <!-- ─── Add Item Modal ────────────────────────── -->
        <div v-if="showItemModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h2 class="text-xl font-bold">Add New Item</h2>
                    <button @click="showItemModal = false" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form @submit.prevent="addItem" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name *</label>
                        <input
                            v-model="itemForm.name"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            required
                        />
                        <div v-if="itemForm.errors.name" class="text-red-500 text-sm">{{ itemForm.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea
                            v-model="itemForm.description"
                            rows="2"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                v-model.number="itemForm.default_cost_price"
                                @input="calculateSellingPrice"
                                class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Markup %</label>
                            <input
                                type="number"
                                step="0.1"
                                min="0"
                                v-model.number="itemForm.default_markup_percentage"
                                @input="calculateSellingPrice"
                                class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>
                    </div>

                    <!-- Selling Price (auto-calculated, read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price</label>
                        <input
                            type="number"
                            step="0.01"
                            v-model="itemForm.default_selling_price"
                            readonly
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed"
                        />
                    </div>

                    <!-- Category Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <select
                            v-model="itemForm.category"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <option value="">Select Category</option>
                            <option v-for="cat in props.categories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-4 border-t dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="itemForm.processing"
                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition"
                        >
                            Add Item
                        </button>
                        <button
                            type="button"
                            @click="showItemModal = false"
                            class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
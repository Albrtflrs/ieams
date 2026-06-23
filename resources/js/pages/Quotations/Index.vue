<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useSettings } from '@/composables/useSettings';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    quotations: Object,
    items: Object, // 👈 ADDED
    user_role: String, // 👈 ADDED
});

const { currency } = useSettings();

const peso = (val) => `${currency.value}${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

// ─── Quotation Actions ──────────────────────
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

// ─── Item Management ──────────────────────
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

const addItem = () => {
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

// ─── Status Badge ──────────────────────────
const statusBadgeClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        accepted: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        expired: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        converted: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
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
                <div class="flex gap-2">
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
                        v-if="canManageItems"
                        :href="route('quotations.create')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                    >
                        + New Quotation
                    </Link>
                    <Link
                        v-else
                        :href="route('quotations.create')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                    >
                        + New Quotation
                    </Link>
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
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize" :class="statusBadgeClass(q.status)">
                                    {{ q.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <Link :href="route('quotations.edit', q.id)" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                                <button @click="deleteQuotation(q.id)" class="text-red-600 dark:text-red-400 hover:underline mr-2">Delete</button>
                                <button
                                    v-if="q.status === 'accepted' && !q.converted_to_income_id"
                                    @click="convertToIncome(q.id)"
                                    class="text-green-600 dark:text-green-400 hover:underline"
                                >
                                    Convert to Income
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
                                    <button
                                        v-if="canManageItems"
                                        @click="deleteItem(item.id)"
                                        class="text-red-600 dark:text-red-400 hover:underline text-xs"
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
                                v-model="itemForm.default_cost_price"
                                class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Markup %</label>
                            <input
                                type="number"
                                step="0.1"
                                v-model="itemForm.default_markup_percentage"
                                class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <input
                            v-model="itemForm.category"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                            placeholder="e.g., Office Supplies, IT Equipment"
                        />
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
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object,
    summary: Object,
});

const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const deleteMisc = (id) => {
    if (confirm('Delete this record?')) {
        router.delete(route('misc.destroy', id));
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Miscellaneous</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Miscellaneous Transactions</h1>
                <Link :href="route('misc.create')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition">
                    + Add Record
                </Link>
            </div>

            <!-- Summary Cards with tooltips -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow"
                     title="Total miscellaneous income for the selected period.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Income</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_income) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow"
                     title="Total miscellaneous expenses for the selected period.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expense) }}</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-blue-600 text-white rounded-lg p-4 shadow"
                     title="Net = Total Income − Total Expenses for the selected period.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Net</p>
                    <p class="text-2xl font-bold" :class="summary.net < 0 ? 'text-red-200' : 'text-green-200'">
                        {{ peso(summary.net) }}
                    </p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-violet-600 text-white rounded-lg p-4 shadow"
                     title="Total number of miscellaneous records for the selected period.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Records</p>
                    <p class="text-2xl font-bold">{{ summary.count ?? 0 }}</p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="t in transactions.data" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2">{{ t.id }}</td>
                            <td class="px-4 py-2 capitalize">
                                <span :class="t.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    {{ t.type }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ t.date }}</td>
                            <td class="px-4 py-2 font-medium"
                                :class="t.type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                ₱{{ t.amount.toLocaleString() }}
                            </td>
                            <td class="px-4 py-2">{{ t.category || '-' }}</td>
                            <td class="px-4 py-2 max-w-xs truncate" :title="t.description || ''">{{ t.description || '-' }}</td>
                            <td class="px-4 py-2">{{ t.reference_number || '-' }}</td>
                            <td class="px-4 py-2">
                                <Link :href="route('misc.edit', t.id)" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">Edit</Link>
                                <button @click="deleteMisc(t.id)" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!transactions.data || transactions.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No miscellaneous transactions found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in transactions.links" :key="link.label">
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
    </AppLayout>
</template>
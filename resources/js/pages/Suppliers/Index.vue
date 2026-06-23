<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    suppliers: Object,
    summary: Object,
});

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'contact_person', label: 'Contact Person' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
];

const peso = (val) => `₱${Number(val ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const deleteSupplier = (id) => {
    if (confirm('Delete this supplier?')) {
        router.delete(route('suppliers.destroy', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-bold">Suppliers</h1>
                <Link :href="route('suppliers.create')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Supplier</Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Suppliers</p>
                    <p class="text-2xl font-bold">{{ summary.total_suppliers ?? 0 }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Expenses</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_expenses) }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-violet-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Top Supplier (Spend)</p>
                    <p class="text-lg font-bold">{{ summary.top_supplier_amount?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ peso(summary.top_supplier_amount?.amount) }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Most Used</p>
                    <p class="text-lg font-bold">{{ summary.top_supplier_count?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ summary.top_supplier_count?.count ?? 0 }} transactions</p>
                </div>
            </div>

            <!-- DataTable -->
            <DataTable :columns="columns" :data="suppliers.data">
                <template #column-email="{ value }">
                    <a :href="`mailto:${value}`" class="text-blue-600 hover:underline">{{ value || '-' }}</a>
                </template>
                <template #actions="{ row }">
                    <Link :href="route('suppliers.edit', row.id)" class="text-blue-600 mr-2">Edit</Link>
                    <button @click="deleteSupplier(row.id)" class="text-red-600">Delete</button>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div v-if="suppliers.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in suppliers.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
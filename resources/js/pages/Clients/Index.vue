<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    clients: Object,
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

function destroy(id) {
    if (confirm('Delete this client?')) {
        router.delete(route('clients.destroy', id));
    }
}
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-bold">Clients</h1>
                <Link :href="route('clients.create')" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Add Client
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Clients</p>
                    <p class="text-2xl font-bold">{{ summary.total_clients ?? 0 }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Revenue</p>
                    <p class="text-2xl font-bold">{{ peso(summary.total_revenue) }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-violet-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Top Client (Revenue)</p>
                    <p class="text-lg font-bold">{{ summary.top_client_revenue?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ peso(summary.top_client_revenue?.amount) }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-lg p-4 shadow">
                    <p class="text-sm uppercase tracking-wider opacity-80">Most Active</p>
                    <p class="text-lg font-bold">{{ summary.top_client_count?.name || '—' }}</p>
                    <p class="text-sm opacity-80">{{ summary.top_client_count?.count ?? 0 }} transactions</p>
                </div>
            </div>

            <!-- DataTable -->
            <DataTable :columns="columns" :data="clients.data">
                <template #column-contact_person="{ value }">
                    {{ value ?? '—' }}
                </template>
                <template #column-phone="{ value }">
                    {{ value ?? '—' }}
                </template>
                <template #column-email="{ value }">
                    {{ value ?? '—' }}
                </template>
                <template #actions="{ row }">
                    <Link :href="route('clients.edit', row.id)" class="text-blue-600 hover:underline mr-2">Edit</Link>
                    <button @click="destroy(row.id)" class="text-red-600 hover:underline">Delete</button>
                </template>
            </DataTable>

            <!-- Pagination -->
            <div class="mt-4 flex gap-2">
                <Link
                    v-for="link in clients.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="px-3 py-1 border rounded text-sm"
                    :class="{
                        'bg-blue-500 text-white border-blue-500': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url
                    }"
                />
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({ templates: Object });

function removeSchedule(id) {
    if (confirm('Remove this monthly bill schedule? Existing expense transactions will remain.')) {
        router.delete(route('expenses.recurring.destroy', id));
    }
}
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <Link :href="route('expenses.index')" class="text-sm text-gray-500 hover:underline">Expenses</Link>
                    <h1 class="text-2xl font-bold mt-1">Monthly Fixed Bills</h1>
                    <p class="text-sm text-gray-500 mt-1">Active schedules create an unpaid expense on each due date. Editing or removing a schedule stops or changes future bills; past transactions stay in Expenses.</p>
                </div>
                <Link :href="route('expenses.recurring.create')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">+ Add Monthly Bill</Link>
            </div>

            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-left">
                        <tr>
                            <th class="p-3">Subcategory</th><th class="p-3">Supplier</th><th class="p-3">Description</th><th class="p-3">Amount</th><th class="p-3">Next Due</th><th class="p-3">Status</th><th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="template in templates.data" :key="template.id" class="border-t dark:border-gray-700">
                            <td class="p-3">{{ template.subcategory }}</td>
                            <td class="p-3">{{ template.supplier_name || '-' }}</td>
                            <td class="p-3">{{ template.description }}</td>
                            <td class="p-3">{{ Number(template.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</td>
                            <td class="p-3">{{ template.next_due_date }}</td>
                            <td class="p-3"><span :class="template.is_active ? 'text-green-700' : 'text-gray-500'">{{ template.is_active ? 'Active' : 'Paused' }}</span></td>
                            <td class="p-3 whitespace-nowrap">
                                <Link :href="route('expenses.recurring.edit', template.id)" class="text-blue-600 hover:underline mr-3">Edit</Link>
                                <button type="button" @click="removeSchedule(template.id)" class="text-red-600 hover:underline">Remove</button>
                            </td>
                        </tr>
                        <tr v-if="templates.data.length === 0"><td colspan="7" class="p-8 text-center text-gray-500">No monthly bills scheduled.</td></tr>
                    </tbody>
                </table>
            </div>

            <div v-if="templates.links" class="flex flex-wrap gap-2">
                <Link v-for="link in templates.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 border rounded text-sm" :class="{ 'bg-blue-600 text-white': link.active, 'pointer-events-none opacity-50': !link.url }" />
            </div>
        </div>
    </AppLayout>
</template>

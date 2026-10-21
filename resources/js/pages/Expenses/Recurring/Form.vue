<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableEntitySelect from '@/Components/SearchableEntitySelect.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ template: Object, selectedSupplier: Object });
const today = new Date();
const localToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const form = useForm({
    supplier_id: props.selectedSupplier?.id ?? null,
    subcategory: props.template?.subcategory ?? '',
    description: props.template?.description ?? '',
    amount: props.template?.amount ?? '',
    payment_method: props.template?.payment_method ?? '',
    next_due_date: props.template?.next_due_date?.substring(0, 10) ?? localToday,
    is_active: props.template ? Boolean(props.template.is_active) : true,
});

function submit() {
    if (props.template) {
        form.put(route('expenses.recurring.update', props.template.id));
    } else {
        form.post(route('expenses.recurring.store'));
    }
}
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-2xl mx-auto">
            <Link :href="route('expenses.recurring.index')" class="text-sm text-gray-500 hover:underline">Monthly Fixed Bills</Link>
            <h1 class="text-2xl font-bold mt-1 mb-5">{{ template ? 'Edit Monthly Bill' : 'Add Monthly Bill' }}</h1>

            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
                <div>
                    <label class="block text-sm font-medium mb-1">Supplier</label>
                    <SearchableEntitySelect v-model="form.supplier_id" :selected-option="selectedSupplier" search-route="suppliers.search" placeholder="Search supplier by name..." empty-label="No supplier" />
                    <div v-if="form.errors.supplier_id" class="text-red-500 text-sm">{{ form.errors.supplier_id }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bill Subcategory *</label>
                    <input v-model="form.subcategory" list="monthly-bill-types" required maxlength="255" placeholder="Electricity, Water, Internet, Rent..." class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <datalist id="monthly-bill-types"><option value="Electricity" /><option value="Water" /><option value="Internet" /><option value="Rent" /><option value="Other" /></datalist>
                    <div v-if="form.errors.subcategory" class="text-red-500 text-sm">{{ form.errors.subcategory }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description *</label>
                    <textarea v-model="form.description" required maxlength="2000" rows="3" placeholder="Account, service address, or bill details" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Monthly Amount *</label>
                    <input v-model.number="form.amount" type="number" min="0.01" step="0.01" required class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm">{{ form.errors.amount }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <input v-model="form.payment_method" maxlength="50" placeholder="Cash, bank transfer, online..." class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.payment_method" class="text-red-500 text-sm">{{ form.errors.payment_method }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Next Bill Date *</label>
                    <input v-model="form.next_due_date" type="date" :min="localToday" required class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <p class="text-xs text-gray-500 mt-1">Future bills repeat monthly on this day. Months without this day use their last day.</p>
                    <div v-if="form.errors.next_due_date" class="text-red-500 text-sm">{{ form.errors.next_due_date }}</div>
                </div>
                <label v-if="template" class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" />
                    Schedule active
                </label>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-emerald-600 text-white px-5 py-2 rounded-lg">{{ template ? 'Save Changes' : 'Create Schedule' }}</button>
                    <Link :href="route('expenses.recurring.index')" class="bg-gray-200 dark:bg-gray-700 px-5 py-2 rounded-lg">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

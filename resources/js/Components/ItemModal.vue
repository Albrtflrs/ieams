<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    name: '',
    description: '',
    cost_price: 0,
    markup_percentage: 0,
    selling_price: 0,
    category: '',
});

const submit = () => {
    form.post(route('items.store'), {
        onSuccess: () => {
            emit('saved');
            resetForm();
        },
    });
};

const resetForm = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Add New Item</h2>

            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Item Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <input
                            v-model="form.description"
                            type="text"
                            class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                    </div>

                    <!-- Cost Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price</label>
                        <input
                            v-model.number="form.cost_price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                    </div>

                    <!-- Markup % -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Markup %</label>
                        <input
                            v-model.number="form.markup_percentage"
                            type="number"
                            step="0.1"
                            min="0"
                            class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                    </div>

                    <!-- 👇 Category Dropdown (NEW) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <select
                            v-model="form.category"
                            class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <option value="">Select Category</option>
                            <option v-for="cat in props.categories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        type="button"
                        @click="resetForm"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded disabled:opacity-50"
                    >
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
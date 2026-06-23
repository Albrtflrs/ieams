<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    rowKey: { type: String, default: 'id' },
});

const rows = computed(() => props.data);
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 border">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th v-for="col in columns" :key="col.key" class="px-4 py-2 text-left">
                        {{ col.label }}
                    </th>
                    <th v-if="$slots.actions" class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row[rowKey]" class="border-t">
                    <td v-for="col in columns" :key="col.key" class="px-4 py-2">
                        <slot :name="`column-${col.key}`" :row="row" :value="row[col.key]">
                            {{ row[col.key] }}
                        </slot>
                    </td>
                    <td v-if="$slots.actions" class="px-4 py-2">
                        <slot name="actions" :row="row" />
                    </td>
                </tr>
                <tr v-if="rows.length === 0">
                    <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-2 text-center text-gray-500">
                        No data found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script setup>
import { ref, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';

const props = defineProps({
    modelValue: Boolean,
    initialGross: { type: Number, default: 0 },
    initialRoyalty: { type: Number, default: 0 },
    initialDeductions: { type: Number, default: 0 },
});

const emit = defineEmits(['update:modelValue', 'apply']);

const localGross = ref(props.initialGross);
const localRoyalty = ref(props.initialRoyalty);
const localDeductions = ref(props.initialDeductions);

const royaltyGross = ref(0);
const netSales = ref(0);
const netCashConversion = ref(0);

const updateCalculations = () => {
    royaltyGross.value = localGross.value * (localRoyalty.value / 100);
    netSales.value = localGross.value - localDeductions.value;
    netCashConversion.value = localGross.value - royaltyGross.value - localDeductions.value;
};

watch([localGross, localRoyalty, localDeductions], () => updateCalculations(), { immediate: true });

const applyAndClose = () => {
    emit('apply', {
        gross_price: localGross.value,
        royalty_percent: localRoyalty.value,
        deductions: localDeductions.value,
    });
    emit('update:modelValue', false);
};
</script>

<template>
    <Dialog :open="modelValue" @close="$emit('update:modelValue', false)" class="relative z-50">
        <div class="fixed inset-0 bg-black/50" aria-hidden="true" />
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <DialogTitle class="text-lg font-bold mb-4">Income Calculator</DialogTitle>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium">Gross Price (₱)</label>
                        <input v-model.number="localGross" type="number" step="0.01" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Royalty (%)</label>
                        <input v-model.number="localRoyalty" type="number" step="0.1" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Deductions (₱)</label>
                        <input v-model.number="localDeductions" type="number" step="0.01" class="w-full border rounded p-2" />
                    </div>
                    <div class="border-t pt-3 mt-2">
                        <p><strong>Royalty Gross:</strong> ₱{{ royaltyGross.toFixed(2) }}</p>
                        <p><strong>Net Sales:</strong> ₱{{ netSales.toFixed(2) }}</p>
                        <p><strong>Net Cash Conversion:</strong> ₱{{ netCashConversion.toFixed(2) }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="$emit('update:modelValue', false)" class="px-3 py-1.5 border rounded">Cancel</button>
                    <button @click="applyAndClose" class="px-3 py-1.5 bg-blue-500 text-white rounded">Apply</button>
                </div>
            </DialogPanel>
        </div>
    </Dialog>
</template>
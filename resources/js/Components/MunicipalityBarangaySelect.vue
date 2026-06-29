<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    municipalities: {
        type: Array,
        default: () => [],   // 👈 Always an array, never undefined
    },
    modelValue: {
        type: Object,
        default: () => ({ municipality: '', barangay: '' }),
    },
});

const emit = defineEmits(['update:modelValue']);

const OTHER = '__other__';

const selectedMunicipality = ref(props.modelValue.municipality || '');
const selectedBarangay = ref(props.modelValue.barangay || '');
const customMunicipality = ref('');
const customBarangay = ref('');

// Safely check if municipality exists in list – guard against undefined
const isKnownMunicipality = computed(() => {
    const munis = props.municipalities || [];
    return munis.some(m => m.name === selectedMunicipality.value);
});

// Safely get barangays for the selected municipality
const currentBarangays = computed(() => {
    const munis = props.municipalities || [];
    const found = munis.find(m => m.name === selectedMunicipality.value);
    return found?.barangays || [];
});

// When municipality changes, reset barangay selection
watch(selectedMunicipality, () => {
    selectedBarangay.value = '';
    customBarangay.value = '';
});

const emitValue = () => {
    const municipality = selectedMunicipality.value === OTHER
        ? customMunicipality.value
        : selectedMunicipality.value;

    const barangay = !isKnownMunicipality.value || selectedBarangay.value === OTHER
        ? customBarangay.value
        : selectedBarangay.value;

    emit('update:modelValue', { municipality, barangay });
};

watch([selectedMunicipality, selectedBarangay, customMunicipality, customBarangay], emitValue);
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Municipality -->
        <div>
            <label class="block text-sm font-medium mb-1">Municipality</label>
            <select v-model="selectedMunicipality" class="w-full border rounded px-3 py-2">
                <option value="">Select municipality</option>
                <option v-for="m in (municipalities || [])" :key="m.id" :value="m.name">{{ m.name }}</option>
                <option :value="OTHER">Other (outside Aklan)</option>
            </select>

            <input
                v-if="selectedMunicipality === OTHER"
                v-model="customMunicipality"
                type="text"
                placeholder="Enter municipality / city / province"
                class="w-full border rounded px-3 py-2 mt-2"
            />
        </div>

        <!-- Barangay -->
        <div>
            <label class="block text-sm font-medium mb-1">Barangay</label>

            <select
                v-if="isKnownMunicipality"
                v-model="selectedBarangay"
                class="w-full border rounded px-3 py-2"
            >
                <option value="">Select barangay</option>
                <option v-for="b in currentBarangays" :key="b" :value="b">{{ b }}</option>
                <option :value="OTHER">Other / not listed</option>
            </select>

            <input
                v-if="!isKnownMunicipality || selectedBarangay === OTHER"
                v-model="customBarangay"
                type="text"
                placeholder="Enter barangay"
                class="w-full border rounded px-3 py-2"
                :class="{ 'mt-2': isKnownMunicipality }"
            />
        </div>
    </div>
</template>
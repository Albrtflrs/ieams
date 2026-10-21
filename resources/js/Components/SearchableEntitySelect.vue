<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: null },
    selectedOption: { type: Object, default: null },
    searchRoute: { type: String, required: true },
    placeholder: { type: String, default: 'Type at least 2 characters to search...' },
    allowEmpty: { type: Boolean, default: true },
    emptyLabel: { type: String, default: 'Clear selection' },
});
const emit = defineEmits(['update:modelValue']);

const search = ref(props.selectedOption?.name || '');
const selected = ref(props.selectedOption);
const results = ref([]);
const open = ref(false);
const loading = ref(false);
let timer;
let requestId = 0;

watch(() => props.selectedOption, (option) => {
    selected.value = option;
    search.value = option?.name || '';
});

watch(search, (value) => {
    if (selected.value && value === selected.value.name) return;
    clearTimeout(timer);
    selected.value = null;
    emit('update:modelValue', props.allowEmpty ? null : '');
    results.value = [];
    const term = value.trim();
    if (term.length < 2) {
        loading.value = false;
        return;
    }
    timer = setTimeout(async () => {
        const id = ++requestId;
        loading.value = true;
        try {
            const url = new URL(route(props.searchRoute), window.location.origin);
            url.searchParams.set('q', term);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Search request failed');
            const data = await response.json();
            if (id === requestId) results.value = data;
        } catch {
            if (id === requestId) results.value = [];
        } finally {
            if (id === requestId) loading.value = false;
        }
    }, 2000);
});

function choose(option) {
    selected.value = option;
    search.value = option.name;
    results.value = [];
    open.value = false;
    emit('update:modelValue', option.id);
}

function clear() {
    selected.value = null;
    search.value = '';
    results.value = [];
    open.value = false;
    emit('update:modelValue', props.allowEmpty ? null : '');
}
</script>

<template>
    <div class="relative">
        <div class="flex gap-2">
            <input
                v-model="search"
                type="search"
                :placeholder="placeholder"
                autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"
                @focus="open = true"
                @keydown.esc="open = false"
            />
            <button v-if="selected || search" type="button" class="px-3 border rounded-lg dark:border-gray-600" @click="clear">Clear</button>
        </div>
        <p v-if="selected" class="text-xs text-gray-500 mt-1">
            Selected: {{ selected.name }}<span v-if="selected.institution_type"> · {{ selected.institution_type }}</span>
        </p>
        <div v-if="open && search.trim().length >= 2" class="absolute z-20 mt-1 w-full rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-600 shadow-lg max-h-60 overflow-auto">
            <div v-if="loading" class="px-3 py-2 text-sm text-gray-500">Searching...</div>
            <button v-for="option in results" :key="option.id" type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700" @mousedown.prevent="choose(option)">
                <span>{{ option.name }}</span>
                <span v-if="option.institution_type" class="ml-2 text-xs text-gray-500">{{ option.institution_type }}</span>
            </button>
            <div v-if="!loading && results.length === 0" class="px-3 py-2 text-sm text-gray-500">No matches found.</div>
            <button v-if="allowEmpty" type="button" class="block w-full px-3 py-2 text-left text-sm border-t dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700" @mousedown.prevent="clear">{{ emptyLabel }}</button>
        </div>
    </div>
</template>

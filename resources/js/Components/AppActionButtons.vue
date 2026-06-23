<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    editRoute: String,
    deleteAction: Function,
    viewRoute: String,
    showView: { type: Boolean, default: false },
    size: { type: String, default: 'sm' }, // sm, md
});

const sizeClasses = {
    sm: 'px-2 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
};
</script>

<template>
    <div class="flex items-center gap-2">
        <Link
            v-if="viewRoute"
            :href="viewRoute"
            class="btn btn-secondary"
            :class="sizeClasses[size]"
        >
            View
        </Link>
        <Link
            v-if="editRoute"
            :href="editRoute"
            class="btn btn-primary"
            :class="sizeClasses[size]"
        >
            Edit
        </Link>
        <button
            v-if="deleteAction"
            @click="deleteAction"
            class="btn btn-danger"
            :class="sizeClasses[size]"
        >
            Delete
        </button>
    </div>
</template>
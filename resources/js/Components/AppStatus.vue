<script setup>
const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (v) => ['paid', 'unpaid', 'active', 'expired', 'used_up', 'success', 'warning', 'danger', 'info'].includes(v)
    },
    label: String,
    size: { type: String, default: 'sm' }
});

const colorMap = {
    paid: 'bg-emerald-50/80 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300',
    unpaid: 'bg-rose-50/80 border-rose-200 dark:bg-rose-900/30 dark:border-rose-700 text-rose-700 dark:text-rose-300',
    active: 'bg-green-50/80 border-green-200 dark:bg-green-900/30 dark:border-green-700 text-green-700 dark:text-green-300',
    expired: 'bg-gray-50/80 border-gray-200 dark:bg-gray-800/50 dark:border-gray-700 text-gray-600 dark:text-gray-400',
    used_up: 'bg-amber-50/80 border-amber-200 dark:bg-amber-900/30 dark:border-amber-700 text-amber-700 dark:text-amber-300',
    success: 'bg-emerald-50/80 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300',
    warning: 'bg-amber-50/80 border-amber-200 dark:bg-amber-900/30 dark:border-amber-700 text-amber-700 dark:text-amber-300',
    danger: 'bg-rose-50/80 border-rose-200 dark:bg-rose-900/30 dark:border-rose-700 text-rose-700 dark:text-rose-300',
    info: 'bg-sky-50/80 border-sky-200 dark:bg-sky-900/30 dark:border-sky-700 text-sky-700 dark:text-sky-300',
};

const dotColor = {
    paid: 'bg-emerald-500',
    unpaid: 'bg-rose-500',
    active: 'bg-green-500',
    expired: 'bg-gray-400 dark:bg-gray-500',
    used_up: 'bg-amber-500',
    success: 'bg-emerald-500',
    warning: 'bg-amber-500',
    danger: 'bg-rose-500',
    info: 'bg-sky-500',
};

const sizeClasses = {
    sm: 'px-2.5 py-0.5 text-[0.7rem] gap-1.5',
    md: 'px-3.5 py-1 text-sm gap-2',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border font-medium shadow-sm transition-all hover:shadow-md"
        :class="[colorMap[type], sizeClasses[size]]"
    >
        <span class="inline-block w-1.5 h-1.5 rounded-full" :class="dotColor[type]"></span>
        <slot>{{ label || type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</slot>
    </span>
</template>
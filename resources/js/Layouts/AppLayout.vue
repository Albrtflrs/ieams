<script setup>
import { computed } from 'vue'; // <-- THIS WAS MISSING
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const isLoggedIn = computed(() => !!user.value);
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-gray-950 transition-colors duration-300">
        <!-- If not logged in, render only the slot (login page) without any wrapper -->
        <template v-if="!isLoggedIn">
            <slot />
        </template>

        <!-- If logged in, show full layout with sidebar -->
        <template v-else>
            <Sidebar />
            <div class="lg:ml-64">
                <main class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </main>
            </div>
        </template>
    </div>
</template>
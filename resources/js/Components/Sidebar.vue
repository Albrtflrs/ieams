<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useDarkMode } from '@/composables/useDarkMode';

// Use the shared dark mode composable
const { isDark, toggle } = useDarkMode();

// Mobile menu state
const isOpen = ref(false);

const menuItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Summary', route: 'summary.index', icon: 'M3 13h4v8H3zm6-8h4v16H9zm6 4h4v12h-4z' },
    { name: 'Client Files', route: 'clients.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 4 4 0 016 0z' },
    { name: 'Supplier Files', route: 'suppliers.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'Income', route: 'income.index', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Expenses', route: 'expenses.index', icon: 'M3 6h18M9 4v2m6-2v2M5 12h14M7 18h10' },
    { name: 'Receivables', route: 'receivables-payables.index', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Miscellaneous', route: 'misc.index', icon: 'M8 12h8M12 8v8M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z' },
    { name: 'Quotations', route: 'quotations.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Retainers', route: 'retainers.index', icon: 'M12 8v4l3 3M12 2a10 10 0 100 20 10 10 0 000-20z' },
    { name: 'Users', route: 'users.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { name: 'Reports', route: 'reports.index', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Settings', route: 'settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }
];

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
    <!-- Backdrop for mobile -->
    <div v-if="isOpen" class="fixed inset-0 z-20 bg-black/50 lg:hidden" @click="isOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 z-30 w-64 transform bg-white dark:bg-gray-800 shadow-lg transition-transform duration-300 ease-in-out lg:translate-x-0 transition-colors duration-300" :class="isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex h-full flex-col">
            <!-- 👇 LOGO SECTION – UPDATED -->
            <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0">
                        <img 
                            :src="isDark ? '/images/logow.png' : '/images/logob.png'" 
                            alt="Logo" 
                            class="w-full h-full object-contain"
                            @error="(e) => e.target.style.display = 'none'"
                        />
                    </div>
                    <span class="font-bold text-gray-800 dark:text-white">IEAMS</span>
                </Link>
                <button @click="isOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-2">
                    <li v-for="item in menuItems" :key="item.name">
                        <Link :href="route(item.route)" class="flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="mr-3 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            {{ item.name }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Bottom: user + dark mode + logout -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 space-y-3 transition-colors duration-300">
                <div class="flex items-center space-x-3">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ user?.name || 'Guest' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email || '' }}</p>
                    </div>
                </div>
                <button @click="toggle" class="flex w-full items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                    <svg v-if="isDark" class="mr-3 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="mr-3 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                </button>
                <form @submit.prevent="() => $inertia.post(route('logout'))">
                    <button type="submit" class="flex w-full items-center rounded-lg px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                        <svg class="mr-3 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile menu button (fixed) -->
    <button @click="isOpen = true" class="fixed bottom-4 right-4 z-40 rounded-full bg-emerald-600 p-3 text-white shadow-lg lg:hidden">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>
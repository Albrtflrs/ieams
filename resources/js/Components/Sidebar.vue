<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useDarkMode } from '@/composables/useDarkMode';

const { isDark, toggle } = useDarkMode();
const isOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const avatarUrl = computed(() => user.value?.avatar_url ?? null);

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
    { name: 'Profile', route: 'profile.edit', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'Settings', route: 'settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
];

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <!-- Mobile backdrop -->
    <div v-if="isOpen" class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden" @click="isOpen = false"></div>

    <aside
        class="relative fixed inset-y-0 left-0 z-30 w-64 transform bg-white dark:bg-gray-900 shadow-2xl transition-transform duration-300 ease-in-out lg:translate-x-0 transition-colors duration-300"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Gradient edge (right side) -->
        <div class="absolute right-0 top-0 h-full w-6 pointer-events-none bg-gradient-to-r from-transparent to-white/80 dark:to-gray-900/80"></div>

        <div class="flex h-full flex-col relative z-10">
            <!-- Logo with gradient accent -->
            <div class="flex h-16 items-center justify-between px-5 border-b border-gray-200/70 dark:border-gray-800/70 bg-gradient-to-r from-emerald-50/80 to-white dark:from-emerald-900/20 dark:to-gray-900">
                <Link :href="route('dashboard')" class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl overflow-hidden flex-shrink-0 shadow-md">
                        <img
                            :src="isDark ? '/images/logow.png' : '/images/logob.png'"
                            alt="Logo"
                            class="w-full h-full object-contain"
                            @error="(e) => e.target.style.display = 'none'"
                        />
                    </div>
                    <span class="text-lg font-bold text-gray-800 dark:text-white tracking-tight">IEAMS</span>
                </Link>
                <button
                    @click="isOpen = false"
                    class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-1">
                    <li v-for="item in menuItems" :key="item.name">
                        <Link
                            :href="route(item.route)"
                            class="flex items-center rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200 group"
                            :class="[
                                $page.component === item.route || $page.url === '/' + item.route
                                    ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shadow-sm'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-400'
                            ]"
                        >
                            <svg
                                class="mr-3 h-5 w-5 shrink-0 transition-colors duration-200"
                                :class="[
                                    $page.component === item.route || $page.url === '/' + item.route
                                        ? 'text-emerald-500 dark:text-emerald-400'
                                        : 'text-gray-400 dark:text-gray-500 group-hover:text-emerald-500'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            <span class="truncate">{{ item.name }}</span>
                            <!-- Active indicator dot -->
                            <span
                                v-if="$page.component === item.route || $page.url === '/' + item.route"
                                class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-500"
                            ></span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Bottom: user + dark mode + logout -->
            <div class="border-t border-gray-200/70 dark:border-gray-800/70 p-4 space-y-3 bg-gray-50/50 dark:bg-gray-900/50">
                <!-- User row with avatar -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-emerald-400/30 shadow-md">
                        <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar" class="w-full h-full object-cover" />
                        <span v-else class="flex items-center justify-center w-full h-full bg-gradient-to-br from-emerald-400 to-cyan-500 text-white font-semibold text-sm">
                            {{ user?.name?.charAt(0) || 'G' }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ user?.name || 'Guest' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email || '' }}</p>
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <button
                    @click="toggle"
                    class="flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors duration-200"
                >
                    <svg v-if="isDark" class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                </button>

                <!-- Logout -->
                <button
                    @click="logout"
                    class="flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-200"
                >
                    <svg class="mr-3 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </aside>

    <!-- Mobile menu button -->
    <button
        @click="isOpen = true"
        class="fixed bottom-6 right-6 z-40 rounded-full bg-emerald-500 p-3.5 text-white shadow-lg hover:bg-emerald-600 hover:shadow-xl transition-all duration-200 lg:hidden"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>
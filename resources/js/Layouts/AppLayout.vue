<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';
import ToastContainer from '@/Components/ToastContainer.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const { theme, isDark } = useDarkMode();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const breadcrumb = computed(() => {
    const component = page.component || '';
    const componentParts = component.split('/');
    const sectionMap = {
        Dashboard: 'Main',
        Clients: 'CRM',
        Suppliers: 'CRM',
        Income: 'Financial',
        Expenses: 'Financial',
        Miscellaneous: 'Financial',
        Quotations: 'Operations',
        Retainers: 'Operations',
        Reports: 'Reporting',
        ReceivablesPayables: 'Reporting',
        Summary: 'Reporting',
        Users: 'Admin',
        Profile: 'Admin',
        Settings: 'Admin',
        Admin: 'Admin',
    };
    const titleMap = {
        'ReceivablesPayables/Index': 'Receivables & Payables',
        'Reports/Aging': 'Receivables Aging',
        'Reports/PayablesAging': 'Payables Aging',
        'Summary/Index': 'Summary',
        'Admin/AuditLog': 'Audit Log',
        'Expenses/Recurring/Index': 'Recurring Expenses',
    };
    const sectionKey = componentParts[0];
    const pageKey = componentParts.at(-1);
    let title = titleMap[component];
    let action = null;

    if (!title) {
        if (pageKey === 'Create') action = 'Create';
        else if (pageKey === 'Edit') action = 'Edit';
        else if (pageKey === 'Show') action = 'Details';
        else if (pageKey === 'Trash') action = 'Trash';
        else if (pageKey === 'Form') action = page.url.includes('/edit') ? 'Edit' : 'Create';
        else if (pageKey === 'Index') title = sectionKey;
        else title = pageKey || 'Page';
    }

    const items = [
        { label: 'Home', href: route('dashboard') },
        { label: sectionMap[sectionKey] || 'Main' },
    ];

    if (action && componentParts.length > 1) {
        items.push({ label: sectionKey });
        items.push({ label: action });
    } else {
        items.push({ label: title || action });
    }

    return items;
});

const avatarUrl = computed(() => {
    if (user.value?.avatar_path) {
        return `/storage/${user.value.avatar_path}`;
    }
    return null;
});

const imageUrl = (path) => {
    return `${window.location.origin}${path}`;
};

const sidebarOpen = ref(true);
const mobileOpen = ref(false);
const isLoading = ref(false);
const loadingPath = ref('');
const isLoggingOut = ref(false);

router.on('start', (event) => {
    isLoading.value = true;
    loadingPath.value = event.detail.visit.url.pathname.toLowerCase();
});
router.on('finish', () => { isLoading.value = false; });

const skeletonKind = computed(() => {
    const path = loadingPath.value;
    if (path === '/' || path.includes('dashboard')) return 'dashboard';
    if (path.includes('reports') || path.includes('summary')) return 'analytics';
    if (path.includes('receivables-payables')) return 'split-tables';
    if (/\/(create|edit|settings|profile)(\/|$)/.test(path)) return 'form';
    if (/\/(clients|suppliers|income|expenses|misc|quotations|retainers|users)(\/|$)/.test(path)) return 'table';
    return 'compact';
});

const toggleSidebar = () => {
    if (window.innerWidth < 1024) {
        mobileOpen.value = !mobileOpen.value;
    } else {
        sidebarOpen.value = !sidebarOpen.value;
    }
};

function logout() {
    isLoggingOut.value = true;
    router.post(route('logout'), {}, {
        onFinish: () => { isLoggingOut.value = false; },
    });
}

const menuItems = {
    'Main': [
        { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    ],
    'Financial': [
        { name: 'Income', route: 'income.index', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        { name: 'Expenses', route: 'expenses.index', icon: 'M3 6h18M9 4v2m6-2v2M5 12h14M7 18h10' },
        { name: 'Miscellaneous', route: 'misc.index', icon: 'M8 12h8M12 8v8M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z' },
    ],
    'CRM': [
        { name: 'Clients', route: 'clients.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 4 4 0 016 0z' },
        { name: 'Suppliers', route: 'suppliers.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    ],
    'Operations': [
        { name: 'Retainers', route: 'retainers.index', icon: 'M12 8v4l3 3M12 2a10 10 0 100 20 10 10 0 000-20z' },
        { name: 'Quotations', route: 'quotations.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    ],
    'Reporting': [
        { name: 'Reports', route: 'reports.index', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
        { name: 'Receivables & Payables', route: 'receivables-payables.index', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        { name: 'Summary', route: 'summary.index', icon: 'M3 13h4v8H3zm6-8h4v16H9zm6 4h4v12h-4z' },
    ],
    'Admin': [
        { name: 'Users', route: 'users.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
        { name: 'Profile', route: 'profile.edit', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
        { name: 'Settings', route: 'settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
    ],
};

const pageDescriptions = {
    dashboard: 'Business performance and recent activity.',
    'income.index': 'Record sales, receipts, and client balances.',
    'expenses.index': 'Track supplier bills and operating costs.',
    'misc.index': 'Manage income and expenses outside regular transactions.',
    'clients.index': 'Maintain client contacts and review income activity.',
    'suppliers.index': 'Maintain supplier contacts and review expense activity.',
    'retainers.index': 'Track client agreements, balances, and documents.',
    'quotations.index': 'Prepare client offers and convert accepted work.',
    'reports.index': 'Analyze income, expenses, and financial trends.',
    'receivables-payables.index': 'Review outstanding client balances and supplier bills.',
    'summary.index': 'See key financial totals, margins, and monthly trends.',
    'users.index': 'Manage accounts, roles, and access permissions.',
    'profile.edit': 'Update your account details and preferences.',
    settings: 'Configure organization and application defaults.',
};

function isCurrentPage(item) {
    const targetPath = new URL(route(item.route), window.location.origin).pathname.replace(/\/$/, '') || '/';
    const currentPath = page.url.split('?')[0].replace(/\/$/, '') || '/';
    return currentPath === targetPath || currentPath.startsWith(`${targetPath}/`);
}
</script>

<template>
    <!-- ─── Main container with consistent gradient ─── -->
    <div class="min-h-screen bg-gradient-to-br from-emerald-50/90 to-green-50/90 dark:from-gray-900 dark:to-gray-800 transition-colors duration-300" :class="{ 'theme-teal-canvas': theme === 'teal', 'theme-light-canvas': theme === 'light' }">

        <!-- ─── Top Loading Bar ─── -->
        <Transition name="loading-bar">
            <div v-if="isLoading" class="fixed top-0 left-0 right-0 z-50 h-0.5 bg-emerald-500 loading-bar-progress"></div>
        </Transition>

        <!-- ─── Full-page logout splash ─── -->
        <Transition name="splash">
            <div v-if="isLoggingOut" class="splash-overlay">
                <div class="splash-card">
                    <img :src="imageUrl('/images/logow.png')" alt="IEAMS" class="splash-logo" />
                    <p class="splash-text">Signing out…</p>
                    <div class="splash-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ─── Sidebar ─── -->
        <aside
            :class="[
                'fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out flex flex-col',
                'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
                sidebarOpen ? 'w-64' : 'w-20',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo area with mint gradient accent -->
            <div class="relative flex items-center h-16 border-b border-gray-200 dark:border-gray-800 flex-shrink-0 bg-gradient-to-r from-emerald-50/80 to-white dark:from-emerald-900/20 dark:to-gray-900" :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-0'">
                <Link :href="route('dashboard')" aria-label="IEAMS home" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0">
                        <img
                            :src="isDark ? imageUrl('/images/logow.png') : imageUrl('/images/logob.png')"
                            alt="Logo"
                            class="w-full h-full object-contain"
                            @error="(e) => e.target.style.display = 'none'"
                        />
                    </div>
                    <span v-if="sidebarOpen" class="text-lg font-bold text-gray-800 dark:text-white">IEAMS</span>
                </Link>
                <button @click="toggleSidebar" :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'" :aria-label="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'" :class="['p-1 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors', sidebarOpen ? '' : 'absolute right-2']">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-4">
                <div v-for="(items, section) in menuItems" :key="section">
                    <p v-if="sidebarOpen" class="px-3 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500 tracking-wider">
                        {{ section }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="item in items" :key="item.name">
                            <Link
                                :href="route(item.route)"
                                :aria-label="`${item.name}: ${pageDescriptions[item.route] || 'Open page'}`"
                                :title="sidebarOpen ? undefined : `${item.name}: ${pageDescriptions[item.route] || 'Open page'}`"
                                :class="[
                                    'flex rounded-lg text-sm font-medium transition-all duration-200 group',
                                    sidebarOpen ? 'items-start gap-3 px-3 py-2' : 'items-center justify-center px-0 py-2',
                                    isCurrentPage(item)
                                        ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-400'
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0 transition-all duration-200 icon-shake"
                                    :class="[
                                        isCurrentPage(item)
                                            ? 'text-emerald-500 dark:text-emerald-400'
                                            : 'text-gray-500 dark:text-gray-400 group-hover:text-emerald-500'
                                    ]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span v-if="sidebarOpen" class="min-w-0">
                                    <span class="block truncate">{{ item.name }}</span>
                                    <span class="mt-0.5 hidden text-xs font-normal leading-4 text-gray-500 group-hover:block group-focus-visible:block dark:text-gray-400">{{ pageDescriptions[item.route] || 'Open page' }}</span>
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- ─── Sidebar Bottom ─── -->
            <div class="flex-shrink-0 p-3 border-t border-gray-200 dark:border-gray-800 space-y-1">
                <div v-if="sidebarOpen" class="flex items-center gap-3 px-3 py-2">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm">
                        <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar" class="w-full h-full object-cover" />
                        <span v-else>{{ user?.name?.charAt(0) || 'G' }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ user?.name || 'Guest' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ user?.email || '' }}</p>
                    </div>
                </div>
                <div v-else class="flex justify-center py-2">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm">
                        <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar" class="w-full h-full object-cover" />
                        <span v-else>{{ user?.name?.charAt(0) || 'G' }}</span>
                    </div>
                </div>

                <label v-if="sidebarOpen" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="flex-1">Theme</span>
                    <select v-model="theme" aria-label="Choose theme" class="max-w-28 rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                        <option value="teal">Teal</option>
                    </select>
                </label>
                <div v-else class="flex justify-center py-2">
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-md text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 100 18h1a2 2 0 002-2c0-.5-.2-1-.5-1.4-.3-.4-.5-.9-.5-1.4a2 2 0 012-2h2.2a2.8 2.8 0 002.8-2.8C21 6.8 17 3 12 3zM7.5 10h.01M10 7h.01M15 7h.01M17.5 10h.01" />
                        </svg>
                        <select v-model="theme" aria-label="Choose theme" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                        <option value="teal">Teal</option>
                        </select>
                    </div>
                </div>

                <button
                    @click="logout"
                    :disabled="isLoggingOut"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="isLoggingOut" class="w-5 h-5 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" />
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    <svg v-else class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span v-if="sidebarOpen">{{ isLoggingOut ? 'Signing out…' : 'Sign out' }}</span>
                </button>
            </div>
        </aside>

        <!-- ─── Overlay (mobile) ─── -->
        <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="mobileOpen = false"></div>

        <!-- ─── Main Content ─── -->
        <div :class="['transition-all duration-300', sidebarOpen ? 'lg:ml-64' : 'lg:ml-20']">
            <header class="sticky top-0 z-20 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center h-16 px-4 md:px-6 gap-4">
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 transition-colors">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </header>

            <main class="p-4 md:p-6">
                <nav class="mb-2 px-6 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
                    <template v-for="(item, index) in breadcrumb" :key="`${item.label}-${index}`">
                        <Link v-if="item.href" :href="item.href" class="hover:underline">{{ item.label }}</Link>
                        <span v-else :class="index === breadcrumb.length - 1 ? 'font-medium text-gray-700 dark:text-gray-300' : ''">{{ item.label }}</span>
                        <span v-if="index < breadcrumb.length - 1" class="mx-2" aria-hidden="true">›</span>
                    </template>
                </nav>
                <div v-if="isLoading" class="animate-pulse" role="status" aria-label="Loading page">
                    <span class="sr-only">Loading page</span>

                    <div v-if="skeletonKind === 'dashboard'" class="space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="h-7 w-52 rounded bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-9 w-44 rounded bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                            <div v-for="card in 4" :key="`dashboard-metric-${card}`" class="space-y-3 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                                <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-7 w-36 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-2 w-full rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div v-for="chart in 4" :key="`dashboard-chart-${chart}`" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                                <div class="h-4 w-40 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-56 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="skeletonKind === 'analytics'" class="space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="h-7 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-9 w-64 rounded bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div v-for="filter in 4" :key="`analytics-filter-${filter}`" class="h-9 w-36 rounded bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div v-for="chart in 2" :key="`analytics-chart-${chart}`" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                                <div class="h-4 w-44 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-64 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="skeletonKind === 'table'" class="space-y-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="h-7 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-9 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <div class="flex flex-wrap gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <div class="h-9 min-w-48 flex-1 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            <div class="h-9 w-32 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            <div class="h-9 w-28 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                        </div>
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="h-10 bg-gray-100 dark:bg-gray-700"></div>
                            <div v-for="row in 8" :key="`table-row-${row}`" class="flex gap-4 border-t border-gray-100 p-4 dark:border-gray-700">
                                <div v-for="column in 5" :key="`table-cell-${row}-${column}`" class="h-4 flex-1 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="skeletonKind === 'form'" class="mx-auto max-w-4xl space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="h-7 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-8 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                            <div v-for="field in 6" :key="`form-field-${field}`" class="space-y-2">
                                <div class="h-3 w-28 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-10 w-full rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <div class="h-9 w-24 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                                <div class="h-9 w-32 rounded bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="skeletonKind === 'split-tables'" class="space-y-5">
                        <div class="h-7 w-64 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div v-for="panel in 2" :key="`split-table-${panel}`" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="h-5 w-40 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-9 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                                <div v-for="row in 6" :key="`split-row-${panel}-${row}`" class="h-8 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="space-y-5">
                        <div class="h-7 w-48 rounded bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                            <div v-for="line in 5" :key="`compact-line-${line}`" class="h-8 rounded bg-gray-100 dark:bg-gray-700/70"></div>
                        </div>
                    </div>
                </div>
                <slot v-else />
            </main>
        </div>

        <ToastContainer />
        <ConfirmDialog />
    </div>
</template>

<style scoped>
/* ─── Splash overlay ─── */
.splash-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(ellipse at 10% 20%, #142a24, #0b1412);
}
.splash-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
    animation: splash-pop 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.splash-logo {
    width: 96px;
    height: 96px;
    object-fit: contain;
    filter: drop-shadow(0 0 32px rgba(16, 185, 129, 0.5));
}
.splash-text {
    font-size: 0.95rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.04em;
}
.splash-dots {
    display: flex;
    gap: 6px;
}
.splash-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    animation: dot-bounce 1.2s ease-in-out infinite;
}
.splash-dots span:nth-child(2) { animation-delay: 0.2s; }
.splash-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes dot-bounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40%            { transform: scale(1);   opacity: 1; }
}
@keyframes splash-pop {
    0%   { opacity: 0; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1); }
}
.splash-enter-active { transition: opacity 0.25s ease; }
.splash-leave-active { transition: opacity 0.4s ease; }
.splash-enter-from,
.splash-leave-to     { opacity: 0; }

.loading-bar-progress {
    animation: loading-bar-slide 1.2s ease-in-out infinite;
    background: linear-gradient(90deg, #10b981, #06b6d4, #10b981);
    background-size: 200% 100%;
}
@keyframes loading-bar-slide {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.loading-bar-enter-active,
.loading-bar-leave-active { transition: opacity 0.2s; }
.loading-bar-enter-from,
.loading-bar-leave-to     { opacity: 0; }

.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }

/* ─── Shake animation on group hover ─── */
.icon-shake {
    display: inline-block;
    transition: transform 0.15s ease;
}
.group:hover .icon-shake {
    animation: shake-strong 0.7s ease-in-out;
}
@keyframes shake-strong {
    0%, 100% { transform: translateX(0) rotate(0deg); }
    10% { transform: translateX(-5px) rotate(-10deg); }
    30% { transform: translateX(5px) rotate(10deg); }
    50% { transform: translateX(-4px) rotate(-8deg); }
    70% { transform: translateX(4px) rotate(8deg); }
    90% { transform: translateX(-2px) rotate(-4deg); }
}
</style>
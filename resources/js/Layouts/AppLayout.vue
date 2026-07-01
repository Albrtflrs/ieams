<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';
import ToastContainer from '@/Components/ToastContainer.vue';

const { isDark, toggle } = useDarkMode();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

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
const isLoggingOut = ref(false);

router.on('start', () => { isLoading.value = true; });
router.on('finish', () => { isLoading.value = false; });

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
</script>

<template>
    <!-- ─── Main container with consistent gradient ─── -->
    <div class="min-h-screen bg-gradient-to-br from-emerald-50/90 to-green-50/90 dark:from-gray-900 dark:to-gray-800 transition-colors duration-300">

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
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-800 flex-shrink-0 bg-gradient-to-r from-emerald-50/80 to-white dark:from-emerald-900/20 dark:to-gray-900">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
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
                <button @click="toggleSidebar" class="p-1 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
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
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 group"
                                :class="[
                                    $page.component.startsWith(item.route) || $page.url === '/' + item.route
                                        ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-400'
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0 transition-all duration-200 icon-shake"
                                    :class="[
                                        $page.component.startsWith(item.route) || $page.url === '/' + item.route
                                            ? 'text-emerald-500 dark:text-emerald-400'
                                            : 'text-gray-500 dark:text-gray-400 group-hover:text-emerald-500'
                                    ]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span v-if="sidebarOpen" class="truncate">{{ item.name }}</span>
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

                <button
                    @click="toggle"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 dark:text-gray-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 hover:text-emerald-700 dark:hover:text-emerald-400"
                >
                    <svg v-if="isDark" class="w-5 h-5 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="w-5 h-5 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span v-if="sidebarOpen">{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
                </button>

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
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white capitalize">
                        {{ $page.component }}
                    </h1>
                </div>
            </header>

            <Transition name="fade">
                <div v-if="isLoading" class="fixed inset-0 z-10 bg-white/30 dark:bg-gray-950/30 backdrop-blur-[2px] pointer-events-none"></div>
            </Transition>

            <main class="p-4 md:p-6">
                <slot />
            </main>
        </div>

        <ToastContainer />
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
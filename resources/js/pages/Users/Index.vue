<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps({
    users: Object,
});

const { formatDate } = useDateFormat();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const totalUsers = computed(() => props.users.total ?? 0);

const avatarUrl = (path) => {
    if (!path) return '';
    const filename = path.split('/').pop();
    return `/profile/avatar/${encodeURIComponent(filename)}`;
};

// ─── Role display formatting ─────────────
const formatRole = (role) => {
    const map = {
        super_admin: 'Super Admin',
        admin: 'Admin',
        manager: 'Manager',
        staff: 'Staff',
        viewer: 'Viewer',
    };
    return map[role] || role;
};

// ─── Permissions ──────────────────────────
const canEdit = (user) => {
    if (currentUser.value?.role === 'super_admin') return true;
    if (currentUser.value?.role === 'admin' && user.role !== 'super_admin') return true;
    return false;
};

const canDelete = (user) => {
    if (currentUser.value?.role === 'super_admin' && currentUser.value?.id !== user.id) return true;
    return false;
};

const deleteUser = (id) => {
    if (confirm('Delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', id));
    }
};

// ─── Role badge colors (raw role) ────────
const roleBadgeClass = (role) => {
    const colors = {
        super_admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        admin: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        manager: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        staff: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        viewer: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return colors[role] || colors.viewer;
};
</script>

<template>
    <AppLayout>
        <main class="p-6">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ totalUsers }} {{ totalUsers === 1 ? 'account' : 'accounts' }}</span>
                </div>
                <Link
                    v-if="currentUser?.role === 'super_admin' || currentUser?.role === 'admin'"
                    :href="route('users.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <span class="text-lg leading-none" aria-hidden="true">+</span>
                    Add User
                </Link>
            </header>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Users">
                <article
                    v-for="user in users.data"
                    :key="user.id"
                    class="flex min-h-52 flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-gray-100 text-base font-bold text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ user.name?.charAt(0)?.toUpperCase() || '?' }}
                                <img
                                    v-if="user.avatar_path"
                                    :src="avatarUrl(user.avatar_path)"
                                    :alt="`${user.name}'s profile photo`"
                                    class="absolute h-12 w-12 rounded-full object-cover"
                                    @error="$event.target.style.display = 'none'"
                                />
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate font-semibold text-gray-900 dark:text-white">{{ user.name }}</h2>
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="roleBadgeClass(user.role)">
                            {{ formatRole(user.role) }}
                        </span>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 text-sm dark:border-gray-700">
                        <div>
                            <dt class="text-xs uppercase text-gray-400">User ID</dt>
                            <dd class="mt-1 font-medium text-gray-700 dark:text-gray-200">#{{ user.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Joined</dt>
                            <dd class="mt-1 font-medium text-gray-700 dark:text-gray-200">{{ formatDate(user.created_at) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-auto flex items-center justify-end gap-3 pt-4">
                        <Link
                            v-if="canEdit(user)"
                            :href="route('users.edit', user.id)"
                            class="rounded px-2 py-1 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete(user)"
                            type="button"
                            @click="deleteUser(user.id)"
                            class="rounded px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                            Delete
                        </button>
                        <span v-if="!canEdit(user) && !canDelete(user)" class="text-sm text-gray-400">No actions</span>
                    </div>
                </article>

                <div v-if="!users.data || users.data.length === 0" class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">No users yet</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">New accounts will appear here.</p>
                </div>
            </section>

            <Pagination :links="users.links" />
        </main>
    </AppLayout>
</template>
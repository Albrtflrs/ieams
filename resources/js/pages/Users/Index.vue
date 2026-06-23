<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps({
    users: Object, // paginated collection with 'data', 'links', etc.
});

const { formatDate } = useDateFormat();
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Calculate summary stats
const totalUsers = computed(() => props.users.total ?? 0);
const roleCounts = computed(() => {
    const counts = {};
    if (props.users.data) {
        props.users.data.forEach(u => {
            counts[u.role] = (counts[u.role] || 0) + 1;
        });
    }
    return counts;
});

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
        <div class="p-6">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Users</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Users</h1>
                <Link
                    v-if="currentUser?.role === 'super_admin' || currentUser?.role === 'admin'"
                    :href="route('users.create')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition"
                >
                    + Add User
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-lg p-4 shadow"
                     title="Total number of users in the system.">
                    <p class="text-sm uppercase tracking-wider opacity-80">Total Users</p>
                    <p class="text-2xl font-bold">{{ totalUsers }}</p>
                </div>
                <div v-for="(count, role) in roleCounts" :key="role" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ role }}</p>
                    <p class="text-2xl font-bold">{{ count }}</p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2">{{ user.id }}</td>
                            <td class="px-4 py-2 font-medium">{{ user.name }}</td>
                            <td class="px-4 py-2">{{ user.email }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    :class="roleBadgeClass(user.role)">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ formatDate(user.created_at) }}</td>
                            <td class="px-4 py-2">
                                <!-- Edit button – only if allowed -->
                                <Link
                                    v-if="canEdit(user)"
                                    :href="route('users.edit', user.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline mr-2"
                                >
                                    Edit
                                </Link>
                                <!-- Delete button – only if allowed -->
                                <button
                                    v-if="canDelete(user)"
                                    @click="deleteUser(user.id)"
                                    class="text-red-600 dark:text-red-400 hover:underline"
                                >
                                    Delete
                                </button>
                                <span v-if="!canEdit(user) && !canDelete(user)" class="text-gray-400 text-sm">—</span>
                            </td>
                        </tr>
                        <tr v-if="!users.data || users.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links" class="mt-4 flex justify-center space-x-2">
                <template v-for="link in users.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                        class="px-3 py-1 rounded-md"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 rounded-md text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
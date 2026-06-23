<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'viewer',
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="New User" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('users.index')" class="hover:underline">Users</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Create</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create New User</h1>
                <Link :href="route('users.index')" class="text-gray-600 dark:text-gray-400 hover:underline">← Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Full name of the user.">
                        Name *
                    </label>
                    <input v-model="form.name" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Email address (used for login).">
                        Email *
                    </label>
                    <input v-model="form.email" type="email" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Password (minimum 8 characters).">
                        Password *
                    </label>
                    <input v-model="form.password" type="password" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                    <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Confirm the password.">
                        Confirm Password *
                    </label>
                    <input v-model="form.password_confirmation" type="password" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required />
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="User role determines permissions.">
                        Role *
                    </label>
                    <select v-model="form.role" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                        <option value="viewer">Viewer</option>
                    </select>
                    <div v-if="form.errors.role" class="text-red-500 text-sm">{{ form.errors.role }}</div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                        Save
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
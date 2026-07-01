<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false); // controls both fields

const submit = () => {
    form.put(route('users.update', props.user.id));
};

function generatePassword(length = 12) {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
    let password = '';
    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    return password;
}

function setRandomPassword() {
    const newPassword = generatePassword(12);
    form.password = newPassword;
    form.password_confirmation = newPassword;
}
</script>

<template>
    <AppLayout>
        <Head title="Edit User" />

        <div class="p-6 max-w-2xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <Link :href="route('users.index')" class="hover:underline">Users</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Edit</span>
            </div>

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit User</h1>
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

                <!-- Password field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Leave blank to keep current password.">
                        Password (leave blank to keep current)
                    </label>
                    <div class="relative">
                        <button
                            type="button"
                            @click="setRandomPassword"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            title="Generate random password"
                        >
                            🎲
                        </button>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full border rounded-lg px-3 py-2 pl-10 pr-10 dark:bg-gray-700 dark:border-gray-600"
                            :placeholder="showPassword ? 'New Password (visible)' : 'New Password'"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            :title="showPassword ? 'Hide passwords' : 'Show passwords'"
                        >
                            <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" title="Confirm the new password (if changing).">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <button
                            type="button"
                            @click="setRandomPassword"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            title="Generate random password"
                        >
                            🎲
                        </button>
                        <input
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full border rounded-lg px-3 py-2 pl-10 pr-10 dark:bg-gray-700 dark:border-gray-600"
                            :placeholder="showPassword ? 'Confirm Password (visible)' : 'Confirm Password'"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            :title="showPassword ? 'Hide passwords' : 'Show passwords'"
                        >
                            <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
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
                        Update
                    </button>
                    <button type="button" @click="form.reset()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
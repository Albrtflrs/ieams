<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

// ✅ Helper to generate the correct route URL and bypass the 403 storage symlink issue
const getAvatarUrl = (path) => {
    if (!path) return null;
    // Extract just the filename (e.g., 'Riuv00...jpg') from 'profile_photos/Riuv00...jpg'
    const filename = path.split('/').pop();
    return `/profile/avatar/${filename}`;
};

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    avatar: null,
});

const avatarPreview = ref(getAvatarUrl(props.user?.avatar_path));

const submit = () => {
    console.log('Submitting form with avatar:', form.avatar);

    form.post(route('profile.update'), {
        forceFormData: true,
        onSuccess: () => {
            window.location.reload(true);
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        },
    });
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
        console.log('File selected:', file);
    }
};

const resetForm = () => {
    form.name = props.user?.name || '';
    form.email = props.user?.email || '';
    form.avatar = null;
    // ✅ Use the helper here as well
    avatarPreview.value = getAvatarUrl(props.user?.avatar_path);
    const fileInput = document.getElementById('avatar-input');
    if (fileInput) fileInput.value = '';
    form.errors = {};
};
</script>

<template>
    <AppLayout>
        <Head title="Profile" />

        <div class="p-6">
            <!-- Breadcrumb (matches Reports page) -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Dashboard</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Profile</span>
            </div>

            <h1 class="text-2xl font-bold mb-6">My Profile</h1>

            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                <p class="text-red-600 dark:text-red-400 font-medium">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-500 dark:text-red-300 mt-1">
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ field }}: {{ error }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4 max-w-2xl">
                <!-- Avatar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                    <div class="flex items-center gap-6 mt-1">
                        <div class="w-24 h-24 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="w-full h-full object-cover" />
                            <span v-else class="text-4xl text-gray-400">👤</span>
                        </div>
                        <div>
                            <input
                                id="avatar-input"
                                type="file"
                                @change="onFileChange"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="text-xs text-gray-400 mt-1">Max 2MB – JPG, PNG, GIF</p>
                        </div>
                    </div>
                    <div v-if="form.errors.avatar" class="text-red-500 text-sm mt-1">{{ form.errors.avatar }}</div>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                    <input v-model="form.name" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input v-model="form.email" type="email" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                        Save Profile
                    </button>
                    <button type="button" @click="resetForm" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
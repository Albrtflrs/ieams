<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'));
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
                <p class="text-slate-500 text-sm mt-1">Sign in to your account</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- ✅ Added target="_top" and method="POST" -->
                <form @submit.prevent="submit" target="_top" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input v-model="form.email" type="email" autofocus
                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               required />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input v-model="form.password" type="password"
                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               required />
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                            <span class="ml-2 text-sm text-slate-600">Remember me</span>
                        </label>
                        <Link :href="route('register')" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            Create account
                        </Link>
                    </div>
                    <button type="submit" :disabled="form.processing"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md">
                        Sign In
                    </button>
                </form>
            </div>

            <div class="text-center mt-6 text-xs text-slate-400">
                IEAMS v1.0.0 | Authorized Access Only
            </div>
        </div>
    </div>
</template>
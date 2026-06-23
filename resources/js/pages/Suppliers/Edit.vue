<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['supplier']);
const form = useForm({
    name: props.supplier.name,
    contact_person: props.supplier.contact_person || '',
    phone: props.supplier.phone || '',
    email: props.supplier.email || '',
    address: props.supplier.address || '',
});

function submit() {
    form.put(route('suppliers.update', props.supplier.id));
}
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-xl">
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-bold">Edit Supplier</h1>
                <Link :href="route('suppliers.index')" class="text-gray-600">← Back</Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block font-medium">Name *</label>
                    <input v-model="form.name" class="w-full border rounded p-2" required />
                    <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label class="block font-medium">Contact Person</label>
                    <input v-model="form.contact_person" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label class="block font-medium">Phone</label>
                    <input v-model="form.phone" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label class="block font-medium">Email</label>
                    <input v-model="form.email" type="email" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label class="block font-medium">Address</label>
                    <textarea v-model="form.address" class="w-full border rounded p-2" rows="3"></textarea>
                </div>
                <button type="submit" :disabled="form.processing" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
            </form>
        </div>
    </AppLayout>
</template>
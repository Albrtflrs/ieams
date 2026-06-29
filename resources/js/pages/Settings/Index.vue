<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    company_name: props.settings.company_name || '',
    company_address: props.settings.company_address || '',
    tax_id: props.settings.tax_id || '',
    currency_symbol: props.settings.currency_symbol || '₱',
    fiscal_year_start: props.settings.fiscal_year_start || 'January',
    default_royalty_rate: props.settings.default_royalty_rate || 0,
    default_payment_terms: props.settings.default_payment_terms || 'Due on receipt',
    enable_registration: props.settings.enable_registration || false,
    date_format: props.settings.date_format || 'Y-m-d',
    rows_per_page: props.settings.rows_per_page || 20,
    // Categories removed – they are not in the controller
    invoice_prefix: props.settings.invoice_prefix || 'INV-',
    invoice_next_number: props.settings.invoice_next_number || 1,
    backup_path: props.settings.backup_path || '',
    backup_monthly: !!props.settings.backup_monthly,
    allowed_ips: props.settings.allowed_ips || '127.0.0.1',
});

const logoPreview = ref(props.settings.logo_path ? '/storage/' + props.settings.logo_path : null);
const hasFile = ref(false);

const submit = () => {
    form.put(route('settings.update'), {
        preserveScroll: true,
        preserveState: true,
    });
};

const onFileChange = (event) => {
    hasFile.value = event.target.files.length > 0;
};

const uploadLogo = () => {
    const fileInput = document.getElementById('logoInput');
    if (fileInput && fileInput.files.length > 0) {
        const formData = new FormData();
        formData.append('logo', fileInput.files[0]);
        router.post(route('settings.logo.upload'), formData, {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true,
        });
    }
};

const removeLogo = () => {
    if (confirm('Remove logo?')) {
        router.delete(route('settings.logo.remove'), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const clearFileInput = () => {
    document.getElementById('logoInput').value = '';
    hasFile.value = false;
};

const triggerBackup = () => {
    if (confirm('Download database backup now?')) {
        window.location.href = route('settings.backup.download');
    }
};

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const dateFormats = [
    { value: 'Y-m-d', label: 'YYYY-MM-DD' },
    { value: 'd/m/Y', label: 'DD/MM/YYYY' },
    { value: 'm/d/Y', label: 'MM/DD/YYYY' },
];
const pageSizes = [5, 10, 20, 50, 100];
const paymentTerms = ['Due on receipt', 'Net 15', 'Net 30', 'Net 60'];
</script>

<template>
    <AppLayout>
        <Head title="Settings" />

        <div class="p-6 max-w-5xl mx-auto">

            <!-- Breadcrumb -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                <Link :href="route('dashboard')" class="hover:underline">Home</Link>
                <span class="mx-2">›</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Settings</span>
            </div>

            <!-- Header -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Settings</h1>
                <span class="text-xs text-gray-500">Configure your application preferences</span>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-6">

                <!-- ─── General ────────────────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">General</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Your company's legal or trading name.">
                                Company Name
                            </label>
                            <input v-model="form.company_name" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Your company's tax identification number.">
                                Tax ID
                            </label>
                            <input v-model="form.tax_id" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Physical address of your company.">
                                Company Address
                            </label>
                            <textarea v-model="form.company_address" rows="2" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ─── Branding ────────────────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">Branding</h2>
                    <div class="flex items-start gap-4 flex-wrap">
                        <div v-if="logoPreview" class="w-24 h-24 border rounded-lg overflow-hidden flex-shrink-0 bg-white dark:bg-gray-700">
                            <img :src="logoPreview" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <input 
                                    type="file" 
                                    id="logoInput" 
                                    accept="image/*" 
                                    @change="onFileChange" 
                                    class="block w-full max-w-xs text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50" 
                                />
                                <button 
                                    type="button" 
                                    @click="uploadLogo" 
                                    :disabled="!hasFile" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    Upload Logo
                                </button>
                                <button 
                                    type="button" 
                                    @click="clearFileInput" 
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                >
                                    Clear file
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Max 2MB, PNG/JPG. Select a file then click Upload.</p>
                            <button 
                                v-if="props.settings.logo_path" 
                                type="button" 
                                @click="removeLogo" 
                                class="text-red-600 dark:text-red-400 text-sm hover:underline"
                            >
                                Remove Logo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ─── Financial ──────────────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">Financial</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Currency symbol used throughout the application (e.g., ₱, $).">
                                Currency Symbol
                            </label>
                            <input v-model="form.currency_symbol" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="₱" />
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Month when your fiscal year begins.">
                                Fiscal Year Start Month
                            </label>
                            <select v-model="form.fiscal_year_start" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                                <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Default royalty rate (%) applied to income transactions.">
                                Default Royalty Rate (%)
                            </label>
                            <input v-model.number="form.default_royalty_rate" type="number" step="0.1" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" />
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Default payment terms for invoices.">
                                Default Payment Terms
                            </label>
                            <select v-model="form.default_payment_terms" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                                <option v-for="t in paymentTerms" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ─── Default Categories – REMOVED ──────────────────── -->

                <!-- ─── Invoice Numbering ──────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">Invoice Numbering</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Prefix used for invoice numbers (e.g., INV-, INVOICE-).">
                                Invoice Prefix
                            </label>
                            <input v-model="form.invoice_prefix" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="INV-" />
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Next invoice number to be assigned (auto-increments).">
                                Next Invoice Number
                            </label>
                            <input v-model.number="form.invoice_next_number" type="number" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" min="1" />
                        </div>
                    </div>
                </div>

                <!-- ─── Backup ──────────────────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">Backup</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Directory path where SQL backup files will be saved.">
                                Backup Path
                            </label>
                            <input v-model="form.backup_path" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="C:/Users/User/Desktop/ieams_backups" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Directory where SQL dumps will be saved</p>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <input type="checkbox" v-model="form.backup_monthly" id="backup_monthly" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="backup_monthly" class="text-sm text-gray-700 dark:text-gray-300" title="Enable automatic monthly database backups.">
                                Enable monthly automatic backup
                            </label>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" @click="triggerBackup" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm shadow transition">
                            Run Backup Now
                        </button>
                    </div>
                </div>

                <!-- ─── IP Whitelist ────────────────────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">IP Whitelist</h2>
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Comma-separated list of IPs allowed to access the system. Use * for wildcard.">
                            Allowed IPs
                        </label>
                        <input v-model="form.allowed_ips" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600" placeholder="127.0.0.1,192.168.1.*" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Comma separated. Use * for wildcard (e.g., 192.168.*.*)</p>
                    </div>
                </div>

                <!-- ─── Appearance & Preferences ───────────────────────── -->
                <div>
                    <h2 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-4 text-gray-800 dark:text-white">Appearance &amp; Preferences</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Default date format used throughout the application.">
                                Date Format
                            </label>
                            <select v-model="form.date_format" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                                <option v-for="f in dateFormats" :key="f.value" :value="f.value">{{ f.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" title="Number of rows displayed per page in data tables.">
                                Rows per Page
                            </label>
                            <select v-model.number="form.rows_per_page" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                                <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" v-model="form.enable_registration" id="enable_reg" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="enable_reg" class="text-sm text-gray-700 dark:text-gray-300" title="Allow new users to register via the registration page.">
                                Enable public user registration
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ─── Actions ─────────────────────────────────────────── -->
                <div class="flex items-center gap-4 pt-4 border-t dark:border-gray-700">
                    <button type="submit" :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg shadow disabled:opacity-50 transition">
                        Save Settings
                    </button>
                    <span v-if="form.recentlySuccessful" class="text-green-600 dark:text-green-400 text-sm font-medium">✓ Settings saved!</span>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
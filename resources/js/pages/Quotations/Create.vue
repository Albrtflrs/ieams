<script setup>
import QuotationForm from './Form.vue';

defineProps({
    clients: Array,
    items: Array,
    quotation_number: String,
    default_currency: String,
    user_role: String,
    categories: Array,
});
</script>

<template>
    <QuotationForm
        :clients="clients"
        :items="items"
        :quotation_number="quotation_number"
        :default_currency="default_currency"
        :user_role="user_role"
        :categories="categories"
    />
</template>
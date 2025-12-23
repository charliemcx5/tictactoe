<script setup lang="ts">
import Auth from './Auth.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Auth
        :status="status"
        :can-reset-password="canResetPassword"
        :can-register="canRegister"
    />
</template>

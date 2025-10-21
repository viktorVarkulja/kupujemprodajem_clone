<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});
import ToastBar from "@/components/ToastBar.vue";
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <div v-if="flash?.success" class="bg-emerald-50 text-emerald-900 border-b border-emerald-200">
            <div class="mx-auto md:max-w-7xl px-4 py-2 text-sm">{{ flash.success }}</div>
        </div>
        <div v-else-if="flash?.error" class="bg-red-50 text-red-900 border-b border-red-200">
            <div class="mx-auto md:max-w-7xl px-4 py-2 text-sm">{{ flash.error }}</div>
        </div>
        <AppContent>
            <slot />
        </AppContent>
        <ToastBar />
    </AppShell>
</template>


<script setup lang="ts">
import '../../css/app.css';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import FlashMessage from '@/components/admin/FlashMessage.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div dir="rtl">
        <AppShell variant="sidebar">
            <AdminSidebar />
            <AppContent variant="sidebar" class="bg-muted/30">
                <header
                    class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
                >
                    <div class="flex items-center gap-2">
                        <SidebarTrigger class="-mr-1" />
                        <Breadcrumb v-if="breadcrumbs.length > 0">
                            <BreadcrumbList>
                                <template v-for="(item, index) in breadcrumbs" :key="index">
                                    <BreadcrumbItem>
                                        <BreadcrumbPage v-if="index === breadcrumbs.length - 1">{{ item.title }}</BreadcrumbPage>
                                        <BreadcrumbLink v-else :href="item.href">{{ item.title }}</BreadcrumbLink>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
                                </template>
                            </BreadcrumbList>
                        </Breadcrumb>
                    </div>
                    <div class="ms-auto flex items-center">
                        <NotificationBell />
                    </div>
                </header>
                <FlashMessage />
                <slot />
            </AppContent>
        </AppShell>
    </div>
</template>

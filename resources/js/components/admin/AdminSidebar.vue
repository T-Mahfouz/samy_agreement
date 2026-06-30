<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Building2,
    FileText,
    FolderTree,
    LayoutGrid,
    type LucideIcon,
    MapPin,
    MessageSquare,
    Receipt,
    ScrollText,
    Settings,
    Users,
    HelpCircle,
    FileSignature,
    Newspaper,
} from 'lucide-vue-next';

interface AdminNavItem {
    title: string;
    href: string;
    icon: LucideIcon;
}

const groups: { label: string; items: AdminNavItem[] }[] = [
    {
        label: 'الرئيسية',
        items: [{ title: 'لوحة التحكم', href: '/admin/dashboard', icon: LayoutGrid }],
    },
    {
        label: 'المنافسات',
        items: [
            { title: 'المنافسات', href: '/admin/tenders', icon: FileText },
            { title: 'العروض', href: '/admin/offers', icon: ScrollText },
            { title: 'العقود', href: '/admin/contracts', icon: FileSignature },
            { title: 'المدفوعات', href: '/admin/payments', icon: Receipt },
            { title: 'الاستفسارات', href: '/admin/inquiries', icon: MessageSquare },
        ],
    },
    {
        label: 'المستخدمون',
        items: [
            { title: 'الموردون', href: '/admin/providers', icon: Building2 },
            { title: 'العملاء', href: '/admin/clients', icon: Users },
        ],
    },
    {
        label: 'الإعدادات والمحتوى',
        items: [
            { title: 'التصنيفات', href: '/admin/categories', icon: FolderTree },
            { title: 'المناطق والمدن', href: '/admin/locations', icon: MapPin },
            { title: 'الأسئلة الشائعة', href: '/admin/faqs', icon: HelpCircle },
            { title: 'محتوى الصفحات', href: '/admin/content', icon: Newspaper },
            { title: 'رسائل التواصل', href: '/admin/messages', icon: MessageSquare },
            { title: 'الإعدادات', href: '/admin/settings', icon: Settings },
        ],
    },
];

const page = usePage<SharedData>();

const isActive = (href: string) => page.url === href || page.url.startsWith(href + '/');
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" side="right">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin/dashboard" class="flex items-center gap-2">
                            <div class="flex aspect-square size-9 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-emerald-600 text-white shadow-sm shadow-primary/30">
                                <LayoutGrid class="size-5" />
                            </div>
                            <div class="grid flex-1 text-right text-sm leading-tight">
                                <span class="truncate font-bold">منصة اتفاق</span>
                                <span class="truncate text-xs text-muted-foreground">لوحة التحكم</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup v-for="group in groups" :key="group.label" class="px-2 py-0">
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in group.items" :key="item.href">
                        <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.title">
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

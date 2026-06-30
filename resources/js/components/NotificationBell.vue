<script setup lang="ts">
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { Bell } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const page = usePage<SharedData>();
const data = computed(() => page.props.notifications ?? { unread: 0, items: [] });

const open = ref(false);
const root = ref<HTMLElement | null>(null);
onClickOutside(root, () => (open.value = false));

// Shared visual styling for the dropdown panel.
const panelBase =
    'max-height:420px; overflow:auto; background:#fff; border:1px solid #e2e8e4; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,.12); z-index:100000;';

// On narrow screens the bell sits near the viewport edge, so a 320px panel
// anchored to it would clip. Detach it and span the viewport instead.
const panelStyle = ref(panelBase);
const computePanelStyle = () => {
    if (typeof window === 'undefined') return;
    if (window.innerWidth < 576) {
        const top = (root.value?.getBoundingClientRect().bottom ?? 56) + 8;
        panelStyle.value = `position:fixed; inset-inline:12px; top:${top}px; width:auto; max-width:none; ${panelBase}`;
    } else {
        panelStyle.value = `position:absolute; inset-inline-end:0; top:calc(100% + 8px); width:min(320px, calc(100vw - 24px)); max-width:calc(100vw - 24px); ${panelBase}`;
    }
};
const toggle = () => {
    open.value = !open.value;
    if (open.value) computePanelStyle();
};
const onResize = () => {
    if (open.value) computePanelStyle();
};
onMounted(() => window.addEventListener('resize', onResize));
onBeforeUnmount(() => window.removeEventListener('resize', onResize));

const openItem = (n: { id: number; link: string | null }) => {
    open.value = false;
    router.put(`/notifications/${n.id}/read`, {}, { preserveScroll: !n.link });
};
const readAll = () => router.put('/notifications/read-all', {}, { preserveScroll: true });
const fmt = (s: string) => new Date(s).toLocaleString('ar-EG', { dateStyle: 'short', timeStyle: 'short' });
</script>

<template>
    <div ref="root" style="position:relative;">
        <button type="button" @click="toggle"
            style="position:relative; background:transparent; border:0; cursor:pointer; padding:6px; display:inline-flex; align-items:center;">
            <Bell :size="22" :stroke-width="2" />
            <span v-if="data.unread > 0"
                style="position:absolute; top:0; inset-inline-start:0; min-width:18px; height:18px; padding:0 4px; background:#df0000; color:#fff; border-radius:9px; font-size:11px; line-height:18px; text-align:center; font-weight:700;">
                {{ data.unread > 9 ? '9+' : data.unread }}
            </span>
        </button>

        <div v-if="open" :style="panelStyle">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border-bottom:1px solid #eef2ef;">
                <span style="font-weight:700; color:#1c9c55;">الإشعارات</span>
                <button v-if="data.unread > 0" type="button" @click="readAll"
                    style="background:transparent; border:0; color:#0f6eb1; font-size:12px; cursor:pointer;">تعليم الكل كمقروء</button>
            </div>
            <ul style="list-style:none; margin:0; padding:0;">
                <li v-for="n in data.items" :key="n.id" @click="openItem(n)"
                    :style="{ padding:'12px 14px', borderBottom:'1px solid #f1f5f2', cursor:'pointer', background: n.is_read ? '#fff' : '#f0faf4' }">
                    <div style="font-weight:600; font-size:14px; color:#1a1a1a;">{{ n.title }}</div>
                    <div v-if="n.body" style="font-size:12px; color:#666; margin-top:2px;">{{ n.body }}</div>
                    <div style="font-size:11px; color:#999; margin-top:4px;">{{ fmt(n.created_at) }}</div>
                </li>
                <li v-if="data.items.length === 0" style="padding:24px 14px; text-align:center; color:#999; font-size:13px;">لا توجد إشعارات</li>
            </ul>
        </div>
    </div>
</template>

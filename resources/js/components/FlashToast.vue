<script setup lang="ts">
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

// إشعار عائم (toast) يوضّح نتيجة أي نموذج: نجاح أخضر / خطأ أحمر.
// يعمل في صفحات Slice (بدون Tailwind) وصفحات الأدمن معًا.
const page = usePage<SharedData>();

interface Toast {
    id: number;
    type: 'success' | 'error';
    text: string;
}

const toasts = ref<Toast[]>([]);
let seq = 0;
const timers = new Map<number, ReturnType<typeof setTimeout>>();

const dismiss = (id: number) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
    const tm = timers.get(id);
    if (tm) {
        clearTimeout(tm);
        timers.delete(id);
    }
};

const push = (type: 'success' | 'error', text?: string | null) => {
    if (!text) return;
    const id = ++seq;
    toasts.value.push({ id, type, text });
    timers.set(id, setTimeout(() => dismiss(id), 6000));
};

// رسائل الفلاش من الخادم (back()->with('success'|'error', ...))
watch(
    () => [page.props.flash?.success, page.props.flash?.error] as const,
    ([s, e]) => {
        push('success', s);
        push('error', e);
    },
    { immediate: true },
);

// أخطاء التحقق (422) — رسالة عامة واضحة، والتفاصيل تظهر بجانب كل حقل
let stopError: (() => void) | null = null;
onMounted(() => {
    stopError = router.on('error', (event: { detail?: { errors?: Record<string, unknown> } }) => {
        const errs = event?.detail?.errors;
        if (errs && Object.keys(errs).length) {
            push('error', 'تعذّر الإرسال — يرجى مراجعة الحقول الموضّحة بالأحمر في النموذج.');
        }
    });
});
onBeforeUnmount(() => {
    stopError?.();
    timers.forEach((t) => clearTimeout(t));
    timers.clear();
});
</script>

<template>
    <div class="ag-toasts" aria-live="polite" aria-atomic="true">
        <transition-group name="ag-toast">
            <div v-for="t in toasts" :key="t.id" class="ag-toast" :class="`ag-toast--${t.type}`" role="alert">
                <span class="ag-toast__icon" aria-hidden="true">{{ t.type === 'success' ? '✓' : '!' }}</span>
                <span class="ag-toast__text">{{ t.text }}</span>
                <button type="button" class="ag-toast__close" aria-label="إغلاق" @click="dismiss(t.id)">×</button>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.ag-toasts {
    position: fixed;
    top: 20px;
    inset-inline: 0;
    z-index: 99999999;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 0 16px;
    pointer-events: none;
}
.ag-toast {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 12px;
    width: min(460px, 100%);
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.6;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16);
}
.ag-toast--success {
    background: #e9f8ef;
    border-color: #bfe9cf;
    color: #1c7a43;
}
.ag-toast--error {
    background: #fdecec;
    border-color: #f5c2c2;
    color: #c0331f;
}
.ag-toast__icon {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #fff;
}
.ag-toast--success .ag-toast__icon {
    background: #1c9c55;
}
.ag-toast--error .ag-toast__icon {
    background: #df0000;
}
.ag-toast__text {
    flex: 1;
}
.ag-toast__close {
    flex-shrink: 0;
    padding: 0 2px;
    background: transparent;
    border: 0;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    color: inherit;
    opacity: 0.6;
}
.ag-toast__close:hover {
    opacity: 1;
}
.ag-toast-enter-active,
.ag-toast-leave-active {
    transition: all 0.3s ease;
}
.ag-toast-enter-from,
.ag-toast-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
</style>

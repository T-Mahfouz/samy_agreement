<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{ deadline: string | null; time?: string | null }>();
const text = ref('—');
let timer: ReturnType<typeof setInterval> | undefined;

const target = () => {
    if (!props.deadline) return null;
    // ضم الوقت إن وُجد، وإلا نهاية اليوم
    const t = props.time ? props.time.slice(0, 5) : '23:59';
    return new Date(`${props.deadline}T${t}:00`).getTime();
};

const compute = () => {
    const end = target();
    if (!end) { text.value = '—'; return; }
    const diff = end - Date.now();
    if (diff <= 0) { text.value = 'انتهى'; return; }
    const d = Math.floor(diff / 86400000);
    const h = Math.floor((diff % 86400000) / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    text.value = `${d} يوم ${h} ساعة ${m} دقيقة`;
};

onMounted(() => { compute(); timer = setInterval(compute, 60000); });
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <span>{{ text }}</span>
</template>

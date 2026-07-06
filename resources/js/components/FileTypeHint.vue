<script setup lang="ts">
defineProps<{ exts: string; maxMb: number }>();
</script>

<template>
    <small class="d-block" style="margin-top: 8px; color: #8a8f99; font-size: 12px; line-height: 1.6">
        الصيغ المسموحة: {{ exts }} — أقصى حجم للملف {{ maxMb }} ميجابايت
    </small>
</template>

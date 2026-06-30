<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{ links: { url: string | null; label: string; active: boolean }[]; total?: number }>();
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-between gap-3 border-t p-4">
        <span class="text-xs text-muted-foreground">{{ (total ?? 0).toLocaleString('ar-EG') }} عنصر</span>
        <div class="flex flex-wrap justify-center gap-1">
            <template v-for="(l, i) in links" :key="i">
                <Link
                    v-if="l.url"
                    :href="l.url"
                    preserve-scroll
                    preserve-state
                    class="min-w-9 rounded-lg border px-3 py-1.5 text-center text-sm transition-colors"
                    :class="l.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                    v-html="l.label"
                />
                <span v-else class="min-w-9 rounded-lg border px-3 py-1.5 text-center text-sm text-muted-foreground opacity-50" v-html="l.label" />
            </template>
        </div>
    </div>
</template>

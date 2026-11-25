<script setup>
defineProps({
  tone: { type: String, default: 'neutral' },
  size: { type: String, default: 'md' },
  dot: { type: Boolean, default: false },
})
</script>

<template>
  <span class="badge" :class="[`badge--${tone}`, `badge--${size}`]">
    <span v-if="dot" class="badge__dot" aria-hidden="true"></span>
    <slot />
  </span>
</template>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border-radius: var(--radius-full);
  font-weight: 600;
  line-height: 1.3;
  white-space: nowrap;
}

.badge--sm {
  padding: 0.2rem 0.55rem;
  font-size: 0.68rem;
}

.badge--md {
  padding: 0.3rem 0.7rem;
  font-size: var(--text-xs);
}

.badge__dot {
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 50%;
  background: currentColor;
}

.badge--neutral {
  background: var(--bg-subtle);
  color: var(--text-soft);
}

.badge--teal {
  background: var(--brand-50);
  color: var(--brand-700);
}

.badge--green {
  background: var(--ok-bg);
  color: var(--ok);
}

.badge--amber {
  background: var(--warn-bg);
  color: var(--warn);
}

.badge--red {
  background: var(--error-bg);
  color: var(--error);
}

.badge--violet {
  background: #f5f3ff;
  color: #6d28d9;
}

.badge--rose {
  background: var(--cat-konser-soft);
  color: var(--cat-konser);
}

.badge--blue {
  background: var(--info-bg);
  color: var(--info);
}
</style>

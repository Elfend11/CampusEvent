<script setup>
defineProps({
  title: { type: String, default: 'Belum ada data' },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="empty">
    <div class="empty__icon" aria-hidden="true">
      <slot name="icon">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-1.7a2.8 2.8 0 0 0 0-5.6z" />
          <path d="M9 9.5v5" stroke-dasharray="1 2.4" />
        </svg>
      </slot>
    </div>
    <p class="empty__title">{{ title }}</p>
    <p v-if="description" class="empty__description">{{ description }}</p>
    <div v-if="$slots.actions" class="empty__actions">
      <slot name="actions" />
    </div>
  </div>
</template>

<style scoped>
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: clamp(2rem, 6vw, 3.5rem) 1.5rem;
  border: 1px dashed var(--border-strong);
  border-radius: var(--radius-xl);
  background: var(--surface);
  text-align: center;
}

.empty__icon {
  display: grid;
  width: 3.4rem;
  height: 3.4rem;
  place-items: center;
  margin-bottom: 1rem;
  border-radius: var(--radius-lg);
  background: var(--brand-50);
  color: var(--brand);
}

.empty__title {
  margin: 0;
  font-size: var(--text-lg);
  font-weight: 700;
  letter-spacing: var(--tracking-tight);
}

.empty__description {
  max-width: 30rem;
  margin: 0.4rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
}

.empty__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.6rem;
  margin-top: 1.25rem;
}
</style>

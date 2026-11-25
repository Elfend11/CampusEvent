<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'info' },
  title: { type: String, default: '' },
})

const role = computed(() => (['error', 'warning'].includes(props.variant) ? 'alert' : 'status'))
const live = computed(() => (props.variant === 'error' ? 'assertive' : 'polite'))
</script>

<template>
  <div class="alert" :class="`alert--${variant}`" :role="role" :aria-live="live">
    <span class="alert__icon" aria-hidden="true">
      <svg v-if="variant === 'error' || variant === 'warning'" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
        <path d="M12 8v5" />
        <circle cx="12" cy="16.5" r="0.9" fill="currentColor" stroke="none" />
        <circle cx="12" cy="12" r="8.5" />
      </svg>
      <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="8.5" />
        <path d="m8.5 12.2 2.4 2.4 4.6-4.8" />
      </svg>
    </span>
    <div class="alert__body">
      <p v-if="title" class="alert__title">{{ title }}</p>
      <div class="alert__content"><slot /></div>
    </div>
    <div v-if="$slots.actions" class="alert__actions">
      <slot name="actions" />
    </div>
  </div>
</template>

<style scoped>
.alert {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.1rem;
  border: 1px solid;
  border-radius: var(--radius-lg);
  font-size: var(--text-sm);
}

.alert--info {
  border-color: var(--info-border);
  background: var(--info-bg);
  color: #075985;
}

.alert--success {
  border-color: var(--ok-border);
  background: var(--ok-bg);
  color: #166534;
}

.alert--warning {
  border-color: var(--warn-border);
  background: var(--warn-bg);
  color: #92400e;
}

.alert--error {
  border-color: var(--error-border);
  background: var(--error-bg);
  color: #991b1b;
}

.alert__icon {
  display: inline-flex;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.alert__body {
  flex: 1;
  min-width: 0;
}

.alert__title {
  margin: 0 0 0.15rem;
  font-weight: 700;
}

.alert__content {
  line-height: 1.55;
}

.alert__content :deep(p) {
  margin: 0;
}

.alert__actions {
  display: flex;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 0.5rem;
}

@media (max-width: 560px) {
  .alert {
    flex-wrap: wrap;
  }

  .alert__actions {
    width: 100%;
    padding-left: 2rem;
  }
}
</style>

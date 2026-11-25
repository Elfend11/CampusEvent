<script setup>
import { computed, useAttrs } from 'vue'
import { RouterLink } from 'vue-router'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  to: { type: [String, Object], default: null },
  href: { type: String, default: null },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})

const attrs = useAttrs()

const isDisabled = computed(() => props.disabled || props.loading)
const tag = computed(() => (props.to ? RouterLink : props.href ? 'a' : 'button'))

const bindings = computed(() => {
  if (props.to) {
    return {
      to: props.to,
      'aria-disabled': isDisabled.value ? 'true' : undefined,
      tabindex: isDisabled.value ? -1 : undefined,
    }
  }

  if (props.href) {
    return { href: props.href }
  }

  return { type: props.type, disabled: isDisabled.value }
})
</script>

<template>
  <component
    :is="tag"
    v-bind="{ ...attrs, ...bindings }"
    class="btn"
    :class="[`btn--${variant}`, `btn--${size}`, { 'btn--block': block, 'is-loading': loading }]"
  >
    <span v-if="loading" class="btn__spinner" aria-hidden="true"></span>
    <slot />
  </component>
</template>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: 1px solid transparent;
  border-radius: var(--radius-full);
  font-family: var(--font-sans);
  font-weight: 600;
  line-height: 1.2;
  text-align: center;
  white-space: nowrap;
  cursor: pointer;
  transition:
    background-color var(--transition-fast),
    border-color var(--transition-fast),
    color var(--transition-fast),
    box-shadow var(--transition-fast),
    transform var(--transition-fast);
}

.btn:hover:not(:disabled):not([aria-disabled='true']) {
  transform: translateY(-1px);
}

.btn:active:not(:disabled):not([aria-disabled='true']) {
  transform: translateY(0);
}

.btn:disabled,
.btn[aria-disabled='true'] {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

.btn--sm {
  min-height: 40px;
  padding: 0.5rem 1rem;
  font-size: var(--text-sm);
}

.btn--md {
  min-height: 46px;
  padding: 0.7rem 1.25rem;
  font-size: var(--text-sm);
}

.btn--lg {
  min-height: 54px;
  padding: 0.85rem 1.6rem;
  font-size: var(--text-base);
}

.btn--primary {
  background: var(--brand);
  color: #fff;
  box-shadow: var(--shadow-brand);
}

.btn--primary:hover:not(:disabled):not([aria-disabled='true']) {
  background: var(--brand-dark);
}

.btn--secondary {
  border-color: var(--border);
  background: var(--surface);
  color: var(--text);
  box-shadow: var(--shadow-xs);
}

.btn--secondary:hover:not(:disabled):not([aria-disabled='true']) {
  border-color: var(--brand-300);
  color: var(--brand-700);
  background: var(--brand-50);
}

.btn--soft {
  background: var(--brand-50);
  color: var(--brand-700);
}

.btn--soft:hover:not(:disabled):not([aria-disabled='true']) {
  background: var(--brand-100);
}

.btn--ghost {
  background: transparent;
  color: var(--text-soft);
}

.btn--ghost:hover:not(:disabled):not([aria-disabled='true']) {
  background: var(--bg-subtle);
  color: var(--text);
}

.btn--inverse {
  background: #fff;
  color: var(--brand-700);
}

.btn--inverse:hover:not(:disabled):not([aria-disabled='true']) {
  background: var(--brand-50);
}

.btn--outline-inverse {
  border-color: rgb(255 255 255 / 35%);
  background: rgb(255 255 255 / 8%);
  color: #fff;
}

.btn--outline-inverse:hover:not(:disabled):not([aria-disabled='true']) {
  background: rgb(255 255 255 / 16%);
  border-color: rgb(255 255 255 / 55%);
}

.btn--danger {
  background: var(--error-bg);
  border-color: var(--error-border);
  color: var(--error);
}

.btn--block {
  width: 100%;
}

.btn__spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: btn-spin 0.7s linear infinite;
}

@keyframes btn-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

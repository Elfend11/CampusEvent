<script setup>
import { useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
})

defineEmits(['update:modelValue'])

const attrs = useAttrs()
</script>

<template>
  <span class="input-wrap">
    <span v-if="$slots.icon" class="input-wrap__icon" aria-hidden="true">
      <slot name="icon" />
    </span>
    <input
      v-bind="attrs"
      :type="type"
      :value="modelValue"
      class="input"
      :class="{ 'input--with-icon': $slots.icon }"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </span>
</template>

<style scoped>
.input-wrap {
  position: relative;
  display: flex;
  width: 100%;
  align-items: center;
}

.input-wrap__icon {
  position: absolute;
  left: 0.95rem;
  display: inline-flex;
  color: var(--muted);
  pointer-events: none;
}

.input {
  width: 100%;
  min-height: 46px;
  padding: 0.65rem 0.9rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  color: var(--text);
  font: inherit;
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.input--with-icon {
  padding-left: 2.6rem;
}

.input::placeholder {
  color: #94a3b8;
}

.input:hover {
  border-color: var(--border-strong);
}

.input:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 4px rgb(20 184 166 / 15%);
  outline: none;
}
</style>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  width: { type: [String, Number], default: '100%' },
  height: { type: [String, Number], default: '1rem' },
  radius: { type: String, default: 'var(--radius-sm)' },
  circle: { type: Boolean, default: false },
})

const size = (value) => (typeof value === 'number' ? `${value}px` : value)

const styles = computed(() => ({
  width: size(props.width),
  height: size(props.height),
  borderRadius: props.circle ? '50%' : props.radius,
}))
</script>

<template>
  <span class="skeleton" :style="styles" aria-hidden="true"></span>
</template>

<style scoped>
.skeleton {
  display: block;
  background: linear-gradient(100deg, #eef2f6 30%, #f8fafc 50%, #eef2f6 70%);
  background-size: 220% 100%;
  animation: skeleton-shimmer 1.4s ease-in-out infinite;
}

@keyframes skeleton-shimmer {
  from {
    background-position: 120% 0;
  }

  to {
    background-position: -120% 0;
  }
}
</style>

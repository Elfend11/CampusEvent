<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  expiresAt: {
    type: String,
    required: true,
  },
})

const now = ref(Date.now())
let timer = null

const remaining = computed(() =>
  Math.max(0, new Date(props.expiresAt).getTime() - now.value),
)

const label = computed(() => {
  const totalSeconds = Math.floor(remaining.value / 1000)
  const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0')
  const seconds = String(totalSeconds % 60).padStart(2, '0')

  return `${minutes}:${seconds}`
})

onMounted(() => {
  timer = window.setInterval(() => {
    now.value = Date.now()
  }, 1000)
})

onBeforeUnmount(() => {
  window.clearInterval(timer)
})
</script>

<template>
  <span class="countdown" :class="{ 'countdown-expired': remaining === 0 }">
    {{ remaining === 0 ? 'Kedaluwarsa' : label }}
  </span>
</template>

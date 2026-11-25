<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import DefaultLayout from '@/layouts/DefaultLayout.vue'
import StudentLayout from '@/layouts/StudentLayout.vue'

const route = useRoute()

const layout = computed(() =>
  route.meta.layout === 'student' ? StudentLayout : DefaultLayout,
)
</script>

<template>
  <component :is="layout" />
</template>

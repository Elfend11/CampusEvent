<script setup>
defineProps({
  name: { type: String, default: 'spark' },
  size: { type: [Number, String], default: 24 },
})
</script>

<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
  >
    <template v-if="name === 'seminar'">
      <path d="M4 5.5h16v10H4z" />
      <path d="M12 15.5V19" />
      <path d="M8.5 19h7" />
      <path d="M8 9h5" />
      <path d="M8 11.5h3" />
      <circle cx="16" cy="10" r="1.4" />
    </template>

    <template v-else-if="name === 'workshop'">
      <path d="M14.5 6.5a3.5 3.5 0 0 0 4.4 4.4l-7.6 7.6a2.4 2.4 0 0 1-3.4-3.4z" />
      <path d="m6.5 5.5 3 3" />
      <path d="m9.5 8.5-1.2 1.2" />
      <path d="m16.5 15.5 2 2" />
    </template>

    <template v-else-if="name === 'kompetisi'">
      <path d="M8 4h8v5a4 4 0 0 1-8 0z" />
      <path d="M8 5.5H5.5V7A3.5 3.5 0 0 0 9 10.5" />
      <path d="M16 5.5h2.5V7A3.5 3.5 0 0 1 15 10.5" />
      <path d="M12 13v3.5" />
      <path d="M8.5 20h7" />
      <path d="M9.5 16.5h5l.7 3.5h-6.4z" />
    </template>

    <template v-else-if="name === 'konser'">
      <path d="M9 17.5V6.8l9-2v10.7" />
      <circle cx="6.6" cy="18" r="2.4" />
      <circle cx="15.6" cy="15.8" r="2.4" />
      <path d="M9 10.2l9-2" />
    </template>

    <template v-else>
      <path d="M12 3.5 13.6 9l5.4 1.7-5.4 1.7L12 18l-1.6-5.6L5 10.7 10.4 9z" />
      <path d="M18.5 16.5v3" />
      <path d="M17 18h3" />
    </template>
  </svg>
</template>

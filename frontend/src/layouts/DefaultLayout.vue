<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <RouterLink to="/" class="brand">CampusEvent</RouterLink>
      <nav class="app-nav">
        <RouterLink to="/">Beranda</RouterLink>
      </nav>
    </header>

    <main class="app-main">
      <RouterView />
    </main>

    <footer class="app-footer">
      CampusEvent &middot; Sistem Informasi Manajemen Event Kampus
    </footer>
  </div>
</template>

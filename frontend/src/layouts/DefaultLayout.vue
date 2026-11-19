<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.push({ name: 'home' })
}
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <RouterLink to="/" class="brand">CampusEvent</RouterLink>

      <nav class="app-nav">
        <RouterLink to="/">Beranda</RouterLink>
        <RouterLink
          v-if="auth.hasAnyRole(['SUPER_ADMIN', 'ORGANIZATION_ADMIN'])"
          to="/organizations"
        >
          Organisasi
        </RouterLink>
        <RouterLink v-if="auth.isAuthenticated" to="/dashboard">Dashboard</RouterLink>
        <RouterLink v-else to="/login">Masuk</RouterLink>
      </nav>

      <div v-if="auth.isAuthenticated" class="account">
        <span class="account-name">{{ auth.user.name }}</span>
        <button type="button" class="link-button" @click="logout">Keluar</button>
      </div>
    </header>

    <main class="app-main">
      <RouterView />
    </main>

    <footer class="app-footer">
      CampusEvent &middot; Sistem Informasi Manajemen Event Kampus
    </footer>
  </div>
</template>

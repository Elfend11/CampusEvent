<script setup>
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.replace({ name: 'home' })
}
</script>

<template>
  <section v-if="auth.user" class="dashboard">
    <p class="eyebrow">Dashboard</p>
    <h1>Halo, {{ auth.user.name }}</h1>
    <p class="muted">{{ auth.user.email }}</p>

    <div class="panel">
      <h2>Role Akun</h2>
      <ul class="role-list">
        <li v-for="role in auth.roles" :key="role">{{ role }}</li>
      </ul>
      <p class="muted">
        Modul event, pendaftaran, tiket, dan check-in akan tersedia pada tahap
        berikutnya.
      </p>
    </div>

    <button type="button" class="secondary" @click="logout">Keluar</button>
  </section>
</template>

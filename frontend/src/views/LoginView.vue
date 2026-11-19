<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const error = ref(null)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  error.value = null

  try {
    await auth.login({ email: email.value, password: password.value })

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await router.replace(redirect)
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data?.errors?.email?.[0] ?? 'Email atau password tidak sesuai.'
    } else if (err.response?.status === 429) {
      error.value = 'Terlalu banyak percobaan. Coba lagi dalam satu menit.'
    } else {
      error.value = 'Tidak dapat masuk. Periksa koneksi ke server.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="auth-card">
    <h1>Masuk</h1>
    <p class="muted">Gunakan akun kampus Anda untuk mengakses CampusEvent.</p>

    <form class="auth-form" @submit.prevent="submit">
      <label for="email">Email</label>
      <input
        id="email"
        v-model.trim="email"
        type="email"
        name="email"
        autocomplete="email"
        required
      />

      <label for="password">Password</label>
      <input
        id="password"
        v-model="password"
        type="password"
        name="password"
        autocomplete="current-password"
        required
      />

      <p v-if="error" class="form-error">{{ error }}</p>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>
  </section>
</template>

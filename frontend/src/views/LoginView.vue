<script setup>
import { ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const error = ref(null)
const notice = ref(
  route.query.verified === '1' ? 'Email berhasil diverifikasi. Silakan login.' : null,
)
const unverifiedEmail = ref(null)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  error.value = null
  unverifiedEmail.value = null

  try {
    await auth.login({ email: email.value, password: password.value })

    const roles = auth.roles
    const redirect =
      typeof route.query.redirect === 'string'
        ? route.query.redirect
        : auth.hasRole('SUPER_ADMIN')
          ? '/dashboard?view=manager'
          : auth.hasRole('STUDENT')
            ? '/dashboard?view=student'
            : '/dashboard'
    await router.replace(redirect)
  } catch (err) {
    if (err.response?.status === 403 && err.response.data?.code === 'EMAIL_UNVERIFIED') {
      unverifiedEmail.value = email.value
      error.value = err.response.data?.message ?? 'Email belum diverifikasi.'
    } else if (err.response?.status === 422) {
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

    <p v-if="notice" class="form-success">{{ notice }}</p>

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

      <p v-if="unverifiedEmail" class="muted">
        <RouterLink :to="{ name: 'verify-email', query: { email: unverifiedEmail, sent: '0' } }">
          Verifikasi email sekarang
        </RouterLink>
      </p>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>

    <p class="muted">
      Belum punya akun?
      <RouterLink to="/register">Daftar sebagai mahasiswa</RouterLink>
    </p>
  </section>
</template>

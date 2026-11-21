<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref(null)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  error.value = null

  try {
    await auth.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    await router.replace('/dashboard')
  } catch (err) {
    const errors = err.response?.data?.errors

    error.value =
      errors?.email?.[0] ??
      errors?.password?.[0] ??
      errors?.name?.[0] ??
      (err.response?.status === 429
        ? 'Terlalu banyak percobaan. Coba lagi dalam satu menit.'
        : 'Tidak dapat membuat akun. Periksa koneksi ke server.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="auth-card">
    <h1>Daftar Akun Mahasiswa</h1>
    <p class="muted">Buat akun untuk mendaftar event dan memperoleh tiket digital.</p>

    <form class="auth-form" @submit.prevent="submit">
      <label for="name">Nama Lengkap</label>
      <input
        id="name"
        v-model.trim="name"
        type="text"
        name="name"
        autocomplete="name"
        required
      />

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
        autocomplete="new-password"
        minlength="8"
        required
      />

      <label for="password-confirmation">Konfirmasi Password</label>
      <input
        id="password-confirmation"
        v-model="passwordConfirmation"
        type="password"
        name="password_confirmation"
        autocomplete="new-password"
        minlength="8"
        required
      />

      <p v-if="error" class="form-error">{{ error }}</p>

      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Memproses...' : 'Daftar' }}
      </button>
    </form>

    <p class="muted">
      Sudah punya akun?
      <RouterLink to="/login">Masuk di sini</RouterLink>
    </p>
  </section>
</template>

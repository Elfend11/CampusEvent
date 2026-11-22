<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref(typeof route.query.email === 'string' ? route.query.email : '')
const code = ref('')
const error = ref(null)
const notice = ref(null)
const submitting = ref(false)
const resending = ref(false)
const cooldown = ref(0)

let cooldownTimer = null

function startCooldown(seconds) {
  const value = Number(seconds)

  cooldown.value = Number.isFinite(value) && value > 0 ? Math.ceil(value) : 0
  clearInterval(cooldownTimer)

  if (cooldown.value === 0) {
    return
  }

  cooldownTimer = setInterval(() => {
    cooldown.value = Math.max(0, cooldown.value - 1)

    if (cooldown.value === 0) {
      clearInterval(cooldownTimer)
    }
  }, 1000)
}

onMounted(() => {
  if (!email.value) {
    router.replace({ name: 'register' })
    return
  }

  if (route.query.sent === '0') {
    notice.value =
      'Akun dibuat, tetapi email verifikasi belum terkirim. Gunakan kirim ulang kode di bawah.'
  }

  startCooldown(route.query.cooldown)
})

onBeforeUnmount(() => {
  clearInterval(cooldownTimer)
})

async function submit() {
  submitting.value = true
  error.value = null
  notice.value = null

  try {
    await auth.verifyOtp({ email: email.value, code: code.value })

    await router.replace({ name: 'login', query: { verified: '1' } })
  } catch (err) {
    error.value =
      err.response?.data?.message ??
      err.response?.data?.errors?.code?.[0] ??
      'Kode verifikasi tidak valid. Periksa kembali kode dari email Anda.'
  } finally {
    submitting.value = false
  }
}

async function resend() {
  resending.value = true
  error.value = null
  notice.value = null

  try {
    const result = await auth.resendOtp({ email: email.value })

    notice.value = result.message ?? 'Kode verifikasi baru telah dikirim.'
    startCooldown(result.retry_after ?? 60)
  } catch (err) {
    if (err.response?.status === 429) {
      const retryAfter = Number(err.response.data?.retry_after ?? 60)

      startCooldown(retryAfter)
      error.value = `Tunggu ${retryAfter} detik sebelum meminta kode baru.`
    } else {
      error.value =
        err.response?.data?.message ??
        'Tidak dapat mengirim kode baru. Periksa koneksi ke server.'
    }
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <section class="auth-card">
    <h1>Verifikasi Email</h1>
    <p class="muted">
      Masukkan kode enam digit yang dikirim ke
      <strong>{{ email }}</strong>.
    </p>

    <p v-if="notice" class="form-success">{{ notice }}</p>
    <p v-if="error" class="form-error">{{ error }}</p>

    <form class="auth-form" @submit.prevent="submit">
      <label for="otp-code">Kode Verifikasi</label>
      <input
        id="otp-code"
        v-model.trim="code"
        type="text"
        name="code"
        inputmode="numeric"
        pattern="[0-9]{6}"
        maxlength="6"
        autocomplete="one-time-code"
        placeholder="000000"
        required
      />

      <button type="submit" :disabled="submitting || code.length !== 6">
        {{ submitting ? 'Memverifikasi...' : 'Verifikasi Email' }}
      </button>
    </form>

    <p class="muted">
      Belum menerima kode?
      <button
        type="button"
        class="link-button"
        :disabled="resending || cooldown > 0"
        @click="resend"
      >
        {{ cooldown > 0 ? `Kirim ulang dalam ${cooldown} detik` : 'Kirim ulang kode' }}
      </button>
    </p>

    <p class="muted">
      Sudah terverifikasi?
      <RouterLink to="/login">Masuk di sini</RouterLink>
    </p>
  </section>
</template>

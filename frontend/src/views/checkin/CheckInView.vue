<script setup>
import { ref } from 'vue'

import api from '@/services/api'
import { formatDateTime } from '@/utils/format'

const token = ref('')
const loading = ref(false)
const error = ref(null)
const result = ref(null)

async function submit() {
  const value = token.value.trim()

  if (!value) {
    error.value = 'Masukkan token QR tiket.'
    return
  }

  loading.value = true
  error.value = null
  result.value = null

  try {
    const { data } = await api.post('/check-in/validate', { token: value })
    result.value = data
  } catch (err) {
    const payload = err.response?.data
    error.value = payload?.message ?? 'Check-in gagal.'
    result.value = payload?.data ? { data: payload.data, valid: false } : null
  } finally {
    loading.value = false
  }
}

function reset() {
  token.value = ''
  error.value = null
  result.value = null
}
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Petugas Check-in</p>
        <h1>Validasi Tiket</h1>
      </div>
    </div>

    <div class="panel">
      <h2>Pindai atau Masukkan Token QR</h2>
      <p class="muted">
        Gunakan pemindai QR untuk memasukkan token tiket, lalu tekan Validasi.
        Setiap tiket hanya dapat digunakan satu kali.
      </p>

      <form class="stack-form" @submit.prevent="submit">
        <div class="field">
          <label for="ticket-token">Token Tiket</label>
          <textarea
            id="ticket-token"
            v-model="token"
            rows="3"
            autocomplete="off"
            placeholder="Tempel token QR tiket di sini"
          ></textarea>
        </div>

        <div class="payment-actions">
          <button type="submit" class="primary-button" :disabled="loading">
            {{ loading ? 'Memvalidasi...' : 'Validasi & Check-in' }}
          </button>
          <button type="button" class="secondary" :disabled="loading" @click="reset">
            Bersihkan
          </button>
        </div>
      </form>

      <p v-if="error" class="form-error">{{ error }}</p>
    </div>

    <div v-if="result?.valid" class="panel registration-box registration-confirmed">
      <p class="registration-status">Check-in berhasil</p>

      <dl class="detail-list">
        <div>
          <dt>Peserta</dt>
          <dd>{{ result.data?.owner?.name }}</dd>
        </div>
        <div>
          <dt>Event</dt>
          <dd>{{ result.data?.event?.title }}</dd>
        </div>
        <div>
          <dt>Kode Tiket</dt>
          <dd>{{ result.data?.ticket_code }}</dd>
        </div>
        <div>
          <dt>Waktu Check-in</dt>
          <dd>{{ formatDateTime(result.data?.attendance?.checked_in_at) }}</dd>
        </div>
      </dl>
    </div>

    <div v-else-if="result?.data" class="panel registration-box">
      <p class="registration-status">Tiket tidak dapat digunakan</p>

      <dl class="detail-list">
        <div>
          <dt>Peserta</dt>
          <dd>{{ result.data.owner?.name }}</dd>
        </div>
        <div>
          <dt>Event</dt>
          <dd>{{ result.data.event?.title }}</dd>
        </div>
        <div>
          <dt>Kode Tiket</dt>
          <dd>{{ result.data.ticket_code }}</dd>
        </div>
        <div>
          <dt>Status</dt>
          <dd>{{ result.data.status }}</dd>
        </div>
      </dl>
    </div>
  </section>
</template>

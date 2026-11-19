<script setup>
import { onMounted, ref } from 'vue'

import api from '@/services/api'

const health = ref(null)
const error = ref(null)
const loading = ref(false)

async function checkHealth() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/health')
    health.value = data
  } catch (err) {
    health.value = null
    error.value = err.response?.data?.message ?? 'Tidak dapat terhubung ke backend.'
  } finally {
    loading.value = false
  }
}

onMounted(checkHealth)
</script>

<template>
  <section class="hero">
    <p class="eyebrow">Integrated Campus Event &amp; Ticketing Management System</p>
    <h1>CampusEvent</h1>
    <p class="lead">
      Temukan kegiatan kampus, daftar sebagai peserta, dan kelola tiket digital
      dalam satu platform.
    </p>

    <div class="status-card">
      <div class="status-head">
        <h2>Status Integrasi</h2>
        <button type="button" :disabled="loading" @click="checkHealth">
          {{ loading ? 'Memeriksa...' : 'Periksa Ulang' }}
        </button>
      </div>

      <p v-if="loading" class="muted">Menghubungi backend...</p>

      <p v-else-if="error" class="status status-error">
        <span class="dot"></span>{{ error }}
      </p>

      <div v-else-if="health" class="status status-ok">
        <span class="dot"></span>
        <span>
          Backend terhubung &middot; {{ health.service }} ({{ health.version }})
        </span>
      </div>
    </div>
  </section>
</template>

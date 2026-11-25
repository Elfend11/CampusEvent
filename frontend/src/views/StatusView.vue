<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import AppAlert from '@/components/ui/AppAlert.vue'
import AppButton from '@/components/ui/AppButton.vue'
import api from '@/services/api'
import { formatDateTime } from '@/utils/format'

const health = ref(null)
const error = ref(null)
const loading = ref(false)
const checkedAt = ref(null)

const state = computed(() => {
  if (loading.value) {
    return { tone: 'pending', label: 'Memeriksa koneksi' }
  }

  if (error.value) {
    return { tone: 'error', label: 'Gangguan koneksi' }
  }

  if (health.value) {
    return { tone: 'ok', label: 'Layanan terhubung' }
  }

  return { tone: 'idle', label: 'Belum diperiksa' }
})

async function checkHealth() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/health')
    health.value = data
    checkedAt.value = new Date().toISOString()
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
  <section class="status-page" aria-labelledby="status-title">
    <div class="status-page__head">
      <p class="eyebrow">Diagnostik</p>
      <h1 id="status-title" class="status-page__title">Status layanan</h1>
      <p class="status-page__lead">
        Halaman ini memeriksa koneksi antara antarmuka CampusEvent dan API. Gunakan
        saat pengembangan atau ketika kamu mengalami kendala koneksi.
      </p>
    </div>

    <div class="status-card">
      <div class="status-card__top">
        <span class="status-pill" :class="`status-pill--${state.tone}`">
          <span class="status-pill__dot" aria-hidden="true"></span>
          {{ state.label }}
        </span>
        <AppButton size="sm" variant="secondary" :loading="loading" @click="checkHealth">
          Periksa ulang
        </AppButton>
      </div>

      <p class="sr-only" role="status" aria-live="polite">{{ state.label }}</p>

      <dl v-if="health" class="status-facts">
        <div>
          <dt>Layanan</dt>
          <dd>{{ health.service }}</dd>
        </div>
        <div>
          <dt>Versi</dt>
          <dd>{{ health.version }}</dd>
        </div>
        <div>
          <dt>Status</dt>
          <dd>{{ health.status }}</dd>
        </div>
        <div>
          <dt>Waktu server</dt>
          <dd>{{ formatDateTime(health.time) }}</dd>
        </div>
        <div>
          <dt>Terakhir diperiksa</dt>
          <dd>{{ formatDateTime(checkedAt) }}</dd>
        </div>
      </dl>

      <p v-else-if="loading" class="status-card__muted">Menghubungi backend…</p>

      <AppAlert v-else-if="error" variant="error" title="Tidak dapat terhubung">
        {{ error }}
      </AppAlert>

      <AppAlert v-else variant="info">
        Belum ada hasil pemeriksaan. Tekan “Periksa ulang” untuk memulai.
      </AppAlert>
    </div>

    <p class="status-page__back">
      <RouterLink :to="{ name: 'home' }">← Kembali ke beranda</RouterLink>
    </p>
  </section>
</template>

<style scoped>
.status-page {
  max-width: 46rem;
  margin: 0 auto;
}

.status-page__head {
  margin-bottom: 1.5rem;
}

.status-page__title {
  margin: 0;
  font-size: clamp(1.7rem, 3.4vw, 2.3rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.status-page__lead {
  max-width: 40rem;
  margin: 0.5rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.status-card {
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  background: var(--surface);
  box-shadow: var(--shadow-xs);
}

.status-card__top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.35rem 0.75rem;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 700;
}

.status-pill__dot {
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
  background: currentColor;
}

.status-pill--pending {
  background: var(--warn-bg);
  color: var(--warn);
}

.status-pill--error {
  background: var(--error-bg);
  color: var(--error);
}

.status-pill--ok {
  background: var(--ok-bg);
  color: var(--ok);
}

.status-pill--idle {
  background: var(--bg-subtle);
  color: var(--muted);
}

.status-facts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
  gap: 1rem;
  margin: 1.4rem 0 0;
  padding-top: 1.3rem;
  border-top: 1px solid var(--border);
}

.status-facts dt {
  color: var(--muted);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
}

.status-facts dd {
  margin: 0.25rem 0 0;
  font-size: var(--text-sm);
  font-weight: 600;
}

.status-card__muted {
  margin: 1.4rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
}

.status-page__back {
  margin: 1.5rem 0 0;
  font-size: var(--text-sm);
  font-weight: 600;
}

.status-page__back a {
  color: var(--brand-700);
}

.status-page__back a:hover {
  color: var(--brand);
}
</style>

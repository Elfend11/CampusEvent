<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { formatDateTime, formatPrice } from '@/utils/format'

const route = useRoute()
const auth = useAuthStore()

const event = ref(null)
const loading = ref(false)
const error = ref(null)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/events/${route.params.id}`)
    event.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Event tidak ditemukan.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <section>
    <p v-if="loading" class="muted">Memuat event...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>

    <article v-else-if="event">
      <p class="eyebrow">{{ event.category }}</p>
      <h1 class="event-title">{{ event.title }}</h1>
      <p class="muted">{{ event.organization?.name }} &middot; {{ event.location }}</p>

      <div class="panel">
        <h2>Informasi Event</h2>
        <dl class="detail-list">
          <div>
            <dt>Mulai</dt>
            <dd>{{ formatDateTime(event.event_start) }}</dd>
          </div>
          <div>
            <dt>Selesai</dt>
            <dd>{{ formatDateTime(event.event_end) }}</dd>
          </div>
          <div>
            <dt>Pendaftaran Dibuka</dt>
            <dd>{{ formatDateTime(event.registration_start) }}</dd>
          </div>
          <div>
            <dt>Pendaftaran Ditutup</dt>
            <dd>{{ formatDateTime(event.registration_end) }}</dd>
          </div>
          <div>
            <dt>Harga Tiket</dt>
            <dd>{{ formatPrice(event.ticket_price) }}</dd>
          </div>
          <div>
            <dt>Sisa Kuota</dt>
            <dd>{{ event.available_quota }} dari {{ event.capacity }}</dd>
          </div>
        </dl>

        <p class="description">{{ event.description }}</p>
      </div>

      <div class="panel">
        <h2>Pendaftaran</h2>
        <p v-if="!auth.isAuthenticated" class="muted">
          Masuk sebagai mahasiswa untuk mendaftar event ini.
        </p>
        <p v-else class="muted">
          Fitur pendaftaran akan tersedia pada tahap berikutnya.
        </p>
      </div>
    </article>
  </section>
</template>

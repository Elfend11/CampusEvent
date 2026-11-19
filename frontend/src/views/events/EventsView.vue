<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import api from '@/services/api'
import { formatDateTime, formatPrice } from '@/utils/format'

const events = ref([])
const loading = ref(false)
const error = ref(null)

const filters = ref({ search: '', category: '' })

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/events', { params: filters.value })
    events.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat event.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Jelajahi</p>
        <h1>Event Kampus</h1>
      </div>
    </div>

    <form class="filter-bar" @submit.prevent="load">
      <input v-model.trim="filters.search" type="search" placeholder="Cari event atau lokasi..." />
      <select v-model="filters.category">
        <option value="">Semua kategori</option>
        <option value="Seminar">Seminar</option>
        <option value="Workshop">Workshop</option>
        <option value="Kompetisi">Kompetisi</option>
        <option value="Konser">Konser</option>
      </select>
      <button type="submit">Cari</button>
    </form>

    <p v-if="loading" class="muted">Memuat event...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="!events.length" class="muted">Belum ada event yang tersedia.</p>

    <div v-else class="event-grid">
      <article v-for="event in events" :key="event.id" class="event-card">
        <p class="event-category">{{ event.category }}</p>
        <h2>
          <RouterLink :to="`/events/${event.id}`">{{ event.title }}</RouterLink>
        </h2>
        <p class="muted">{{ event.organization?.name }}</p>
        <dl class="event-meta">
          <div>
            <dt>Waktu</dt>
            <dd>{{ formatDateTime(event.event_start) }}</dd>
          </div>
          <div>
            <dt>Lokasi</dt>
            <dd>{{ event.location }}</dd>
          </div>
          <div>
            <dt>Harga</dt>
            <dd>{{ formatPrice(event.ticket_price) }}</dd>
          </div>
          <div>
            <dt>Kuota</dt>
            <dd>{{ event.available_quota }} / {{ event.capacity }}</dd>
          </div>
        </dl>
        <RouterLink :to="`/events/${event.id}`" class="secondary-link">Lihat detail</RouterLink>
      </article>
    </div>
  </section>
</template>

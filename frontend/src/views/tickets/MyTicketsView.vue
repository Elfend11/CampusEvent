<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import api from '@/services/api'
import { formatDateTime } from '@/utils/format'

const tickets = ref([])
const loading = ref(false)
const error = ref(null)

const statusLabels = {
  ACTIVE: 'Aktif',
  USED: 'Sudah Digunakan',
  VOID: 'Dibatalkan',
}

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/tickets')
    tickets.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat tiket.'
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
        <p class="eyebrow">Akun Saya</p>
        <h1>Tiket Saya</h1>
      </div>
    </div>

    <p v-if="loading" class="muted">Memuat tiket...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="!tickets.length" class="muted">
      Belum ada tiket. <RouterLink to="/events">Jelajahi event kampus</RouterLink>.
    </p>

    <ul v-else class="card-list">
      <li v-for="ticket in tickets" :key="ticket.id" class="card">
        <div>
          <p class="status-badge" :data-status="ticket.status">
            {{ statusLabels[ticket.status] ?? ticket.status }}
          </p>
          <p class="card-title">{{ ticket.event?.title }}</p>
          <p class="muted">
            {{ formatDateTime(ticket.event?.event_start) }}
            &middot; {{ ticket.event?.location }}
          </p>
          <p class="muted">Kode tiket: {{ ticket.ticket_code }}</p>
        </div>

        <div class="row-actions">
          <RouterLink :to="`/tickets/${ticket.id}`" class="primary-link">
            Lihat Tiket
          </RouterLink>
        </div>
      </li>
    </ul>
  </section>
</template>

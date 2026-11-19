<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import api from '@/services/api'
import { formatDateTime } from '@/utils/format'

const events = ref([])
const loading = ref(false)
const error = ref(null)

const statusActions = {
  DRAFT: [
    { label: 'Publikasikan', target: 'PUBLISHED' },
    { label: 'Batalkan', target: 'CANCELLED' },
  ],
  PUBLISHED: [
    { label: 'Tutup', target: 'CLOSED' },
    { label: 'Batalkan', target: 'CANCELLED' },
  ],
  CLOSED: [],
  CANCELLED: [],
}

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/events', { params: { mine: 1 } })
    events.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat event.'
  } finally {
    loading.value = false
  }
}

async function changeStatus(event, target) {
  error.value = null

  try {
    await api.patch(`/events/${event.id}/status`, { status: target })
    await load()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal mengubah status event.'
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1>Kelola Event</h1>
      </div>
      <RouterLink to="/manage/events/new" class="primary-link">Tambah Event</RouterLink>
    </div>

    <p v-if="loading" class="muted">Memuat event...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="!events.length" class="muted">Belum ada event.</p>

    <ul v-else class="card-list">
      <li v-for="event in events" :key="event.id" class="card">
        <div>
          <p class="status-badge" :data-status="event.status">{{ event.status }}</p>
          <strong>{{ event.title }}</strong>
          <p class="muted">
            {{ event.organization?.name }} &middot; {{ formatDateTime(event.event_start) }}
          </p>
        </div>

        <div class="row-actions">
          <RouterLink :to="`/manage/events/${event.id}/edit`" class="secondary-link">
            Edit
          </RouterLink>
          <button
            v-for="action in statusActions[event.status]"
            :key="action.target"
            type="button"
            class="link-button"
            @click="changeStatus(event, action.target)"
          >
            {{ action.label }}
          </button>
        </div>
      </li>
    </ul>
  </section>
</template>

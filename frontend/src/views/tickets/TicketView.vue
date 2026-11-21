<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import QRCode from 'qrcode'

import api from '@/services/api'
import { formatDateTime } from '@/utils/format'

const route = useRoute()

const ticket = ref(null)
const qrDataUrl = ref(null)
const loading = ref(false)
const error = ref(null)

const statusLabels = {
  ACTIVE: 'Aktif',
  USED: 'Sudah Digunakan',
  VOID: 'Dibatalkan',
}

const event = computed(() => ticket.value?.event ?? null)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/tickets/${route.params.id}`)
    ticket.value = data.data

    if (data.data.token) {
      qrDataUrl.value = await QRCode.toDataURL(data.data.token, {
        width: 280,
        margin: 1,
        errorCorrectionLevel: 'M',
      })
    }
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Tiket tidak ditemukan.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <section>
    <p v-if="loading" class="muted">Memuat tiket...</p>
    <p v-else-if="!ticket && error" class="form-error">{{ error }}</p>

    <article v-else-if="ticket">
      <div class="page-head">
        <div>
          <p class="eyebrow">E-Ticket</p>
          <h1>Tiket Digital</h1>
        </div>

        <RouterLink to="/tickets" class="secondary-link">Semua tiket</RouterLink>
      </div>

      <div class="ticket-panel">
        <div class="ticket-info">
          <p class="status-badge" :data-status="ticket.status">
            {{ statusLabels[ticket.status] ?? ticket.status }}
          </p>

          <h2>{{ event?.title }}</h2>
          <p class="muted">
            {{ event?.organization?.name }} &middot; {{ event?.location }}
          </p>

          <dl class="detail-list">
            <div>
              <dt>Pemilik Tiket</dt>
              <dd>{{ ticket.owner?.name }}</dd>
            </div>
            <div>
              <dt>Kode Tiket</dt>
              <dd><code class="ticket-code">{{ ticket.ticket_code }}</code></dd>
            </div>
            <div>
              <dt>Mulai Event</dt>
              <dd>{{ formatDateTime(event?.event_start) }}</dd>
            </div>
            <div>
              <dt>Selesai Event</dt>
              <dd>{{ formatDateTime(event?.event_end) }}</dd>
            </div>
          </dl>

          <div v-if="ticket.attendance" class="registration-box registration-confirmed">
            <p class="registration-status">Sudah Check-in</p>
            <p class="muted">
              {{ formatDateTime(ticket.attendance.checked_in_at) }}
            </p>
          </div>

          <p v-else class="muted">
            Tunjukkan QR Code kepada petugas saat memasuki event.
          </p>
        </div>

        <div class="ticket-qr">
          <img v-if="qrDataUrl" :src="qrDataUrl" alt="QR Code tiket" width="280" height="280" />
          <p v-else class="muted">QR Code tidak tersedia.</p>
          <p class="muted">Token QR hanya dapat digunakan untuk satu kali check-in.</p>
        </div>
      </div>

      <p v-if="error" class="form-error">{{ error }}</p>

      <RouterLink v-if="event?.id" :to="`/events/${event.id}`" class="secondary-link">
        Detail event
      </RouterLink>
    </article>
  </section>
</template>

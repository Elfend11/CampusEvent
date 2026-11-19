<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import CountdownTimer from '@/components/CountdownTimer.vue'
import api from '@/services/api'
import {
  formatDateTime,
  paymentStatusLabel,
  registrationStatusLabel,
} from '@/utils/format'

const registrations = ref([])
const loading = ref(false)
const error = ref(null)

const statusFilters = ['', 'PENDING', 'CONFIRMED', 'EXPIRED', 'CANCELLED']
const activeFilter = ref('')

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/registrations', {
      params: activeFilter.value ? { status: activeFilter.value } : {},
    })
    registrations.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat registrasi.'
  } finally {
    loading.value = false
  }
}

function isReservationActive(registration) {
  return (
    registration.status === 'PENDING' &&
    registration.expires_at &&
    new Date(registration.expires_at).getTime() > Date.now()
  )
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Akun Saya</p>
        <h1>Registrasi Saya</h1>
      </div>
    </div>

    <div class="filter-tabs">
      <button
        v-for="status in statusFilters"
        :key="status || 'ALL'"
        type="button"
        :class="{ active: activeFilter === status }"
        @click="activeFilter = status; load()"
      >
        {{ status ? registrationStatusLabel(status) : 'Semua' }}
      </button>
    </div>

    <p v-if="loading" class="muted">Memuat registrasi...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="!registrations.length" class="muted">
      Belum ada registrasi.
      <RouterLink to="/events">Jelajahi event kampus</RouterLink>.
    </p>

    <ul v-else class="card-list">
      <li v-for="registration in registrations" :key="registration.id" class="card registration-card">
        <div class="registration-main">
          <p class="status-badge" :data-status="registration.status">
            {{ registrationStatusLabel(registration.status) }}
          </p>

          <RouterLink :to="`/events/${registration.event?.id}`" class="card-title">
            {{ registration.event?.title }}
          </RouterLink>

          <p class="muted">
            {{ formatDateTime(registration.event?.event_start) }}
            &middot; {{ registration.event?.location }}
          </p>

          <p v-if="isReservationActive(registration)" class="muted">
            Sisa waktu pembayaran:
            <CountdownTimer :expires-at="registration.expires_at" />
          </p>

          <p v-else-if="registration.status === 'PENDING'" class="muted">
            Reservasi kedaluwarsa pada {{ formatDateTime(registration.expires_at) }}.
          </p>

          <p v-if="registration.payment" class="muted">
            Pembayaran: {{ paymentStatusLabel(registration.payment.status) }}
          </p>
        </div>

        <div class="registration-side">
          <template v-if="registration.ticket">
            <p class="muted">Kode tiket</p>
            <code class="ticket-code">{{ registration.ticket.ticket_code }}</code>
          </template>

          <RouterLink :to="`/events/${registration.event?.id}`" class="secondary-link">
            Detail event
          </RouterLink>
        </div>
      </li>
    </ul>
  </section>
</template>

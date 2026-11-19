<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import CountdownTimer from '@/components/CountdownTimer.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { formatDateTime, formatPrice, registrationStatusLabel } from '@/utils/format'

const route = useRoute()
const auth = useAuthStore()

const event = ref(null)
const registration = ref(null)
const ticketToken = ref(null)
const loading = ref(false)
const registering = ref(false)
const error = ref(null)
const message = ref(null)

const reservationActive = computed(
  () =>
    registration.value?.status === 'PENDING' &&
    registration.value?.expires_at &&
    new Date(registration.value.expires_at).getTime() > Date.now(),
)

const registrationClosedReason = computed(() => {
  if (!event.value) {
    return null
  }

  if (event.value.status !== 'PUBLISHED') {
    return 'Event ini belum dibuka untuk pendaftaran.'
  }

  const now = Date.now()

  if (now < new Date(event.value.registration_start).getTime()) {
    return `Pendaftaran dibuka pada ${formatDateTime(event.value.registration_start)}.`
  }

  if (now > new Date(event.value.registration_end).getTime()) {
    return 'Pendaftaran sudah ditutup.'
  }

  if (event.value.available_quota <= 0) {
    return 'Kuota tiket sudah habis.'
  }

  return null
})

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

async function loadRegistration() {
  if (!auth.isAuthenticated) {
    return
  }

  try {
    const { data } = await api.get('/registrations', {
      params: { event_id: route.params.id },
    })
    registration.value = data.data[0] ?? null
  } catch {
    registration.value = null
  }
}

async function register() {
  registering.value = true
  error.value = null
  message.value = null
  ticketToken.value = null

  try {
    const { data } = await api.post('/registrations', { event_id: event.value.id })
    registration.value = data.data
    ticketToken.value = data.ticket_token

    message.value =
      data.data.status === 'CONFIRMED'
        ? 'Pendaftaran berhasil. Tiket Anda sudah diterbitkan.'
        : 'Reservasi berhasil. Selesaikan pembayaran sebelum waktu habis.'

    await load()
  } catch (err) {
    error.value =
      err.response?.data?.message ??
      Object.values(err.response?.data?.errors ?? {}).flat()[0] ??
      'Gagal melakukan pendaftaran.'
  } finally {
    registering.value = false
  }
}

onMounted(async () => {
  await load()
  await loadRegistration()
})
</script>

<template>
  <section>
    <p v-if="loading" class="muted">Memuat event...</p>
    <p v-else-if="!event && error" class="form-error">{{ error }}</p>

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
          <RouterLink :to="{ name: 'login', query: { redirect: route.fullPath } }">
            Masuk
          </RouterLink>
          sebagai mahasiswa untuk mendaftar event ini.
        </p>

        <template v-else>
          <p v-if="error" class="form-error">{{ error }}</p>
          <p v-if="message" class="form-success">{{ message }}</p>

          <div v-if="registration?.status === 'CONFIRMED'" class="registration-box registration-confirmed">
            <p class="registration-status">Terkonfirmasi</p>
            <p class="muted">
              Tiket Anda sudah aktif.
              <template v-if="registration.ticket?.ticket_code">
                Kode tiket: <strong>{{ registration.ticket.ticket_code }}</strong>
              </template>
            </p>

            <p v-if="ticketToken" class="token-note">
              Simpan token berikut sekarang. Token hanya ditampilkan sekali:
              <code>{{ ticketToken }}</code>
            </p>

            <RouterLink to="/registrations" class="secondary-link">
              Lihat registrasi saya
            </RouterLink>
          </div>

          <div v-else-if="reservationActive" class="registration-box registration-pending">
            <p class="registration-status">Reservasi aktif</p>
            <p class="muted">
              Sisa waktu pembayaran:
              <CountdownTimer :expires-at="registration.expires_at" />
            </p>
            <p class="muted">Pembayaran akan tersedia pada tahap berikutnya.</p>
          </div>

          <template v-else>
            <p v-if="registration" class="muted">
              Registrasi sebelumnya berstatus
              <strong>{{ registrationStatusLabel(registration.status) }}</strong>.
              Anda dapat mendaftar kembali selama kuota tersedia.
            </p>

            <p v-if="registrationClosedReason" class="muted">
              {{ registrationClosedReason }}
            </p>

            <button
              v-else
              type="button"
              class="primary-button"
              :disabled="registering"
              @click="register"
            >
              {{ registering ? 'Memproses...' : 'Daftar Sekarang' }}
            </button>
          </template>
        </template>
      </div>
    </article>
  </section>
</template>

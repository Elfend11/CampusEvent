<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import CountdownTimer from '@/components/CountdownTimer.vue'
import api from '@/services/api'
import {
  formatDateTime,
  formatPrice,
  paymentStatusLabel,
  registrationStatusLabel,
} from '@/utils/format'

const route = useRoute()

const payment = ref(null)
const ticketToken = ref(null)
const loading = ref(false)
const processing = ref('')
const error = ref(null)
const notice = ref(null)

const registration = computed(() => payment.value?.registration ?? null)
const event = computed(() => registration.value?.event ?? null)
const ticket = computed(() => registration.value?.ticket ?? null)

const reservationActive = computed(
  () =>
    registration.value?.status === 'PENDING' &&
    registration.value?.expires_at &&
    new Date(registration.value.expires_at).getTime() > Date.now(),
)

const canSimulate = computed(
  () =>
    payment.value?.status !== 'PAID' &&
    registration.value?.status === 'PENDING' &&
    reservationActive.value,
)

const isInactive = computed(() =>
  ['EXPIRED', 'CANCELLED'].includes(registration.value?.status),
)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/payments/${route.params.id}`)
    payment.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Pembayaran tidak ditemukan.'
  } finally {
    loading.value = false
  }
}

async function simulate(success) {
  processing.value = success ? 'success' : 'failure'
  error.value = null
  notice.value = null

  try {
    const { data } = await api.post(`/payments/${route.params.id}/simulate`, { success })
    payment.value = data.data

    if (data.ticket_token) {
      ticketToken.value = data.ticket_token
    }

    notice.value = success
      ? 'Pembayaran simulasi berhasil. Tiket digital Anda sudah diterbitkan.'
      : 'Pembayaran simulasi gagal. Reservasi masih aktif dan dapat dicoba kembali.'
  } catch (err) {
    const payload = err.response?.data

    if (payload?.data) {
      payment.value = payload.data
    }

    error.value = payload?.message ?? 'Pembayaran gagal diproses.'
  } finally {
    processing.value = ''
  }
}

onMounted(load)
</script>

<template>
  <section>
    <p v-if="loading" class="muted">Memuat pembayaran...</p>
    <p v-else-if="!payment && error" class="form-error">{{ error }}</p>

    <article v-else-if="payment">
      <div class="page-head">
        <div>
          <p class="eyebrow">Pembayaran Simulasi</p>
          <h1>Pembayaran Tiket</h1>
        </div>

        <RouterLink to="/registrations" class="secondary-link">
          Registrasi saya
        </RouterLink>
      </div>

      <div class="panel">
        <h2>{{ event?.title }}</h2>
        <p class="muted">
          {{ event?.organization?.name }} &middot; {{ event?.location }}
        </p>

        <dl class="detail-list">
          <div>
            <dt>Nominal</dt>
            <dd>{{ formatPrice(payment.amount) }}</dd>
          </div>
          <div>
            <dt>Status Pembayaran</dt>
            <dd>{{ paymentStatusLabel(payment.status) }}</dd>
          </div>
          <div>
            <dt>Status Registrasi</dt>
            <dd>{{ registrationStatusLabel(registration?.status) }}</dd>
          </div>
          <div>
            <dt>Waktu Pembayaran</dt>
            <dd>{{ payment.paid_at ? formatDateTime(payment.paid_at) : '-' }}</dd>
          </div>
        </dl>

        <p v-if="reservationActive" class="muted">
          Sisa waktu reservasi:
          <CountdownTimer :expires-at="registration.expires_at" />
        </p>

        <p v-else-if="registration?.status === 'PENDING'" class="muted">
          Reservasi kedaluwarsa pada {{ formatDateTime(registration.expires_at) }}.
        </p>

        <p v-if="notice" class="form-success">{{ notice }}</p>
        <p v-if="error" class="form-error">{{ error }}</p>

        <div v-if="payment.status === 'PAID'" class="registration-box registration-confirmed">
          <p class="registration-status">Pembayaran berhasil</p>
          <p class="muted">
            Tiket digital Anda sudah aktif.
            <template v-if="ticket?.ticket_code">
              Kode tiket: <strong>{{ ticket.ticket_code }}</strong>
            </template>
          </p>

          <p v-if="ticketToken" class="token-note">
            Simpan token tiket berikut sekarang. Token hanya ditampilkan sekali:
            <code>{{ ticketToken }}</code>
          </p>

          <RouterLink v-if="ticket?.id" :to="`/tickets/${ticket.id}`" class="secondary-link">
            Lihat tiket digital
          </RouterLink>

          <RouterLink to="/registrations" class="secondary-link">
            Lihat registrasi saya
          </RouterLink>
        </div>

        <div v-else-if="isInactive" class="registration-box">
          <p class="registration-status">
            {{ registrationStatusLabel(registration?.status) }}
          </p>
          <p class="muted">
            Reservasi ini tidak dapat dibayar lagi. Silakan mendaftar ulang
            apabila kuota masih tersedia.
          </p>

          <RouterLink
            v-if="event?.id"
            :to="`/events/${event.id}`"
            class="secondary-link"
          >
            Kembali ke event
          </RouterLink>
        </div>

        <div v-else class="payment-actions">
          <p v-if="payment.status === 'FAILED'" class="muted">
            Pembayaran terakhir gagal. Anda dapat mengulangi pembayaran selama
            reservasi masih aktif.
          </p>

          <button
            type="button"
            class="primary-button"
            :disabled="!canSimulate || processing !== ''"
            @click="simulate(true)"
          >
            {{ processing === 'success' ? 'Memproses...' : 'Bayar (Simulasi Berhasil)' }}
          </button>

          <button
            type="button"
            class="secondary"
            :disabled="!canSimulate || processing !== ''"
            @click="simulate(false)"
          >
            {{ processing === 'failure' ? 'Memproses...' : 'Simulasikan Pembayaran Gagal' }}
          </button>
        </div>
      </div>
    </article>
  </section>
</template>

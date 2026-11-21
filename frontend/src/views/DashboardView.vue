<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

import api from '@/services/api'
import { formatDateTime, formatPrice, registrationStatusLabel } from '@/utils/format'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const report = ref(null)
const loading = ref(false)
const error = ref(null)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/reports/dashboard')
    report.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

const platformStats = computed(() => {
  const stats = report.value?.platform

  if (!stats) {
    return []
  }

  return [
    { label: 'Pengguna', value: stats.users_count },
    { label: 'Organisasi', value: stats.organizations_count },
    { label: 'Event', value: stats.events_count },
    { label: 'Event Terbit', value: stats.published_events_count },
    { label: 'Registrasi', value: stats.registrations_count },
    { label: 'Registrasi Terkonfirmasi', value: stats.confirmed_registrations_count },
    { label: 'Tiket', value: stats.tickets_count },
    { label: 'Tiket Terpakai', value: stats.used_tickets_count },
    { label: 'Check-in', value: stats.check_ins_count },
    { label: 'Total Pembayaran', value: formatPrice(stats.paid_payments_total) },
  ]
})

const organizationStats = computed(() => {
  const stats = report.value?.organizations

  if (!stats) {
    return []
  }

  return [
    { label: 'Organisasi', value: stats.organizations_count },
    { label: 'Event', value: stats.events_count },
    { label: 'Registrasi', value: stats.registrations_count },
    { label: 'Registrasi Terkonfirmasi', value: stats.confirmed_registrations_count },
    { label: 'Tiket', value: stats.tickets_count },
    { label: 'Peserta Hadir', value: stats.check_ins_count },
    { label: 'Total Pembayaran', value: formatPrice(stats.paid_payments_total) },
  ]
})

const studentStats = computed(() => {
  const stats = report.value?.student

  if (!stats) {
    return []
  }

  return [
    { label: 'Event Tersedia', value: stats.available_events_count },
    { label: 'Registrasi', value: stats.registrations_count },
    { label: 'Terkonfirmasi', value: stats.confirmed_registrations_count },
    { label: 'Tiket', value: stats.tickets_count },
    { label: 'Tiket Aktif', value: stats.active_tickets_count },
    { label: 'Riwayat Kehadiran', value: stats.attended_count },
    { label: 'Total Pembayaran', value: formatPrice(stats.paid_payments_total) },
  ]
})
</script>

<template>
  <section class="dashboard">
    <div class="page-head">
      <div>
        <p class="eyebrow">Dashboard</p>
        <h1>Halo, {{ auth.user?.name }}</h1>
        <p class="muted">{{ auth.user?.email }}</p>
      </div>
    </div>

    <p v-if="loading" class="muted">Memuat ringkasan dashboard...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>

    <template v-else-if="report">
      <ul class="role-list">
        <li v-for="role in report.roles" :key="role">{{ role }}</li>
      </ul>

      <div v-if="report.platform" class="panel">
        <h2>Statistik Platform</h2>
        <div class="stat-grid">
          <div v-for="stat in platformStats" :key="stat.label" class="stat-card">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <div v-if="report.organizations" class="panel">
        <h2>Statistik Organisasi</h2>
        <div class="stat-grid">
          <div v-for="stat in organizationStats" :key="stat.label" class="stat-card">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>

        <ul class="card-list compact-list">
          <li v-for="organization in report.organizations.organizations" :key="organization.id" class="card">
            <div>
              <p class="card-title">{{ organization.name }}</p>
              <p class="muted">{{ organization.events_count }} event</p>
            </div>
            <RouterLink :to="`/organizations/${organization.id}`" class="secondary-link">
              Detail
            </RouterLink>
          </li>
        </ul>
      </div>

      <div v-if="report.officer" class="panel">
        <h2>Ringkasan Petugas Check-in</h2>
        <div class="stat-grid">
          <div class="stat-card">
            <span class="stat-value">{{ report.officer.assigned_events_count }}</span>
            <span class="stat-label">Event Ditugaskan</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ report.officer.check_ins_count }}</span>
            <span class="stat-label">Total Check-in</span>
          </div>
        </div>

        <ul class="card-list compact-list">
          <li v-for="event in report.officer.assigned_events" :key="event.id" class="card">
            <div>
              <p class="card-title">{{ event.title }}</p>
              <p class="muted">{{ formatDateTime(event.event_start) }} &middot; {{ event.location }}</p>
            </div>
            <RouterLink to="/check-in" class="secondary-link">Check-in</RouterLink>
          </li>
        </ul>
      </div>

      <div class="panel">
        <h2>Ringkasan Mahasiswa</h2>
        <div class="stat-grid">
          <div v-for="stat in studentStats" :key="stat.label" class="stat-card">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>

        <div v-if="report.student?.upcoming_events?.length" class="dashboard-section">
          <h3>Event Mendatang</h3>
          <ul class="card-list compact-list">
            <li v-for="event in report.student.upcoming_events" :key="event.id" class="card">
              <div>
                <p class="card-title">{{ event.title }}</p>
                <p class="muted">{{ formatDateTime(event.event_start) }} &middot; {{ event.location }}</p>
              </div>
              <RouterLink :to="`/events/${event.id}`" class="secondary-link">Lihat</RouterLink>
            </li>
          </ul>
        </div>

        <div v-if="report.student?.recent_registrations?.length" class="dashboard-section">
          <h3>Registrasi Terbaru</h3>
          <ul class="card-list compact-list">
            <li v-for="registration in report.student.recent_registrations" :key="registration.id" class="card">
              <div>
                <p class="card-title">{{ registration.event?.title }}</p>
                <p class="muted">
                  {{ registrationStatusLabel(registration.status) }}
                  &middot; {{ formatDateTime(registration.created_at) }}
                </p>
              </div>
              <div class="row-actions">
                <RouterLink
                  v-if="registration.payment"
                  :to="`/payments/${registration.payment.id}`"
                  class="secondary-link"
                >
                  Pembayaran
                </RouterLink>
                <RouterLink
                  v-if="registration.ticket"
                  :to="`/tickets/${registration.ticket.id}`"
                  class="secondary-link"
                >
                  Tiket
                </RouterLink>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </section>
</template>

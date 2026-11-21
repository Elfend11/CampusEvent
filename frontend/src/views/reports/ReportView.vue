<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import api from '@/services/api'
import {
  formatDateTime,
  formatPrice,
  paymentStatusLabel,
  registrationStatusLabel,
} from '@/utils/format'

const route = useRoute()

const activeTab = ref('registrations')
const search = ref('')
const status = ref('')
const page = ref(1)
const report = ref(null)
const loading = ref(false)
const error = ref(null)

const summaryStats = computed(() => {
  const summary = report.value?.summary

  if (!summary) {
    return []
  }

  return [
    { label: 'Kapasitas', value: summary.capacity },
    { label: 'Kuota Tersedia', value: summary.available_quota },
    { label: 'Reservasi', value: summary.reserved_count },
    { label: 'Tiket Terjual', value: summary.sold_count },
    { label: 'Registrasi', value: summary.registrations_count },
    { label: 'Terkonfirmasi', value: summary.confirmed_registrations_count },
    { label: 'Menunggu', value: summary.pending_registrations_count },
    { label: 'Tiket', value: summary.tickets_count },
    { label: 'Tiket Terpakai', value: summary.used_tickets_count },
    { label: 'Check-in', value: summary.check_ins_count },
    { label: 'Total Pembayaran', value: formatPrice(summary.paid_payments_total) },
  ]
})

async function load() {
  loading.value = true
  error.value = null

  const params = { page: page.value }

  if (search.value.trim()) {
    params.search = search.value.trim()
  }

  if (activeTab.value === 'registrations' && status.value) {
    params.status = status.value
  }

  const endpoint =
    activeTab.value === 'registrations'
      ? `/reports/events/${route.params.id}`
      : `/reports/attendance/${route.params.id}`

  try {
    const { data } = await api.get(endpoint, { params })
    report.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat laporan event.'
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  page.value = 1
  load()
}

function switchTab(tab) {
  if (activeTab.value === tab) {
    return
  }

  activeTab.value = tab
  page.value = 1
  load()
}

function goToPage(target) {
  page.value = target
  load()
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Laporan</p>
        <h1>{{ report?.event?.title ?? 'Laporan Event' }}</h1>
        <p v-if="report?.event" class="muted">
          {{ report.event.organization?.name }} &middot;
          {{ formatDateTime(report.event.event_start) }}
        </p>
      </div>
      <RouterLink to="/manage/events" class="secondary-link">Kembali</RouterLink>
    </div>

    <p v-if="loading && !report" class="muted">Memuat laporan...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>

    <template v-if="report">
      <div class="panel">
        <h2>Ringkasan Event</h2>
        <div class="stat-grid">
          <div v-for="stat in summaryStats" :key="stat.label" class="stat-card">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <div class="tabs">
        <button
          type="button"
          class="tab-button"
          :class="{ active: activeTab === 'registrations' }"
          @click="switchTab('registrations')"
        >
          Laporan Registrasi
        </button>
        <button
          type="button"
          class="tab-button"
          :class="{ active: activeTab === 'attendance' }"
          @click="switchTab('attendance')"
        >
          Laporan Kehadiran
        </button>
      </div>

      <form class="filter-bar" @submit.prevent="applyFilters">
        <input v-model="search" type="search" placeholder="Cari nama, email, atau kode tiket" />

        <select v-if="activeTab === 'registrations'" v-model="status">
          <option value="">Semua status</option>
          <option value="PENDING">Menunggu Pembayaran</option>
          <option value="CONFIRMED">Terkonfirmasi</option>
          <option value="EXPIRED">Kedaluwarsa</option>
          <option value="CANCELLED">Dibatalkan</option>
        </select>

        <button type="submit">Terapkan</button>
      </form>

      <p v-if="loading" class="muted">Memuat data...</p>
      <p v-else-if="error" class="form-error">{{ error }}</p>

      <template v-else>
        <p v-if="!report.meta?.total" class="muted">Tidak ada data untuk ditampilkan.</p>

        <div v-else class="table-wrap">
          <table v-if="activeTab === 'registrations'" class="data-table">
            <thead>
              <tr>
                <th>Pendaftar</th>
                <th>Status</th>
                <th>Kode Tiket</th>
                <th>Pembayaran</th>
                <th>Waktu Daftar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="registration in report.registrations" :key="registration.id">
                <td>
                  <strong>{{ registration.user?.name }}</strong>
                  <span class="muted block">{{ registration.user?.email }}</span>
                </td>
                <td>{{ registrationStatusLabel(registration.status) }}</td>
                <td>{{ registration.ticket?.ticket_code ?? '-' }}</td>
                <td>
                  {{ registration.payment ? paymentStatusLabel(registration.payment.status) : '-' }}
                </td>
                <td>{{ formatDateTime(registration.created_at) }}</td>
              </tr>
            </tbody>
          </table>

          <table v-else class="data-table">
            <thead>
              <tr>
                <th>Peserta</th>
                <th>Kode Tiket</th>
                <th>Petugas</th>
                <th>Waktu Check-in</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="attendance in report.attendances" :key="attendance.id">
                <td>
                  <strong>{{ attendance.ticket?.owner?.name }}</strong>
                  <span class="muted block">{{ attendance.ticket?.owner?.email }}</span>
                </td>
                <td>{{ attendance.ticket?.ticket_code }}</td>
                <td>{{ attendance.officer?.name ?? '-' }}</td>
                <td>{{ formatDateTime(attendance.checked_in_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="report.meta && report.meta.last_page > 1" class="pagination">
          <button
            type="button"
            class="secondary"
            :disabled="report.meta.current_page <= 1"
            @click="goToPage(report.meta.current_page - 1)"
          >
            Sebelumnya
          </button>
          <span class="muted">
            Halaman {{ report.meta.current_page }} dari {{ report.meta.last_page }}
            ({{ report.meta.total }} data)
          </span>
          <button
            type="button"
            class="secondary"
            :disabled="report.meta.current_page >= report.meta.last_page"
            @click="goToPage(report.meta.current_page + 1)"
          >
            Berikutnya
          </button>
        </div>
      </template>
    </template>
  </section>
</template>

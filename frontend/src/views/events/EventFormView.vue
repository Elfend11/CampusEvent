<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import api from '@/services/api'
import { toDateTimeLocal } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => Boolean(route.params.id))

const organizations = ref([])
const officers = ref([])
const officerUserId = ref('')
const officerError = ref(null)

const form = reactive({
  organization_id: '',
  title: '',
  description: '',
  location: '',
  category: 'Seminar',
  registration_start: '',
  registration_end: '',
  event_start: '',
  event_end: '',
  capacity: 100,
  ticket_price: 0,
})

const loading = ref(false)
const saving = ref(false)
const error = ref(null)

async function loadOrganizations() {
  const { data } = await api.get('/organizations')
  organizations.value = data.data

  if (!isEdit.value && data.data.length === 1) {
    form.organization_id = data.data[0].id
  }
}

async function loadEvent() {
  const { data } = await api.get(`/events/${route.params.id}`)
  const event = data.data

  form.organization_id = event.organization_id
  form.title = event.title
  form.description = event.description
  form.location = event.location
  form.category = event.category
  form.registration_start = toDateTimeLocal(event.registration_start)
  form.registration_end = toDateTimeLocal(event.registration_end)
  form.event_start = toDateTimeLocal(event.event_start)
  form.event_end = toDateTimeLocal(event.event_end)
  form.capacity = event.capacity
  form.ticket_price = event.ticket_price
}

async function loadOfficers() {
  const { data } = await api.get(`/events/${route.params.id}/officers`)
  officers.value = data.data
}

async function load() {
  loading.value = true
  error.value = null

  try {
    await loadOrganizations()

    if (isEdit.value) {
      await Promise.all([loadEvent(), loadOfficers()])
    }
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat data event.'
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  error.value = null

  const payload = { ...form, capacity: Number(form.capacity), ticket_price: Number(form.ticket_price) }

  try {
    if (isEdit.value) {
      delete payload.organization_id
      await api.put(`/events/${route.params.id}`, payload)
      await router.push('/manage/events')
    } else {
      payload.organization_id = Number(payload.organization_id)
      await api.post('/events', payload)
      await router.push('/manage/events')
    }
  } catch (err) {
    error.value =
      Object.values(err.response?.data?.errors ?? {}).flat()[0] ??
      err.response?.data?.message ??
      'Gagal menyimpan event.'
  } finally {
    saving.value = false
  }
}

async function assignOfficer() {
  officerError.value = null

  try {
    await api.post(`/events/${route.params.id}/officers`, {
      user_id: Number(officerUserId.value),
    })
    officerUserId.value = ''
    await loadOfficers()
  } catch (err) {
    officerError.value =
      err.response?.data?.message ??
      Object.values(err.response?.data?.errors ?? {}).flat()[0] ??
      'Gagal menugaskan petugas.'
  }
}

async function removeOfficer(officer) {
  officerError.value = null

  try {
    await api.delete(`/events/${route.params.id}/officers/${officer.id}`)
    await loadOfficers()
  } catch (err) {
    officerError.value = err.response?.data?.message ?? 'Gagal menghapus petugas.'
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1>{{ isEdit ? 'Edit Event' : 'Tambah Event' }}</h1>
      </div>
    </div>

    <p v-if="loading" class="muted">Memuat...</p>

    <form v-else class="stack-form" @submit.prevent="save">
      <div class="panel">
        <h2>Informasi Event</h2>

        <div class="form-grid">
          <div class="field">
            <label for="organization">Organisasi</label>
            <select
              id="organization"
              v-model="form.organization_id"
              :disabled="isEdit || organizations.length === 1"
              required
            >
              <option value="" disabled>Pilih organisasi</option>
              <option v-for="organization in organizations" :key="organization.id" :value="organization.id">
                {{ organization.name }}
              </option>
            </select>
          </div>

          <div class="field">
            <label for="title">Nama Event</label>
            <input id="title" v-model.trim="form.title" required />
          </div>

          <div class="field">
            <label for="location">Lokasi</label>
            <input id="location" v-model.trim="form.location" required />
          </div>

          <div class="field">
            <label for="category">Kategori</label>
            <select id="category" v-model="form.category">
              <option value="Seminar">Seminar</option>
              <option value="Workshop">Workshop</option>
              <option value="Kompetisi">Kompetisi</option>
              <option value="Konser">Konser</option>
            </select>
          </div>

          <div class="field">
            <label for="registration-start">Mulai Pendaftaran</label>
            <input id="registration-start" v-model="form.registration_start" type="datetime-local" required />
          </div>

          <div class="field">
            <label for="registration-end">Selesai Pendaftaran</label>
            <input id="registration-end" v-model="form.registration_end" type="datetime-local" required />
          </div>

          <div class="field">
            <label for="event-start">Mulai Event</label>
            <input id="event-start" v-model="form.event_start" type="datetime-local" required />
          </div>

          <div class="field">
            <label for="event-end">Selesai Event</label>
            <input id="event-end" v-model="form.event_end" type="datetime-local" required />
          </div>

          <div class="field">
            <label for="capacity">Kapasitas</label>
            <input id="capacity" v-model.number="form.capacity" type="number" min="1" required />
          </div>

          <div class="field">
            <label for="price">Harga Tiket (0 = gratis)</label>
            <input id="price" v-model.number="form.ticket_price" type="number" min="0" step="500" required />
          </div>
        </div>

        <div class="field">
          <label for="description">Deskripsi</label>
          <textarea id="description" v-model.trim="form.description" rows="5" required></textarea>
        </div>
      </div>

      <p v-if="error" class="form-error">{{ error }}</p>

      <button type="submit" :disabled="saving">
        {{ saving ? 'Menyimpan...' : 'Simpan Event' }}
      </button>
    </form>

    <div v-if="isEdit && !loading" class="panel">
      <h2>Petugas Check-in</h2>

      <ul class="member-list">
        <li v-for="officer in officers" :key="officer.id">
          <div>
            <strong>{{ officer.name }}</strong>
            <p class="muted">{{ officer.email }}</p>
          </div>
          <button type="button" class="danger-link" @click="removeOfficer(officer)">Hapus</button>
        </li>
      </ul>

      <p v-if="!officers.length" class="muted">Belum ada petugas ditugaskan.</p>

      <form class="inline-form" @submit.prevent="assignOfficer">
        <div class="field">
          <label for="officer-id">ID Pengguna Petugas</label>
          <input id="officer-id" v-model="officerUserId" type="number" min="1" required />
        </div>
        <button type="submit">Tugaskan</button>
      </form>

      <p v-if="officerError" class="form-error">{{ officerError }}</p>
    </div>
  </section>
</template>

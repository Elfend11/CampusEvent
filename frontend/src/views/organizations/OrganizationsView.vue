<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'

import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const organizations = ref([])
const loading = ref(false)
const error = ref(null)

const form = reactive({
  name: '',
  type: 'BEM',
  status: 'ACTIVE',
})
const submitting = ref(false)
const formError = ref(null)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/organizations')
    organizations.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat organisasi.'
  } finally {
    loading.value = false
  }
}

async function createOrganization() {
  submitting.value = true
  formError.value = null

  try {
    await api.post('/organizations', form)
    form.name = ''
    await load()
  } catch (err) {
    if (err.response?.status === 422) {
      formError.value = Object.values(err.response.data.errors ?? {}).flat()[0]
    } else {
      formError.value = err.response?.data?.message ?? 'Gagal membuat organisasi.'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <p class="eyebrow">Manajemen</p>
        <h1>Organisasi</h1>
      </div>
    </div>

    <p v-if="loading" class="muted">Memuat organisasi...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>

    <ul v-else class="card-list">
      <li v-for="organization in organizations" :key="organization.id" class="card">
        <div>
          <RouterLink :to="`/organizations/${organization.id}`" class="card-title">
            {{ organization.name }}
          </RouterLink>
          <p class="muted">
            {{ organization.type }} &middot; {{ organization.status }} &middot;
            {{ organization.members_count }} anggota
          </p>
        </div>
        <RouterLink :to="`/organizations/${organization.id}`" class="secondary-link">
          Kelola
        </RouterLink>
      </li>
    </ul>

    <div v-if="auth.hasRole('SUPER_ADMIN')" class="panel">
      <h2>Tambah Organisasi</h2>

      <form class="inline-form" @submit.prevent="createOrganization">
        <div class="field">
          <label for="org-name">Nama</label>
          <input id="org-name" v-model.trim="form.name" required />
        </div>

        <div class="field">
          <label for="org-type">Tipe</label>
          <select id="org-type" v-model="form.type">
            <option value="BEM">BEM</option>
            <option value="HMPS">HMPS</option>
            <option value="UKM">UKM</option>
            <option value="FAKULTAS">Fakultas</option>
          </select>
        </div>

        <div class="field">
          <label for="org-status">Status</label>
          <select id="org-status" v-model="form.status">
            <option value="ACTIVE">ACTIVE</option>
            <option value="INACTIVE">INACTIVE</option>
          </select>
        </div>

        <button type="submit" :disabled="submitting">
          {{ submitting ? 'Menyimpan...' : 'Tambah' }}
        </button>
      </form>

      <p v-if="formError" class="form-error">{{ formError }}</p>
    </div>
  </section>
</template>

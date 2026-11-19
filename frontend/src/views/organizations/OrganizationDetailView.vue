<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'

import api from '@/services/api'

const route = useRoute()
const organizationId = route.params.id

const organization = ref(null)
const loading = ref(false)
const error = ref(null)

const form = reactive({ name: '', type: '', status: 'ACTIVE' })
const saving = ref(false)
const saveMessage = ref(null)

const memberForm = reactive({ user_id: '', role: 'MEMBER' })
const memberError = ref(null)
const memberSaving = ref(false)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/organizations/${organizationId}`)
    organization.value = data.data
    form.name = data.data.name
    form.type = data.data.type
    form.status = data.data.status
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal memuat organisasi.'
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  saveMessage.value = null

  try {
    const { data } = await api.put(`/organizations/${organizationId}`, form)
    organization.value = data.data
    saveMessage.value = 'Perubahan disimpan.'
  } catch (err) {
    saveMessage.value =
      err.response?.data?.message ??
      Object.values(err.response?.data?.errors ?? {}).flat()[0] ??
      'Gagal menyimpan perubahan.'
  } finally {
    saving.value = false
  }
}

async function addMember() {
  memberSaving.value = true
  memberError.value = null

  try {
    await api.post(`/organizations/${organizationId}/members`, {
      user_id: Number(memberForm.user_id),
      role: memberForm.role,
    })
    memberForm.user_id = ''
    memberForm.role = 'MEMBER'
    await load()
  } catch (err) {
    memberError.value =
      err.response?.data?.message ??
      Object.values(err.response?.data?.errors ?? {}).flat()[0] ??
      'Gagal menambah anggota.'
  } finally {
    memberSaving.value = false
  }
}

async function updateRole(member, role) {
  memberError.value = null

  try {
    await api.put(`/organizations/${organizationId}/members/${member.user.id}`, { role })
    await load()
  } catch (err) {
    memberError.value = err.response?.data?.message ?? 'Gagal mengubah role anggota.'
  }
}

async function removeMember(member) {
  memberError.value = null

  try {
    await api.delete(`/organizations/${organizationId}/members/${member.user.id}`)
    await load()
  } catch (err) {
    memberError.value = err.response?.data?.message ?? 'Gagal menghapus anggota.'
  }
}

onMounted(load)
</script>

<template>
  <section>
    <p v-if="loading" class="muted">Memuat organisasi...</p>
    <p v-else-if="error" class="form-error">{{ error }}</p>

    <template v-else-if="organization">
      <div class="page-head">
        <div>
          <p class="eyebrow">Detail Organisasi</p>
          <h1>{{ organization.name }}</h1>
        </div>
      </div>

      <div class="panel">
        <h2>Data Organisasi</h2>

        <form class="inline-form" @submit.prevent="save">
          <div class="field">
            <label for="detail-name">Nama</label>
            <input id="detail-name" v-model.trim="form.name" required />
          </div>

          <div class="field">
            <label for="detail-type">Tipe</label>
            <input id="detail-type" v-model.trim="form.type" required />
          </div>

          <div class="field">
            <label for="detail-status">Status</label>
            <select id="detail-status" v-model="form.status">
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
            </select>
          </div>

          <button type="submit" :disabled="saving">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </form>

        <p v-if="saveMessage" class="muted">{{ saveMessage }}</p>
      </div>

      <div class="panel">
        <h2>Anggota</h2>

        <ul class="member-list">
          <li v-for="member in organization.members" :key="member.id">
            <div>
              <strong>{{ member.user?.name }}</strong>
              <p class="muted">{{ member.user?.email }}</p>
            </div>

            <div class="member-actions">
              <select :value="member.role" @change="updateRole(member, $event.target.value)">
                <option value="ADMIN">ADMIN</option>
                <option value="MEMBER">MEMBER</option>
              </select>
              <button type="button" class="danger-link" @click="removeMember(member)">
                Hapus
              </button>
            </div>
          </li>
        </ul>

        <p v-if="!organization.members?.length" class="muted">Belum ada anggota.</p>

        <form class="inline-form member-form" @submit.prevent="addMember">
          <div class="field">
            <label for="member-id">ID Pengguna</label>
            <input id="member-id" v-model="memberForm.user_id" type="number" min="1" required />
          </div>

          <div class="field">
            <label for="member-role">Role</label>
            <select id="member-role" v-model="memberForm.role">
              <option value="MEMBER">MEMBER</option>
              <option value="ADMIN">ADMIN</option>
            </select>
          </div>

          <button type="submit" :disabled="memberSaving">
            {{ memberSaving ? 'Menambah...' : 'Tambah Anggota' }}
          </button>
        </form>

        <p v-if="memberError" class="form-error">{{ memberError }}</p>
      </div>
    </template>
  </section>
</template>

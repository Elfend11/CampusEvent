<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import EventCard from '@/components/events/EventCard.vue'
import AppAlert from '@/components/ui/AppAlert.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import api from '@/services/api'
import { categoryOptions } from '@/utils/categories'

const route = useRoute()
const router = useRouter()

const events = ref([])
const meta = ref(null)
const loading = ref(false)
const error = ref(null)

const categories = categoryOptions()

function queryFilters() {
  return {
    search: typeof route.query.search === 'string' ? route.query.search : '',
    category: typeof route.query.category === 'string' ? route.query.category : '',
  }
}

const filters = ref(queryFilters())

const hasFilters = computed(() => Boolean(filters.value.search || filters.value.category))

const resultLabel = computed(() => {
  if (loading.value) {
    return 'Memuat event…'
  }

  if (error.value) {
    return 'Event gagal dimuat.'
  }

  if (!events.value.length) {
    return 'Tidak ada event yang cocok.'
  }

  const total = Number(meta.value?.total)

  if (Number.isFinite(total) && total > events.value.length) {
    return `Menampilkan ${events.value.length} dari ${total} event`
  }

  return `${events.value.length} event ditemukan`
})

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/events', { params: filters.value })
    events.value = Array.isArray(data.data) ? data.data : []
    meta.value = data.meta ?? null
  } catch (err) {
    events.value = []
    meta.value = null
    error.value = err.response?.data?.message ?? 'Gagal memuat event.'
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  const unchanged =
    (route.query.search ?? '') === filters.value.search &&
    (route.query.category ?? '') === filters.value.category

  if (unchanged) {
    load()
    return
  }

  const query = {}

  if (filters.value.search) {
    query.search = filters.value.search
  }

  if (filters.value.category) {
    query.category = filters.value.category
  }

  router.push({ name: 'events', query })
}

function resetFilters() {
  filters.value = { search: '', category: '' }
  applyFilters()
}

watch(
  () => route.query,
  () => {
    filters.value = queryFilters()
    load()
  },
)

onMounted(load)
</script>

<template>
  <section class="events-page" aria-labelledby="events-title">
    <header class="events-head">
      <p class="eyebrow">Jelajahi</p>
      <h1 id="events-title" class="events-head__title">Event kampus</h1>
      <p class="events-head__lead">
        Temukan seminar, workshop, kompetisi, dan konser yang sedang dibuka di kampusmu.
      </p>
    </header>

    <form class="filters" role="search" @submit.prevent="applyFilters">
      <div class="filters__field">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
          <circle cx="11" cy="11" r="6.5" />
          <path d="m16 16 4 4" />
        </svg>
        <label class="sr-only" for="events-search">Cari event atau lokasi</label>
        <input
          id="events-search"
          v-model.trim="filters.search"
          type="search"
          name="search"
          placeholder="Cari event atau lokasi…"
          autocomplete="off"
        />
      </div>

      <div class="filters__select">
        <label class="sr-only" for="events-category">Kategori event</label>
        <select id="events-category" v-model="filters.category" name="category">
          <option value="">Semua kategori</option>
          <option v-for="item in categories" :key="item.value" :value="item.value">
            {{ item.label }}
          </option>
        </select>
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <path d="m6 9 6 6 6-6" />
        </svg>
      </div>

      <AppButton type="submit" class="filters__submit">Cari event</AppButton>

      <button v-if="hasFilters" type="button" class="filters__reset" @click="resetFilters">
        Reset
      </button>
    </form>

    <p class="results" role="status" aria-live="polite">{{ resultLabel }}</p>

    <div v-if="loading" class="events-grid" aria-hidden="true">
      <div v-for="n in 6" :key="n" class="skeleton-card">
        <AppSkeleton height="118px" radius="0" />
        <div class="skeleton-card__body">
          <AppSkeleton width="40%" height="11px" />
          <AppSkeleton width="92%" height="20px" />
          <AppSkeleton width="70%" height="11px" />
          <AppSkeleton width="55%" height="11px" />
        </div>
      </div>
    </div>

    <AppAlert v-else-if="error" variant="error" title="Event belum bisa dimuat">
      {{ error }}
      <template #actions>
        <AppButton size="sm" variant="secondary" @click="load">Coba lagi</AppButton>
      </template>
    </AppAlert>

    <AppEmptyState
      v-else-if="!events.length"
      :title="hasFilters ? 'Belum ada event yang cocok' : 'Belum ada event yang dipublikasikan'"
      :description="
        hasFilters
          ? 'Coba kata kunci lain, ganti kategori, atau reset filter untuk melihat semua event.'
          : 'Event baru akan muncul di sini setelah diterbitkan oleh pengelola.'
      "
    >
      <template #actions>
        <AppButton v-if="hasFilters" variant="secondary" @click="resetFilters">Reset filter</AppButton>
        <AppButton v-else :to="{ name: 'home' }">Kembali ke beranda</AppButton>
      </template>
    </AppEmptyState>

    <div v-else class="events-grid">
      <EventCard v-for="event in events" :key="event.id" :event="event" />
    </div>
  </section>
</template>

<style scoped>
.events-page {
  width: 100%;
}

.events-head {
  max-width: 40rem;
  margin-bottom: 1.5rem;
}

.events-head__title {
  margin: 0;
  font-size: clamp(1.85rem, 3.6vw, 2.5rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tighter);
  line-height: 1.1;
}

.events-head__lead {
  margin: 0.5rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-xs);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.filters:focus-within {
  border-color: var(--brand-300);
  box-shadow: 0 0 0 4px rgb(20 184 166 / 12%);
}

.filters__field {
  display: flex;
  flex: 1 1 15rem;
  min-width: 0;
  align-items: center;
  gap: 0.55rem;
  padding: 0 0.6rem;
  color: var(--muted);
}

.filters__field input {
  width: 100%;
  min-height: 44px;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--text);
  font: inherit;
  font-size: var(--text-sm);
  outline: none;
}

.filters__field input::placeholder {
  color: #94a3b8;
}

.filters__field input::-webkit-search-cancel-button {
  display: none;
}

.filters__select {
  position: relative;
  display: flex;
  flex: 0 1 11rem;
  align-items: center;
  border-left: 1px solid var(--border);
}

.filters__select select {
  width: 100%;
  min-height: 44px;
  padding: 0 2rem 0 0.9rem;
  border: 0;
  background: transparent;
  color: var(--text-soft);
  font: inherit;
  font-size: var(--text-sm);
  font-weight: 500;
  outline: none;
  appearance: none;
  cursor: pointer;
}

.filters__select svg {
  position: absolute;
  right: 0.7rem;
  color: var(--muted);
  pointer-events: none;
}

.filters__submit {
  flex-shrink: 0;
}

.filters__reset {
  min-height: 44px;
  padding: 0 0.7rem;
  border: 0;
  background: transparent;
  color: var(--muted);
  font: inherit;
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
}

.filters__reset:hover {
  color: var(--brand);
}

.results {
  margin: 1rem 0 1.15rem;
  color: var(--muted);
  font-size: var(--text-xs);
  font-weight: 600;
}

.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 255px), 1fr));
  gap: 1rem;
}

.skeleton-card {
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  background: var(--surface);
}

.skeleton-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  padding: 1rem 1.1rem 1.2rem;
}

@media (max-width: 720px) {
  .filters {
    align-items: stretch;
  }

  .filters__select {
    flex: 1 1 100%;
    border-top: 1px solid var(--border);
    border-left: 0;
  }

  .filters__submit {
    flex: 1 1 100%;
  }

  .filters__reset {
    flex: 1 1 100%;
    border-top: 1px solid var(--border);
  }
}
</style>

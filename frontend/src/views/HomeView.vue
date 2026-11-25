<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

import CategoryRail from '@/components/home/CategoryRail.vue'
import EventSpotlight from '@/components/home/EventSpotlight.vue'
import HomeHero from '@/components/home/HomeHero.vue'
import HowItWorks from '@/components/home/HowItWorks.vue'
import JoinCta from '@/components/home/JoinCta.vue'
import EventCard from '@/components/events/EventCard.vue'
import AppAlert from '@/components/ui/AppAlert.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { eventCategories } from '@/utils/categories'

const auth = useAuthStore()
const router = useRouter()

const events = ref([])
const meta = ref(null)
const loading = ref(true)
const error = ref(null)

async function load() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/events')
    events.value = Array.isArray(data.data) ? data.data : []
    meta.value = data.meta ?? null
  } catch (err) {
    events.value = []
    meta.value = null
    error.value = err.response?.data?.message ?? 'Tidak dapat memuat event saat ini.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

function isRegistrationOpen(event) {
  if (!event || (event.status && event.status !== 'PUBLISHED')) {
    return false
  }

  const now = Date.now()
  const start = event.registration_start ? new Date(event.registration_start).getTime() : null
  const end = event.registration_end ? new Date(event.registration_end).getTime() : null

  return (
    (!start || start <= now) &&
    (!end || now <= end) &&
    Number(event.available_quota) > 0
  )
}

function uniqueById(list) {
  const seen = new Set()

  return list.filter((event) => {
    if (!event?.id || seen.has(event.id)) {
      return false
    }

    seen.add(event.id)
    return true
  })
}

const openEvents = computed(() => events.value.filter(isRegistrationOpen))

const upcomingEvents = computed(() => {
  const now = Date.now()
  const future = events.value.filter(
    (event) => event.event_start && new Date(event.event_start).getTime() >= now,
  )

  return future.length ? future : [...events.value]
})

const spotlight = computed(
  () => openEvents.value[0] ?? upcomingEvents.value[0] ?? events.value[0] ?? null,
)

const spotlightOpen = computed(() =>
  Boolean(spotlight.value && openEvents.value.some((event) => event.id === spotlight.value.id)),
)

const sideEvents = computed(() =>
  uniqueById([...openEvents.value, ...upcomingEvents.value])
    .filter((event) => event.id !== spotlight.value?.id)
    .slice(0, 3),
)

const upcomingGrid = computed(() =>
  uniqueById([...openEvents.value, ...upcomingEvents.value, ...events.value])
    .filter((event) => event.id !== spotlight.value?.id)
    .slice(0, 6),
)

const freeEvents = computed(() =>
  uniqueById([...openEvents.value, ...upcomingEvents.value, ...events.value])
    .filter((event) => event.is_free || Number(event.ticket_price) === 0)
    .slice(0, 3),
)

const totalEvents = computed(() => Number(meta.value?.total ?? events.value.length) || 0)

function handleSearch({ search, category }) {
  const query = {}

  if (search) {
    query.search = search
  }

  if (category) {
    query.category = category
  }

  router.push({ name: 'events', query })
}
</script>

<template>
  <div class="home">
    <HomeHero
      :spotlight="spotlight"
      :spotlight-open="spotlightOpen"
      :total-events="totalEvents"
      :categories="eventCategories"
      :loading="loading"
      :error="Boolean(error)"
      @search="handleSearch"
    />

    <CategoryRail :categories="eventCategories" />

    <p v-if="loading" class="sr-only" role="status" aria-live="polite">Memuat event…</p>

    <div v-if="error" class="home__status">
      <AppAlert variant="error" title="Event belum bisa dimuat">
        {{ error }}
        <template #actions>
          <AppButton size="sm" variant="secondary" @click="load">Coba lagi</AppButton>
        </template>
      </AppAlert>
    </div>

    <EventSpotlight
      v-if="loading || (!error && spotlight)"
      :featured="spotlight"
      :side="sideEvents"
      :loading="loading"
      :label="spotlightOpen ? 'Sedang dibuka' : 'Event berikutnya'"
    />

    <template v-if="!loading && !error">
      <section v-if="upcomingGrid.length" class="upcoming" aria-labelledby="upcoming-title">
        <div class="section-head">
          <div>
            <p class="eyebrow">Agenda kampus</p>
            <h2 id="upcoming-title" class="section-head__title">Event mendatang</h2>
            <p class="section-head__sub">Pilih yang paling pas dengan minat dan jadwalmu.</p>
          </div>
          <RouterLink :to="{ name: 'events' }" class="section-head__link">
            Lihat semua
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>
        </div>

        <div class="upcoming__grid">
          <EventCard v-for="event in upcomingGrid" :key="event.id" :event="event" />
        </div>
      </section>

      <section v-if="freeEvents.length" class="free" aria-labelledby="free-title">
        <span class="free__orb" aria-hidden="true"></span>
        <div class="free__inner">
          <div class="free__head">
            <p class="free__eyebrow">Tanpa biaya</p>
            <h2 id="free-title" class="free__title">Gratis, tanpa alasan untuk absen</h2>
            <p class="free__lead">
              Event kampus tanpa biaya pendaftaran. Cukup masuk dengan akunmu, daftar,
              dan tiket digital langsung terbit.
            </p>
            <RouterLink :to="{ name: 'events' }" class="free__link">
              Jelajahi semua event
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </RouterLink>
          </div>

          <div class="free__list">
            <EventCard v-for="event in freeEvents" :key="event.id" :event="event" variant="compact" />
          </div>
        </div>
      </section>

      <div v-if="!events.length" class="home__empty">
        <AppEmptyState
          title="Belum ada event yang dipublikasikan"
          description="Event baru akan muncul di sini setelah diterbitkan oleh pengelola. Coba muat ulang sebentar lagi."
        >
          <template #actions>
            <AppButton variant="secondary" @click="load">Muat ulang</AppButton>
            <AppButton :to="{ name: 'events' }">Buka halaman event</AppButton>
          </template>
        </AppEmptyState>
      </div>
    </template>

    <HowItWorks />
    <JoinCta :authenticated="auth.isAuthenticated" />
  </div>
</template>

<style scoped>
.home {
  width: 100%;
}

.home__status {
  width: 100%;
  max-width: var(--container);
  margin: clamp(1.5rem, 3vw, 2rem) auto 0;
  padding: 0 clamp(1rem, 4vw, 2rem);
}

.upcoming {
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(2.75rem, 5.5vw, 4.5rem) clamp(1rem, 4vw, 2rem) 0;
}

.section-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.section-head__title {
  margin: 0;
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.section-head__sub {
  margin: 0.35rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
}

.section-head__link {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand-700);
  font-size: var(--text-sm);
  font-weight: 700;
}

.section-head__link:hover {
  color: var(--brand);
}

.upcoming__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 255px), 1fr));
  gap: 1rem;
}

.free {
  position: relative;
  overflow: hidden;
  margin-top: clamp(3rem, 6vw, 5rem);
  background:
    radial-gradient(700px 340px at 8% 0%, rgb(45 212 191 / 20%), transparent 65%),
    linear-gradient(135deg, var(--brand-900), var(--brand-800) 60%, var(--brand-700));
}

.free__orb {
  position: absolute;
  right: -6rem;
  bottom: -10rem;
  width: 24rem;
  height: 24rem;
  border: 1px solid rgb(255 255 255 / 12%);
  border-radius: 50%;
  pointer-events: none;
}

.free__inner {
  position: relative;
  display: grid;
  max-width: var(--container);
  grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
  gap: clamp(2rem, 5vw, 4rem);
  align-items: center;
  margin: 0 auto;
  padding: clamp(2.5rem, 5vw, 4rem) clamp(1rem, 4vw, 2rem);
}

.free__eyebrow {
  margin: 0 0 0.6rem;
  color: var(--brand-200);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.free__title {
  margin: 0;
  color: #fff;
  font-size: clamp(1.6rem, 3vw, 2.3rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
  line-height: 1.15;
}

.free__lead {
  max-width: 30rem;
  margin: 0.7rem 0 0;
  color: rgb(204 251 241 / 82%);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.free__link {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  gap: 0.35rem;
  margin-top: 1.2rem;
  color: #fff;
  font-size: var(--text-sm);
  font-weight: 700;
}

.free__link:hover {
  color: var(--brand-200);
}

.free__list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.home__empty {
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(2.5rem, 5vw, 4rem) clamp(1rem, 4vw, 2rem) 0;
}

@media (max-width: 900px) {
  .free__inner {
    grid-template-columns: 1fr;
    gap: 1.75rem;
  }
}

@media (max-width: 420px) {
  .upcoming {
    padding-top: 2.25rem;
  }

  .section-head {
    align-items: flex-start;
  }
}
</style>

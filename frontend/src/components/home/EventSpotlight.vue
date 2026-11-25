<script setup>
import { RouterLink } from 'vue-router'

import EventCard from '@/components/events/EventCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'

defineProps({
  featured: { type: Object, default: null },
  side: { type: Array, default: () => [] },
  label: { type: String, default: 'Sedang dibuka' },
  loading: { type: Boolean, default: false },
})
</script>

<template>
  <section class="spot" aria-labelledby="spotlight-title">
    <div class="spot__head">
      <div>
        <p class="eyebrow">{{ label }}</p>
        <h2 id="spotlight-title" class="spot__title">Jangan sampai terlewat</h2>
        <p class="spot__sub">Pilih event, amankan kursimu, lalu simpan tiketnya di akun.</p>
      </div>
      <RouterLink :to="{ name: 'events' }" class="spot__all">
        Semua event
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </RouterLink>
    </div>

    <div v-if="loading" class="spot__grid" aria-hidden="true">
      <div class="spot__featured-skeleton">
        <AppSkeleton height="300px" radius="var(--radius-xl)" />
      </div>
      <div class="spot__side">
        <AppSkeleton width="40%" height="12px" />
        <AppSkeleton height="82px" radius="var(--radius-lg)" />
        <AppSkeleton height="82px" radius="var(--radius-lg)" />
      </div>
    </div>

    <div v-else-if="featured" class="spot__grid" :class="{ 'spot__grid--single': !side.length }">
      <EventCard :event="featured" variant="featured" />

      <div v-if="side.length" class="spot__side">
        <p class="spot__side-label">Berikutnya</p>
        <EventCard v-for="event in side" :key="event.id" :event="event" variant="compact" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.spot {
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(2.75rem, 5.5vw, 4.5rem) clamp(1rem, 4vw, 2rem) 0;
}

.spot__head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.spot__title {
  margin: 0;
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.spot__sub {
  margin: 0.35rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
}

.spot__all {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand-700);
  font-size: var(--text-sm);
  font-weight: 700;
}

.spot__all:hover {
  color: var(--brand);
}

.spot__grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.85fr);
  gap: 1rem;
  align-items: stretch;
}

.spot__grid--single {
  grid-template-columns: 1fr;
}

.spot__side {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.spot__side-label {
  margin: 0 0 0.15rem;
  color: var(--muted);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.spot__featured-skeleton {
  display: flex;
}

@media (max-width: 900px) {
  .spot__grid {
    grid-template-columns: 1fr;
  }

  .spot__side {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .spot__side-label {
    grid-column: 1 / -1;
  }
}

@media (max-width: 560px) {
  .spot__side {
    grid-template-columns: 1fr;
  }
}
</style>

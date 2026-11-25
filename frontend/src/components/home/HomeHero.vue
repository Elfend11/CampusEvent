<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'

import CategoryIcon from '@/components/events/CategoryIcon.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { categoryMeta } from '@/utils/categories'
import {
  formatEventDay,
  formatEventMonth,
  formatPrice,
} from '@/utils/format'

const props = defineProps({
  spotlight: { type: Object, default: null },
  spotlightOpen: { type: Boolean, default: false },
  totalEvents: { type: Number, default: 0 },
  categories: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
})

const emit = defineEmits(['search'])

const search = ref('')
const category = ref('')

const meta = computed(() => categoryMeta(props.spotlight?.category))

function submit() {
  emit('search', {
    search: search.value.trim(),
    category: category.value,
  })
}
</script>

<template>
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero__backdrop" aria-hidden="true">
      <span class="hero__orb hero__orb--one"></span>
      <span class="hero__orb hero__orb--two"></span>
      <span class="hero__grid"></span>
    </div>

    <div class="hero__inner">
      <div class="hero__copy">
        <p class="hero__pill">
          <span class="hero__pulse" aria-hidden="true"></span>
          Platform event kampus
        </p>

        <h1 id="hero-title" class="hero__title">
          Event kampus terbaik dimulai
          <span class="hero__accent">di sini.</span>
        </h1>

        <p class="hero__lead">
          Temukan seminar, workshop, kompetisi, dan konser di kampusmu. Daftar dalam
          hitungan detik, simpan tiket digital, dan tunjukkan QR saat masuk.
        </p>

        <form class="search" role="search" @submit.prevent="submit">
          <div class="search__field">
            <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
              <circle cx="11" cy="11" r="6.5" />
              <path d="m16 16 4 4" />
            </svg>
            <label class="sr-only" for="hero-search">Cari event atau lokasi</label>
            <input
              id="hero-search"
              v-model.trim="search"
              type="search"
              name="search"
              placeholder="Cari seminar, workshop, atau lokasi…"
              autocomplete="off"
            />
          </div>

          <div class="search__select">
            <label class="sr-only" for="hero-category">Kategori event</label>
            <select id="hero-category" v-model="category" name="category">
              <option value="">Semua kategori</option>
              <option v-for="item in categories" :key="item.value" :value="item.value">
                {{ item.label }}
              </option>
            </select>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <path d="m6 9 6 6 6-6" />
            </svg>
          </div>

          <button type="submit" class="search__submit">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <circle cx="11" cy="11" r="6.5" />
              <path d="m16 16 4 4" />
            </svg>
            Cari event
          </button>
        </form>

        <div class="hero__facts">
          <span v-if="error">Agenda kampus dalam satu tempat</span>
          <span v-else-if="totalEvents > 0">
            <strong>{{ totalEvents }}</strong> event dipublikasikan
          </span>
          <span v-else>Event baru segera hadir</span>
          <span class="hero__dot" aria-hidden="true"></span>
          <span>Tiket digital instan</span>
          <span class="hero__dot" aria-hidden="true"></span>
          <span>Tanpa antre di lokasi</span>
        </div>
      </div>

      <div class="hero__visual">
        <span class="hero__visual-label">
          {{ spotlightOpen ? 'Pendaftaran sedang dibuka' : 'Event berikutnya' }}
        </span>

        <div class="poster-wrap">
          <RouterLink
            v-if="spotlight"
            :to="`/events/${spotlight.id}`"
            class="poster"
            :style="{ '--cat-color': meta.color, '--cat-soft': meta.soft }"
          >
            <div class="poster__cover" aria-hidden="true">
              <span class="poster__pattern"></span>
              <span class="poster__badge">{{ meta.label }}</span>
              <span class="poster__icon">
                <CategoryIcon :name="meta.icon" :size="76" />
              </span>
              <span class="poster__date">
                <strong>{{ formatEventDay(spotlight.event_start) }}</strong>
                <span>{{ formatEventMonth(spotlight.event_start) }}</span>
              </span>
            </div>

            <div class="poster__body">
              <p class="poster__org">{{ spotlight.organization?.name ?? 'CampusEvent' }}</p>
              <h2 class="poster__title">{{ spotlight.title }}</h2>
              <p class="poster__meta">
                {{ spotlight.location || 'Lokasi menyusul' }}
              </p>
              <div class="poster__foot">
                <span class="poster__price">{{ formatPrice(spotlight.ticket_price) }}</span>
                <span class="poster__cta">
                  Lihat detail
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                  </svg>
                </span>
              </div>
            </div>
          </RouterLink>

          <div v-else-if="loading" class="poster poster--skeleton" aria-hidden="true">
            <AppSkeleton height="160px" radius="0" />
            <div class="poster__body">
              <AppSkeleton width="35%" height="12px" />
              <AppSkeleton width="92%" height="22px" />
              <AppSkeleton width="60%" height="14px" />
              <div class="poster__foot">
                <AppSkeleton width="4rem" height="14px" />
                <AppSkeleton width="5rem" height="14px" />
              </div>
            </div>
          </div>

          <div v-else-if="error" class="poster poster--empty">
            <span class="poster__icon poster__icon--empty" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                <circle cx="12" cy="12" r="8.5" />
                <path d="M12 8v5" />
                <circle cx="12" cy="16.4" r="0.9" fill="currentColor" stroke="none" />
              </svg>
            </span>
            <h2>Event belum bisa dimuat</h2>
            <p>Koneksi ke server sedang bermasalah. Coba muat ulang halaman atau periksa status layanan.</p>
            <RouterLink :to="{ name: 'status' }" class="poster__empty-link">
              Cek status layanan
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </RouterLink>
          </div>

          <div v-else class="poster poster--empty">
            <span class="poster__icon poster__icon--empty" aria-hidden="true">
              <CategoryIcon name="spark" :size="54" />
            </span>
            <h2>Event berikutnya sedang disiapkan</h2>
            <p>Belum ada event yang dipublikasikan saat ini. Cek kembali sebentar lagi.</p>
            <RouterLink :to="{ name: 'events' }" class="poster__empty-link">
              Jelajahi semua event
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </RouterLink>
          </div>

          <div v-if="spotlight" class="float-card">
            <span class="float-card__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-1.7a2.8 2.8 0 0 0 0-5.6z" />
                <path d="M9 9.5v5" stroke-dasharray="1 2.4" />
              </svg>
            </span>
            <span class="float-card__text">
              <strong>Tiket digital</strong>
              <span>Langsung tersimpan di akunmu</span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero {
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid var(--border);
  background:
    radial-gradient(1200px 600px at 12% -10%, #e6fffb 0%, transparent 55%),
    linear-gradient(180deg, #f7fdfc 0%, var(--bg) 78%);
}

.hero__backdrop {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.hero__orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(10px);
}

.hero__orb--one {
  top: -14rem;
  right: -8rem;
  width: 32rem;
  height: 32rem;
  background: radial-gradient(circle, rgb(20 184 166 / 16%), transparent 65%);
}

.hero__orb--two {
  bottom: -18rem;
  left: -10rem;
  width: 30rem;
  height: 30rem;
  background: radial-gradient(circle, rgb(15 118 110 / 10%), transparent 68%);
}

.hero__grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(to right, rgb(15 118 110 / 5%) 1px, transparent 1px),
    linear-gradient(to bottom, rgb(15 118 110 / 5%) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: radial-gradient(ellipse at 60% 0%, #000 0%, transparent 72%);
}

.hero__inner {
  position: relative;
  display: grid;
  max-width: var(--container);
  grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
  gap: clamp(2rem, 5vw, 4rem);
  align-items: center;
  margin: 0 auto;
  padding: clamp(2.5rem, 6vw, 5rem) clamp(1rem, 4vw, 2rem) clamp(3rem, 7vw, 5.5rem);
}

.hero__pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1.15rem;
  padding: 0.35rem 0.8rem 0.35rem 0.55rem;
  border: 1px solid var(--brand-200);
  border-radius: var(--radius-full);
  background: rgb(255 255 255 / 70%);
  color: var(--brand-700);
  font-size: var(--text-xs);
  font-weight: 700;
  letter-spacing: 0.02em;
}

.hero__pulse {
  position: relative;
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
  background: var(--brand-500);
}

.hero__pulse::after {
  position: absolute;
  inset: -3px;
  border: 1px solid var(--brand-400);
  border-radius: 50%;
  content: '';
  opacity: 0.55;
  animation: hero-pulse 2.4s ease-out infinite;
}

@keyframes hero-pulse {
  from {
    opacity: 0.55;
    transform: scale(0.8);
  }

  to {
    opacity: 0;
    transform: scale(1.9);
  }
}

.hero__title {
  max-width: 13ch;
  margin: 0;
  font-size: clamp(2.25rem, 4.6vw, 3.6rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tighter);
  line-height: 1.06;
}

.hero__accent {
  background: linear-gradient(transparent 64%, var(--brand-200) 64%);
  color: var(--brand-700);
}

.hero__lead {
  max-width: 34rem;
  margin: 1.15rem 0 0;
  color: var(--text-soft);
  font-size: clamp(0.95rem, 1.4vw, 1.05rem);
  line-height: 1.7;
}

.search {
  display: grid;
  max-width: 40rem;
  grid-template-columns: minmax(0, 1fr) 12rem auto;
  gap: 0.35rem;
  align-items: center;
  margin-top: 1.9rem;
  padding: 0.4rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  background: var(--surface);
  box-shadow: var(--shadow-md);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.search:focus-within {
  border-color: var(--brand-300);
  box-shadow: 0 0 0 4px rgb(20 184 166 / 14%), var(--shadow-md);
}

.search__field {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 0.55rem;
  padding: 0 0.55rem;
  color: var(--muted);
}

.search__field input {
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

.search__field input::placeholder {
  color: #94a3b8;
}

.search__field input::-webkit-search-cancel-button {
  display: none;
}

.search__select {
  position: relative;
  display: flex;
  align-items: center;
  border-left: 1px solid var(--border);
}

.search__select select {
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

.search__select svg {
  position: absolute;
  right: 0.75rem;
  color: var(--muted);
  pointer-events: none;
}

.search__submit {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0 1.15rem;
  border: 0;
  border-radius: var(--radius-lg);
  background: var(--brand);
  color: #fff;
  font: inherit;
  font-size: var(--text-sm);
  font-weight: 700;
  white-space: nowrap;
  cursor: pointer;
  transition: background-color var(--transition-fast);
}

.search__submit:hover {
  background: var(--brand-dark);
}

.hero__facts {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.55rem;
  margin-top: 1.15rem;
  color: var(--muted);
  font-size: var(--text-xs);
}

.hero__facts strong {
  color: var(--text);
  font-weight: 700;
}

.hero__dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: var(--border-strong);
}

.hero__visual {
  position: relative;
  padding-top: 0.5rem;
}

.hero__visual-label {
  display: inline-flex;
  margin-bottom: 0.6rem;
  padding: 0.25rem 0.65rem;
  border-radius: var(--radius-full);
  background: var(--brand-700);
  color: #fff;
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.poster-wrap {
  position: relative;
  padding: 0 0 1.4rem 1.4rem;
}

.poster {
  display: flex;
  overflow: hidden;
  flex-direction: column;
  border: 1px solid var(--border);
  border-radius: var(--radius-2xl);
  background: var(--surface);
  color: var(--text);
  box-shadow: var(--shadow-lg);
  transform: rotate(-1.1deg);
  transition: transform var(--transition-base), box-shadow var(--transition-base);
}

.poster:hover {
  box-shadow: 0 34px 70px -30px rgb(15 23 42 / 38%);
  transform: rotate(0deg) translateY(-2px);
}

.poster__cover {
  position: relative;
  display: flex;
  min-height: 168px;
  align-items: flex-start;
  justify-content: space-between;
  overflow: hidden;
  padding: 1.1rem;
  background:
    radial-gradient(circle at 20% 10%, rgb(255 255 255 / 55%), transparent 45%),
    var(--cat-soft);
  color: var(--cat-color);
}

.poster__pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(currentColor 1.2px, transparent 1.2px);
  background-size: 18px 18px;
  opacity: 0.16;
}

.poster__badge {
  position: relative;
  z-index: 1;
  padding: 0.25rem 0.65rem;
  border: 1px solid currentColor;
  border-radius: var(--radius-full);
  background: rgb(255 255 255 / 72%);
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.poster__icon {
  position: absolute;
  right: 1.2rem;
  bottom: 0.4rem;
  opacity: 0.22;
}

.poster__date {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem 0.6rem;
  border-radius: var(--radius-md);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
  color: var(--text);
  line-height: 1;
}

.poster__date strong {
  font-size: 1.55rem;
  font-weight: 800;
}

.poster__date span {
  margin-top: 0.2rem;
  color: var(--muted);
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.poster__body {
  display: flex;
  flex-direction: column;
  padding: 1.15rem 1.3rem 1.3rem;
}

.poster__org {
  margin: 0 0 0.25rem;
  overflow: hidden;
  color: var(--muted);
  font-size: 0.72rem;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.poster__title {
  display: -webkit-box;
  overflow: hidden;
  margin: 0;
  font-size: clamp(1.15rem, 2vw, 1.45rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
  line-height: 1.25;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.poster__meta {
  margin: 0.45rem 0 0;
  overflow: hidden;
  color: var(--muted);
  font-size: var(--text-xs);
  text-overflow: ellipsis;
  white-space: nowrap;
}

.poster__foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 0.85rem;
  border-top: 1px solid var(--border);
}

.poster__price {
  color: var(--brand-700);
  font-size: var(--text-sm);
  font-weight: 800;
}

.poster__cta {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand-700);
  font-size: var(--text-xs);
  font-weight: 700;
}

.poster--skeleton {
  transform: none;
}

.poster--skeleton .poster__body {
  gap: 0.6rem;
}

.poster--skeleton .poster__foot {
  justify-content: space-between;
  border-top: 0;
}

.poster--empty {
  align-items: flex-start;
  padding: 1.6rem;
  transform: none;
}

.poster--empty h2 {
  margin: 0.8rem 0 0.4rem;
  font-size: var(--text-xl);
  letter-spacing: var(--tracking-tight);
}

.poster--empty p {
  margin: 0 0 1rem;
  color: var(--muted);
  font-size: var(--text-sm);
}

.poster__icon--empty {
  position: static;
  display: grid;
  width: 4rem;
  height: 4rem;
  place-items: center;
  border-radius: var(--radius-lg);
  background: var(--brand-50);
  color: var(--brand);
  opacity: 1;
}

.poster__empty-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand-700);
  font-size: var(--text-sm);
  font-weight: 700;
}

.float-card {
  position: absolute;
  bottom: 0;
  left: 0;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.65rem 0.9rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-md);
}

.float-card__icon {
  display: grid;
  width: 2.2rem;
  height: 2.2rem;
  place-items: center;
  border-radius: var(--radius-md);
  background: var(--brand-50);
  color: var(--brand);
}

.float-card__text {
  display: flex;
  flex-direction: column;
  line-height: 1.3;
}

.float-card__text strong {
  font-size: 0.76rem;
  font-weight: 700;
}

.float-card__text span {
  color: var(--muted);
  font-size: 0.68rem;
}

@media (max-width: 980px) {
  .hero__inner {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }

  .hero__title {
    max-width: 18ch;
  }

  .hero__visual {
    max-width: 30rem;
  }

  .poster {
    transform: none;
  }
}

@media (max-width: 640px) {
  .search {
    grid-template-columns: 1fr;
    gap: 0.15rem;
    border-radius: var(--radius-lg);
    padding: 0.35rem;
  }

  .search__field {
    padding: 0 0.7rem;
  }

  .search__select {
    border-top: 1px solid var(--border);
    border-left: 0;
  }

  .search__select select {
    padding-left: 0.7rem;
  }

  .search__submit {
    min-height: 46px;
    border-radius: var(--radius-md);
  }

  .hero__facts {
    row-gap: 0.3rem;
  }

  .poster-wrap {
    padding: 0;
  }

  .float-card {
    display: none;
  }

  .poster__cover {
    min-height: 142px;
  }
}

@media (max-width: 390px) {
  .hero__inner {
    padding-top: 2rem;
  }

  .hero__title {
    font-size: 2.1rem;
  }

  .hero__lead {
    font-size: 0.92rem;
  }

  .hero__dot {
    display: none;
  }
}
</style>

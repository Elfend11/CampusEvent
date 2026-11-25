<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

import CategoryIcon from '@/components/events/CategoryIcon.vue'
import {
  formatEventDate,
  formatEventDay,
  formatEventMonth,
  formatEventTime,
  formatPrice,
} from '@/utils/format'
import { categoryMeta } from '@/utils/categories'

const props = defineProps({
  event: { type: Object, required: true },
  variant: { type: String, default: 'default' },
})

const meta = computed(() => categoryMeta(props.event?.category))

const isFree = computed(
  () => props.event?.is_free ?? Number(props.event?.ticket_price) === 0,
)

const capacity = computed(() => Number(props.event?.capacity) || 0)
const available = computed(() => Number(props.event?.available_quota) || 0)

const quotaPercent = computed(() => {
  if (!capacity.value) {
    return 0
  }

  const used = Math.min(capacity.value, Math.max(0, capacity.value - available.value))

  return Math.round((used / capacity.value) * 100)
})

const isLowQuota = computed(
  () => available.value > 0 && capacity.value > 0 && available.value / capacity.value <= 0.15,
)

const quotaLabel = computed(() =>
  available.value > 0 ? `${available.value} tiket tersisa` : 'Kuota penuh',
)
</script>

<template>
  <RouterLink
    :to="`/events/${event.id}`"
    class="ecard"
    :class="`ecard--${variant}`"
    :style="{ '--cat-color': meta.color, '--cat-soft': meta.soft }"
  >
    <div class="ecard__visual" aria-hidden="true">
      <span class="ecard__pattern"></span>
      <span class="ecard__category">{{ meta.label }}</span>
      <span class="ecard__glyph">
        <CategoryIcon :name="meta.icon" :size="variant === 'featured' ? 64 : 40" />
      </span>
      <span class="ecard__date">
        <strong>{{ formatEventDay(event.event_start) }}</strong>
        <span>{{ formatEventMonth(event.event_start) }}</span>
      </span>
    </div>

    <div class="ecard__body">
      <p class="ecard__org">{{ event.organization?.name ?? 'CampusEvent' }}</p>
      <h3 class="ecard__title">{{ event.title }}</h3>

      <ul class="ecard__meta">
        <li>
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
            <rect x="4" y="5.5" width="16" height="14" rx="3" />
            <path d="M8 3.5v4M16 3.5v4M4 10h16" />
          </svg>
          {{ formatEventDate(event.event_start) }}
        </li>
        <li>
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
            <circle cx="12" cy="12" r="8.5" />
            <path d="M12 7.5V12l3 1.8" />
          </svg>
          {{ formatEventTime(event.event_start) }}
        </li>
        <li class="ecard__meta-location">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 21s6.5-5.4 6.5-10a6.5 6.5 0 1 0-13 0C5.5 15.6 12 21 12 21z" />
            <circle cx="12" cy="11" r="2.4" />
          </svg>
          <span>{{ event.location || 'Lokasi menyusul' }}</span>
        </li>
      </ul>

      <div class="ecard__foot">
        <span class="ecard__price" :class="{ 'is-free': isFree }">
          {{ formatPrice(event.ticket_price) }}
        </span>
        <span class="ecard__quota" :class="{ 'is-low': isLowQuota }">{{ quotaLabel }}</span>
      </div>

      <span v-if="variant !== 'compact' && capacity" class="ecard__progress">
        <span :style="{ width: `${quotaPercent}%` }"></span>
      </span>
    </div>
  </RouterLink>
</template>

<style scoped>
.ecard {
  position: relative;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  background: var(--surface);
  color: var(--text);
  box-shadow: var(--shadow-xs);
  transition:
    transform var(--transition-base),
    box-shadow var(--transition-base),
    border-color var(--transition-base);
}

.ecard:hover {
  transform: translateY(-4px);
  border-color: var(--brand-200);
  box-shadow: var(--shadow-md);
}

.ecard__visual {
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  min-height: 118px;
  padding: 1rem;
  overflow: hidden;
  background:
    radial-gradient(circle at 18% 18%, rgb(255 255 255 / 45%), transparent 42%),
    var(--cat-soft);
  color: var(--cat-color);
}

.ecard__pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(currentColor 1px, transparent 1px);
  background-size: 16px 16px;
  opacity: 0.16;
}

.ecard__category {
  position: relative;
  z-index: 1;
  padding: 0.22rem 0.6rem;
  border: 1px solid currentColor;
  border-radius: var(--radius-full);
  background: rgb(255 255 255 / 70%);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.ecard__glyph {
  position: absolute;
  right: 0.9rem;
  bottom: -0.4rem;
  opacity: 0.24;
}

.ecard__date {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 3.1rem;
  padding: 0.35rem 0.45rem;
  border-radius: var(--radius-md);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
  color: var(--text);
  line-height: 1;
}

.ecard__date strong {
  font-size: 1.15rem;
  font-weight: 800;
}

.ecard__date span {
  margin-top: 0.15rem;
  color: var(--muted);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.ecard__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  padding: 1rem 1.1rem 1.15rem;
}

.ecard__org {
  margin: 0 0 0.2rem;
  overflow: hidden;
  color: var(--muted);
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.ecard__title {
  display: -webkit-box;
  overflow: hidden;
  margin: 0;
  font-size: var(--text-lg);
  font-weight: 700;
  letter-spacing: var(--tracking-tight);
  line-height: var(--leading-snug);
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.ecard__meta {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  margin: 0.7rem 0 0;
  padding: 0;
  color: var(--muted);
  font-size: 0.78rem;
  list-style: none;
}

.ecard__meta li {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  min-width: 0;
}

.ecard__meta svg {
  flex-shrink: 0;
  color: var(--brand);
}

.ecard__meta-location span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.ecard__foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-top: auto;
  padding-top: 0.9rem;
}

.ecard__price {
  color: var(--text);
  font-size: 0.9rem;
  font-weight: 700;
}

.ecard__price.is-free {
  color: var(--brand);
}

.ecard__quota {
  color: var(--muted);
  font-size: 0.72rem;
  font-weight: 600;
}

.ecard__quota.is-low {
  color: var(--warn);
}

.ecard__progress {
  display: block;
  height: 4px;
  margin-top: 0.7rem;
  overflow: hidden;
  border-radius: var(--radius-full);
  background: var(--bg-subtle);
}

.ecard__progress > span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--cat-color);
  opacity: 0.7;
}

/* Featured: editorial split layout */
.ecard--featured {
  flex-direction: row;
  min-height: 300px;
}

.ecard--featured .ecard__visual {
  flex: 0 0 40%;
  min-height: 100%;
  padding: 1.4rem;
  border-radius: 0;
}

.ecard--featured .ecard__body {
  justify-content: center;
  padding: clamp(1.25rem, 3vw, 2rem);
}

.ecard--featured .ecard__title {
  font-size: clamp(1.35rem, 2.4vw, 1.85rem);
  -webkit-line-clamp: 3;
}

.ecard--featured .ecard__date {
  align-self: flex-end;
  min-width: 3.6rem;
  padding: 0.45rem 0.55rem;
}

.ecard--featured .ecard__date strong {
  font-size: 1.4rem;
}

.ecard--featured .ecard__category {
  font-size: 0.7rem;
}

/* Compact: horizontal list item */
.ecard--compact {
  flex-direction: row;
  align-items: stretch;
  border-radius: var(--radius-lg);
}

.ecard--compact .ecard__visual {
  flex: 0 0 4.6rem;
  align-items: center;
  justify-content: center;
  min-height: 100%;
  padding: 0.5rem;
}

.ecard--compact .ecard__category,
.ecard--compact .ecard__glyph {
  display: none;
}

.ecard--compact .ecard__date {
  background: transparent;
  box-shadow: none;
}

.ecard--compact .ecard__date strong {
  font-size: 1.4rem;
}

.ecard--compact .ecard__body {
  padding: 0.8rem 1rem;
}

.ecard--compact .ecard__title {
  font-size: var(--text-sm);
  -webkit-line-clamp: 2;
}

.ecard--compact .ecard__meta {
  display: none;
}

.ecard--compact .ecard__foot {
  padding-top: 0.5rem;
}

.ecard--compact .ecard__quota {
  display: none;
}

@media (max-width: 860px) {
  .ecard--featured {
    flex-direction: column;
    min-height: 0;
  }

  .ecard--featured .ecard__visual {
    flex: none;
    min-height: 150px;
  }

  .ecard--featured .ecard__date {
    align-self: flex-start;
  }
}

@media (max-width: 400px) {
  .ecard__visual {
    min-height: 104px;
  }

  .ecard__body {
    padding: 0.9rem 1rem 1rem;
  }

  .ecard__foot {
    flex-wrap: wrap;
    gap: 0.35rem;
  }
}
</style>

<script setup>
import { RouterLink } from 'vue-router'

import CategoryIcon from '@/components/events/CategoryIcon.vue'

defineProps({
  categories: { type: Array, default: () => [] },
})
</script>

<template>
  <section class="categories" aria-labelledby="categories-title">
    <div class="categories__head">
      <div>
        <p class="eyebrow">Kategori</p>
        <h2 id="categories-title" class="categories__title">Pilih suasana eventmu</h2>
      </div>
      <RouterLink :to="{ name: 'events' }" class="categories__all">
        Semua event
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </RouterLink>
    </div>

    <div class="categories__grid">
      <RouterLink
        v-for="(category, index) in categories"
        :key="category.value"
        :to="{ name: 'events', query: { category: category.value } }"
        class="cat"
        :class="{ 'cat--offset': index % 2 === 1 }"
        :style="{ '--cat': category.color, '--cat-soft': category.soft }"
      >
        <span class="cat__icon">
          <CategoryIcon :name="category.icon" :size="22" />
        </span>
        <span class="cat__label">{{ category.label }}</span>
        <span class="cat__desc">{{ category.description }}</span>
        <span class="cat__arrow" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 17 17 7M9 7h8v8" />
          </svg>
        </span>
        <span class="cat__ghost" aria-hidden="true">
          <CategoryIcon :name="category.icon" :size="96" />
        </span>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
.categories {
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(2.5rem, 5vw, 4rem) clamp(1rem, 4vw, 2rem) 0;
}

.categories__head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.categories__title {
  margin: 0;
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.categories__all {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand-700);
  font-size: var(--text-sm);
  font-weight: 700;
}

.categories__all:hover {
  color: var(--brand);
}

.categories__grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.9rem;
  align-items: start;
}

.cat {
  position: relative;
  display: flex;
  min-height: 188px;
  flex-direction: column;
  overflow: hidden;
  padding: 1.15rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  background: var(--surface);
  transition:
    transform var(--transition-base),
    border-color var(--transition-base),
    box-shadow var(--transition-base);
}

.cat--offset {
  margin-top: 1.6rem;
}

.cat:hover {
  transform: translateY(-4px);
  border-color: var(--cat);
  box-shadow: var(--shadow-md);
}

.cat__icon {
  display: grid;
  width: 2.6rem;
  height: 2.6rem;
  place-items: center;
  margin-bottom: 1.1rem;
  border-radius: var(--radius-md);
  background: var(--cat-soft);
  color: var(--cat);
}

.cat__label {
  position: relative;
  z-index: 1;
  font-size: var(--text-base);
  font-weight: 700;
  letter-spacing: var(--tracking-tight);
}

.cat__desc {
  position: relative;
  z-index: 1;
  margin-top: 0.25rem;
  color: var(--muted);
  font-size: 0.78rem;
  line-height: 1.5;
}

.cat__arrow {
  position: absolute;
  top: 1.15rem;
  right: 1.1rem;
  display: grid;
  width: 1.9rem;
  height: 1.9rem;
  place-items: center;
  border: 1px solid var(--border);
  border-radius: 50%;
  color: var(--muted);
  transition: background-color var(--transition-fast), color var(--transition-fast), border-color var(--transition-fast);
}

.cat:hover .cat__arrow {
  border-color: var(--cat);
  background: var(--cat);
  color: #fff;
}

.cat__ghost {
  position: absolute;
  right: -1rem;
  bottom: -1.4rem;
  color: var(--cat);
  opacity: 0.07;
  transition: opacity var(--transition-base), transform var(--transition-base);
}

.cat:hover .cat__ghost {
  opacity: 0.12;
  transform: rotate(-6deg) scale(1.05);
}

@media (max-width: 900px) {
  .categories__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .cat--offset {
    margin-top: 0;
  }
}

@media (max-width: 420px) {
  .categories__grid {
    grid-template-columns: 1fr;
  }

  .cat {
    min-height: 0;
    padding: 1rem;
  }

  .cat__ghost {
    display: none;
  }

  .cat__desc {
    max-width: 26ch;
  }
}
</style>

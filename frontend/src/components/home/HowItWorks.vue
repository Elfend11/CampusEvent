<script setup>
const steps = [
  {
    number: '01',
    title: 'Temukan event',
    description: 'Jelajahi event kampus berdasarkan kategori, kata kunci, atau lokasi.',
    icon: 'search',
  },
  {
    number: '02',
    title: 'Daftar & amankan kursi',
    description: 'Isi pendaftaran sekali klik. Event berbayar punya waktu reservasi yang jelas.',
    icon: 'ticket',
  },
  {
    number: '03',
    title: 'Tunjukkan QR',
    description: 'Tiket digital tersimpan di akun. Tunjukkan QR ke petugas saat masuk.',
    icon: 'qr',
  },
]
</script>

<template>
  <section class="how" aria-labelledby="how-title">
    <div class="how__inner">
      <div class="how__head">
        <p class="eyebrow">Cara kerja</p>
        <h2 id="how-title" class="how__title">Tiga langkah, satu tiket digital</h2>
        <p class="how__lead">
          Dari menemukan event sampai masuk ke lokasi, semuanya tetap di satu akun
          kampus.
        </p>
      </div>

      <ol class="how__steps">
        <li v-for="step in steps" :key="step.number" class="step">
          <span class="step__number" aria-hidden="true">{{ step.number }}</span>
          <span class="step__icon" aria-hidden="true">
            <svg v-if="step.icon === 'search'" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
              <circle cx="11" cy="11" r="6.5" />
              <path d="m16 16 4 4" />
            </svg>
            <svg v-else-if="step.icon === 'ticket'" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-1.7a2.8 2.8 0 0 0 0-5.6z" />
              <path d="M9 9.5v5" stroke-dasharray="1 2.4" />
            </svg>
            <svg v-else viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="4" width="6" height="6" rx="1" />
              <rect x="14" y="4" width="6" height="6" rx="1" />
              <rect x="4" y="14" width="6" height="6" rx="1" />
              <path d="M14 14h2v2h-2zM18 18h2v2h-2zM14 20h2M20 14h-2" />
            </svg>
          </span>
          <h3 class="step__title">{{ step.title }}</h3>
          <p class="step__desc">{{ step.description }}</p>
        </li>
      </ol>
    </div>
  </section>
</template>

<style scoped>
.how {
  margin-top: clamp(3rem, 6vw, 5rem);
  border-block: 1px solid var(--border);
  background:
    radial-gradient(800px 300px at 85% 0%, rgb(20 184 166 / 8%), transparent 70%),
    var(--surface);
}

.how__inner {
  display: grid;
  max-width: var(--container);
  grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.4fr);
  gap: clamp(2rem, 5vw, 4rem);
  align-items: center;
  margin: 0 auto;
  padding: clamp(2.5rem, 5vw, 4rem) clamp(1rem, 4vw, 2rem);
}

.how__title {
  margin: 0;
  font-size: clamp(1.6rem, 2.8vw, 2.15rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
  line-height: 1.18;
}

.how__lead {
  max-width: 30rem;
  margin: 0.6rem 0 0;
  color: var(--muted);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.how__steps {
  position: relative;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.how__steps::before {
  position: absolute;
  top: 1.55rem;
  right: 2rem;
  left: 2rem;
  border-top: 1px dashed var(--border-strong);
  content: '';
}

.step {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.step__number {
  position: absolute;
  top: -0.4rem;
  right: 0.1rem;
  color: var(--brand-100);
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  line-height: 1;
}

.step__icon {
  position: relative;
  z-index: 1;
  display: grid;
  width: 3.1rem;
  height: 3.1rem;
  place-items: center;
  margin-bottom: 0.9rem;
  border: 1px solid var(--brand-100);
  border-radius: var(--radius-lg);
  background: var(--brand-50);
  color: var(--brand-700);
}

.step__title {
  margin: 0;
  font-size: var(--text-base);
  font-weight: 700;
  letter-spacing: var(--tracking-tight);
}

.step__desc {
  margin: 0.35rem 0 0;
  color: var(--muted);
  font-size: 0.8rem;
  line-height: 1.55;
}

@media (max-width: 900px) {
  .how__inner {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

@media (max-width: 640px) {
  .how__steps {
    grid-template-columns: 1fr;
    gap: 1.4rem;
  }

  .how__steps::before {
    top: 1.5rem;
    bottom: 2rem;
    left: 1.55rem;
    border-top: 0;
    border-left: 1px dashed var(--border-strong);
  }

  .step {
    display: grid;
    grid-template-columns: 3.1rem 1fr;
    column-gap: 1rem;
    align-items: start;
  }

  .step__icon {
    grid-row: span 2;
    margin-bottom: 0;
  }

  .step__number {
    display: none;
  }

  .step__title {
    align-self: center;
  }

  .step__desc {
    margin-top: 0.15rem;
  }
}
</style>

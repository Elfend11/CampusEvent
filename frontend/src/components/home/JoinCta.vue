<script setup>
import { RouterLink } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'

defineProps({
  authenticated: { type: Boolean, default: false },
})
</script>

<template>
  <section class="join" aria-labelledby="join-title">
    <span class="join__orb join__orb--one" aria-hidden="true"></span>
    <span class="join__orb join__orb--two" aria-hidden="true"></span>
    <span class="join__pattern" aria-hidden="true"></span>

    <div class="join__inner">
      <div class="join__copy">
        <p class="join__eyebrow">Untuk mahasiswa</p>
        <h2 id="join-title" class="join__title">
          {{ authenticated ? 'Tiketmu menunggu di satu tempat.' : 'Siap hadir di event berikutnya?' }}
        </h2>
        <p class="join__lead">
          {{
            authenticated
              ? 'Buka tiket digital, cek jadwal, dan pastikan kamu tidak melewatkan agenda kampus.'
              : 'Buat akun sekali, lalu daftar event tanpa mengulang data. Tiket digital langsung tersimpan di akunmu.'
          }}
        </p>
      </div>

      <div class="join__actions">
        <template v-if="authenticated">
          <AppButton :to="{ name: 'tickets' }" variant="inverse" size="lg">Tiket saya</AppButton>
          <AppButton :to="{ name: 'events' }" variant="outline-inverse" size="lg">Jelajahi event</AppButton>
        </template>
        <template v-else>
          <AppButton :to="{ name: 'register' }" variant="inverse" size="lg">Daftar sekarang</AppButton>
          <AppButton :to="{ name: 'login' }" variant="outline-inverse" size="lg">Masuk</AppButton>
        </template>
      </div>
    </div>

    <p class="join__note">
      Sudah punya tiket? <RouterLink :to="{ name: 'tickets' }">Buka tiket digitalmu</RouterLink>
    </p>
  </section>
</template>

<style scoped>
.join {
  position: relative;
  overflow: hidden;
  width: calc(100% - 2rem);
  max-width: var(--container);
  margin: clamp(2.5rem, 5vw, 4rem) auto clamp(2rem, 4vw, 3rem);
  padding: clamp(2rem, 5vw, 3.25rem);
  border-radius: var(--radius-2xl);
  background:
    radial-gradient(600px 300px at 10% -20%, rgb(45 212 191 / 28%), transparent 65%),
    linear-gradient(135deg, var(--brand-800), var(--brand-700) 55%, var(--brand-600));
  color: #fff;
  box-shadow: var(--shadow-brand);
}

.join__orb {
  position: absolute;
  border: 1px solid rgb(255 255 255 / 14%);
  border-radius: 50%;
  pointer-events: none;
}

.join__orb--one {
  top: -7rem;
  right: -3rem;
  width: 18rem;
  height: 18rem;
}

.join__orb--two {
  right: 6rem;
  bottom: -9rem;
  width: 14rem;
  height: 14rem;
}

.join__pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgb(255 255 255 / 22%) 1px, transparent 1px);
  background-size: 22px 22px;
  mask-image: linear-gradient(to left, #000, transparent 65%);
  opacity: 0.5;
  pointer-events: none;
}

.join__inner {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1.75rem;
}

.join__copy {
  flex: 1 1 22rem;
  max-width: 38rem;
}

.join__eyebrow {
  margin: 0 0 0.6rem;
  color: var(--brand-200);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.join__title {
  margin: 0;
  font-size: clamp(1.6rem, 3vw, 2.25rem);
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
  line-height: 1.15;
}

.join__lead {
  max-width: 34rem;
  margin: 0.7rem 0 0;
  color: rgb(236 254 255 / 82%);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.join__actions {
  display: flex;
  flex: 0 1 auto;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.join__note {
  position: relative;
  margin: 1.6rem 0 0;
  color: rgb(236 254 255 / 70%);
  font-size: var(--text-xs);
}

.join__note a {
  color: #fff;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 2px;
}

@media (max-width: 560px) {
  .join {
    width: calc(100% - 2rem);
    padding: 1.6rem 1.25rem;
    border-radius: var(--radius-xl);
  }

  .join__actions {
    width: 100%;
    flex-direction: column;
  }

  .join__actions :deep(.btn) {
    width: 100%;
  }
}
</style>

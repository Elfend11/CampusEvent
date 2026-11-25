<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const mobileOpen = ref(false)
const accountOpen = ref(false)
const scrolled = ref(false)
const accountRef = ref(null)
const menuButton = ref(null)
const drawerClose = ref(null)

const year = new Date().getFullYear()

const isAdmin = computed(() => auth.hasAnyRole(['SUPER_ADMIN', 'ORGANIZATION_ADMIN']))
const isOfficer = computed(() => auth.hasAnyRole(['CHECK_IN_OFFICER', 'SUPER_ADMIN']))

const initials = computed(() => {
  const name = auth.user?.name?.trim()

  if (!name) {
    return 'CE'
  }

  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
})

function closeAccount() {
  accountOpen.value = false
}

function closeMobile(restoreFocus = false) {
  mobileOpen.value = false

  if (restoreFocus) {
    nextTick(() => menuButton.value?.focus())
  }
}

function toggleMobile() {
  if (mobileOpen.value) {
    closeMobile(true)
  } else {
    accountOpen.value = false
    mobileOpen.value = true
  }
}

async function logout() {
  closeAccount()
  closeMobile()
  await auth.logout()
  await router.push({ name: 'home' })
}

function onDocumentClick(event) {
  if (accountOpen.value && accountRef.value && !accountRef.value.contains(event.target)) {
    closeAccount()
  }
}

function onKeydown(event) {
  if (event.key !== 'Escape') {
    return
  }

  if (accountOpen.value) {
    closeAccount()
  }

  if (mobileOpen.value) {
    closeMobile(true)
  }
}

function onScroll() {
  scrolled.value = window.scrollY > 8
}

watch(mobileOpen, async (open) => {
  document.body.style.overflow = open ? 'hidden' : ''

  if (open) {
    await nextTick()
    drawerClose.value?.focus()
  }
})

watch(
  () => route.fullPath,
  () => {
    closeMobile()
    closeAccount()
  },
)

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentClick)
  document.addEventListener('keydown', onKeydown)
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onDocumentClick)
  document.removeEventListener('keydown', onKeydown)
  window.removeEventListener('scroll', onScroll)
  document.body.style.overflow = ''
})
</script>

<template>
  <div class="student-shell">
    <a class="skip-link" href="#main">Lewati ke konten</a>

    <header class="site-header" :class="{ 'is-scrolled': scrolled }">
      <div class="site-header__inner">
        <RouterLink to="/" class="brand" aria-label="CampusEvent — beranda">
          <span class="brand__mark" aria-hidden="true">
            <svg viewBox="0 0 32 32" width="30" height="30">
              <rect width="32" height="32" rx="9" fill="currentColor" />
              <path d="M8.5 11.5A2.5 2.5 0 0 1 11 9h10a2.5 2.5 0 0 1 2.5 2.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7A2.5 2.5 0 0 1 21 23H11a2.5 2.5 0 0 1-2.5-2.5v-1.7a2.8 2.8 0 0 0 0-5.6z" fill="#fff" />
              <path d="M13 13.25v5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="1 2.4" />
              <circle cx="20" cy="16" r="1.6" fill="#5eead4" />
            </svg>
          </span>
          <span class="brand__text">Campus<span>Event</span></span>
        </RouterLink>

        <nav class="primary-nav" aria-label="Navigasi utama">
          <RouterLink to="/" class="nav-link">Beranda</RouterLink>
          <RouterLink :to="{ name: 'events' }" class="nav-link">Jelajahi event</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" :to="{ name: 'tickets' }" class="nav-link">
            Tiket saya
          </RouterLink>
          <RouterLink v-if="auth.isAuthenticated" :to="{ name: 'dashboard' }" class="nav-link">
            Dashboard
          </RouterLink>
        </nav>

        <div class="header-actions">
          <template v-if="auth.isAuthenticated">
            <div ref="accountRef" class="account">
              <button
                type="button"
                class="account__trigger"
                :aria-expanded="accountOpen"
                aria-haspopup="true"
                aria-controls="account-menu"
                @click="accountOpen = !accountOpen"
              >
                <span class="avatar" aria-hidden="true">{{ initials }}</span>
                <span class="account__name">{{ auth.user?.name }}</span>
                <svg class="account__chevron" :class="{ 'is-open': accountOpen }" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </button>

              <Transition name="pop">
                <div v-if="accountOpen" id="account-menu" class="account-menu">
                  <div class="account-menu__head">
                    <strong>{{ auth.user?.name }}</strong>
                    <span>{{ auth.user?.email }}</span>
                  </div>

                  <RouterLink :to="{ name: 'dashboard' }" class="account-menu__item">Dashboard</RouterLink>
                  <RouterLink :to="{ name: 'registrations' }" class="account-menu__item">Registrasi saya</RouterLink>
                  <RouterLink :to="{ name: 'tickets' }" class="account-menu__item">Tiket saya</RouterLink>

                  <template v-if="isAdmin || isOfficer">
                    <p class="account-menu__label">Pengelola</p>
                    <RouterLink v-if="isAdmin" :to="{ name: 'manage-events' }" class="account-menu__item">Kelola event</RouterLink>
                    <RouterLink v-if="isAdmin" :to="{ name: 'organizations' }" class="account-menu__item">Organisasi</RouterLink>
                    <RouterLink v-if="isOfficer" :to="{ name: 'check-in' }" class="account-menu__item">Check-in</RouterLink>
                  </template>

                  <button type="button" class="account-menu__logout" @click="logout">Keluar</button>
                </div>
              </Transition>
            </div>
          </template>

          <template v-else>
            <RouterLink :to="{ name: 'login' }" class="header-login">Masuk</RouterLink>
            <AppButton :to="{ name: 'register' }" size="sm">Daftar</AppButton>
          </template>

          <button
            ref="menuButton"
            type="button"
            class="menu-toggle"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-drawer"
            aria-label="Buka menu navigasi"
            @click="toggleMobile"
          >
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </header>

    <main
      id="main"
      class="site-main"
      :class="{ 'site-main--flush': route.meta.bleed }"
      tabindex="-1"
    >
      <RouterView />
    </main>

    <footer class="site-footer">
      <div class="site-footer__inner">
        <div class="site-footer__brand">
          <RouterLink to="/" class="brand brand--footer" aria-label="CampusEvent — beranda">
            <span class="brand__mark" aria-hidden="true">
              <svg viewBox="0 0 32 32" width="26" height="26">
                <rect width="32" height="32" rx="9" fill="currentColor" />
                <path d="M8.5 11.5A2.5 2.5 0 0 1 11 9h10a2.5 2.5 0 0 1 2.5 2.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7A2.5 2.5 0 0 1 21 23H11a2.5 2.5 0 0 1-2.5-2.5v-1.7a2.8 2.8 0 0 0 0-5.6z" fill="#fff" />
                <path d="M13 13.25v5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="1 2.4" />
                <circle cx="20" cy="16" r="1.6" fill="#5eead4" />
              </svg>
            </span>
            <span class="brand__text">Campus<span>Event</span></span>
          </RouterLink>
          <p class="site-footer__tagline">
            Ruang temu event kampus: temukan kegiatan, daftar dengan mudah, dan simpan tiket digitalmu.
          </p>
        </div>

        <nav class="site-footer__col" aria-label="Jelajahi">
          <p class="site-footer__heading">Jelajahi</p>
          <RouterLink to="/">Beranda</RouterLink>
          <RouterLink :to="{ name: 'events' }">Semua event</RouterLink>
          <RouterLink :to="{ name: 'status' }">Status layanan</RouterLink>
        </nav>

        <nav class="site-footer__col" aria-label="Akun">
          <p class="site-footer__heading">Akun</p>
          <template v-if="auth.isAuthenticated">
            <RouterLink :to="{ name: 'dashboard' }">Dashboard</RouterLink>
            <RouterLink :to="{ name: 'registrations' }">Registrasi saya</RouterLink>
            <RouterLink :to="{ name: 'tickets' }">Tiket saya</RouterLink>
          </template>
          <template v-else>
            <RouterLink :to="{ name: 'login' }">Masuk</RouterLink>
            <RouterLink :to="{ name: 'register' }">Daftar akun</RouterLink>
          </template>
        </nav>

        <nav v-if="isAdmin || isOfficer" class="site-footer__col" aria-label="Pengelola">
          <p class="site-footer__heading">Pengelola</p>
          <RouterLink v-if="isAdmin" :to="{ name: 'manage-events' }">Kelola event</RouterLink>
          <RouterLink v-if="isAdmin" :to="{ name: 'organizations' }">Organisasi</RouterLink>
          <RouterLink v-if="isOfficer" :to="{ name: 'check-in' }">Check-in</RouterLink>
        </nav>
      </div>

      <div class="site-footer__bottom">
        <span>&copy; {{ year }} CampusEvent</span>
        <span>Sistem Informasi Manajemen Event Kampus</span>
      </div>
    </footer>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="mobileOpen" class="drawer-overlay" aria-hidden="true" @click="closeMobile(true)"></div>
      </Transition>

      <Transition name="drawer">
        <div
          v-if="mobileOpen"
          id="mobile-drawer"
          class="drawer"
          role="dialog"
          aria-modal="true"
          aria-label="Menu navigasi"
        >
          <div class="drawer__head">
            <RouterLink to="/" class="brand" @click="closeMobile()">
              <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" width="28" height="28">
                  <rect width="32" height="32" rx="9" fill="currentColor" />
                  <path d="M8.5 11.5A2.5 2.5 0 0 1 11 9h10a2.5 2.5 0 0 1 2.5 2.5v1.7a2.8 2.8 0 0 0 0 5.6v1.7A2.5 2.5 0 0 1 21 23H11a2.5 2.5 0 0 1-2.5-2.5v-1.7a2.8 2.8 0 0 0 0-5.6z" fill="#fff" />
                  <path d="M13 13.25v5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="1 2.4" />
                  <circle cx="20" cy="16" r="1.6" fill="#5eead4" />
                </svg>
              </span>
              <span class="brand__text">Campus<span>Event</span></span>
            </RouterLink>

            <button
              ref="drawerClose"
              type="button"
              class="drawer__close"
              aria-label="Tutup menu"
              @click="closeMobile(true)"
            >
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="m6 6 12 12M18 6 6 18" />
              </svg>
            </button>
          </div>

          <nav class="drawer__nav" aria-label="Navigasi mobile">
            <RouterLink to="/" class="drawer__link" @click="closeMobile()">Beranda</RouterLink>
            <RouterLink :to="{ name: 'events' }" class="drawer__link" @click="closeMobile()">Jelajahi event</RouterLink>
            <RouterLink :to="{ name: 'status' }" class="drawer__link" @click="closeMobile()">Status layanan</RouterLink>
            <template v-if="auth.isAuthenticated">
              <RouterLink :to="{ name: 'tickets' }" class="drawer__link" @click="closeMobile()">Tiket saya</RouterLink>
              <RouterLink :to="{ name: 'registrations' }" class="drawer__link" @click="closeMobile()">Registrasi saya</RouterLink>
              <RouterLink :to="{ name: 'dashboard' }" class="drawer__link" @click="closeMobile()">Dashboard</RouterLink>
            </template>
          </nav>

          <div v-if="isAdmin || isOfficer" class="drawer__section">
            <p class="drawer__label">Pengelola</p>
            <nav class="drawer__nav" aria-label="Navigasi pengelola">
              <RouterLink v-if="isAdmin" :to="{ name: 'manage-events' }" class="drawer__link" @click="closeMobile()">Kelola event</RouterLink>
              <RouterLink v-if="isAdmin" :to="{ name: 'organizations' }" class="drawer__link" @click="closeMobile()">Organisasi</RouterLink>
              <RouterLink v-if="isOfficer" :to="{ name: 'check-in' }" class="drawer__link" @click="closeMobile()">Check-in</RouterLink>
            </nav>
          </div>

          <div class="drawer__account">
            <template v-if="auth.isAuthenticated">
              <div class="drawer__user">
                <span class="avatar" aria-hidden="true">{{ initials }}</span>
                <div>
                  <strong>{{ auth.user?.name }}</strong>
                  <span>{{ auth.user?.email }}</span>
                </div>
              </div>
              <AppButton variant="secondary" block @click="logout">Keluar</AppButton>
            </template>
            <template v-else>
              <AppButton :to="{ name: 'login' }" variant="secondary" block @click="closeMobile()">Masuk</AppButton>
              <AppButton :to="{ name: 'register' }" block @click="closeMobile()">Daftar akun</AppButton>
            </template>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.student-shell {
  display: flex;
  min-height: 100vh;
  flex-direction: column;
}

/* Header ------------------------------------------------------------------ */

.site-header {
  position: sticky;
  top: 0;
  z-index: 80;
  border-bottom: 1px solid transparent;
  background: rgb(248 250 252 / 82%);
  backdrop-filter: blur(14px);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast), background-color var(--transition-fast);
}

.site-header.is-scrolled {
  border-bottom-color: var(--border);
  background: rgb(255 255 255 / 92%);
  box-shadow: 0 8px 24px -20px rgb(15 23 42 / 35%);
}

.site-header__inner {
  display: flex;
  width: 100%;
  max-width: var(--container);
  min-height: var(--header-height);
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin: 0 auto;
  padding: 0 clamp(1rem, 4vw, 2rem);
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  color: var(--brand);
  font-weight: 800;
}

.brand__mark {
  display: inline-flex;
  color: var(--brand);
}

.brand__text {
  color: var(--text);
  font-size: 1.12rem;
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.brand__text span {
  color: var(--brand);
}

.primary-nav {
  display: flex;
  align-items: center;
  gap: 0.2rem;
}

.nav-link {
  position: relative;
  display: inline-flex;
  min-height: 42px;
  align-items: center;
  padding: 0 0.85rem;
  border-radius: var(--radius-full);
  color: var(--text-soft);
  font-size: var(--text-sm);
  font-weight: 600;
  transition: color var(--transition-fast), background-color var(--transition-fast);
}

.nav-link:hover {
  background: var(--surface);
  color: var(--brand);
}

.nav-link.router-link-active {
  background: var(--brand-50);
  color: var(--brand-700);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.header-login {
  padding: 0.5rem 0.8rem;
  color: var(--text-soft);
  font-size: var(--text-sm);
  font-weight: 600;
  transition: color var(--transition-fast);
}

.header-login:hover {
  color: var(--brand);
}

.account {
  position: relative;
}

.account__trigger {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  gap: 0.55rem;
  padding: 0.3rem 0.7rem 0.3rem 0.35rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-full);
  background: var(--surface);
  color: var(--text);
  cursor: pointer;
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.account__trigger:hover {
  border-color: var(--brand-200);
  box-shadow: var(--shadow-xs);
}

.avatar {
  display: grid;
  width: 2rem;
  height: 2rem;
  flex-shrink: 0;
  place-items: center;
  border-radius: 50%;
  background: var(--brand);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.account__name {
  max-width: 9rem;
  overflow: hidden;
  font-size: var(--text-sm);
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account__chevron {
  color: var(--muted);
  transition: transform var(--transition-fast);
}

.account__chevron.is-open {
  transform: rotate(180deg);
}

.account-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  z-index: 90;
  width: 17rem;
  overflow: hidden;
  padding: 0.4rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-lg);
}

.account-menu__head {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  margin-bottom: 0.3rem;
  padding: 0.7rem 0.7rem 0.65rem;
  border-bottom: 1px solid var(--border);
}

.account-menu__head strong {
  overflow: hidden;
  font-size: var(--text-sm);
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account-menu__head span {
  overflow: hidden;
  color: var(--muted);
  font-size: var(--text-xs);
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account-menu__item {
  display: flex;
  min-height: 40px;
  align-items: center;
  padding: 0.45rem 0.7rem;
  border-radius: var(--radius-sm);
  color: var(--text-soft);
  font-size: var(--text-sm);
  font-weight: 500;
}

.account-menu__item:hover {
  background: var(--brand-50);
  color: var(--brand-700);
}

.account-menu__label {
  margin: 0.45rem 0 0.2rem;
  padding: 0.3rem 0.7rem 0;
  border-top: 1px solid var(--border);
  color: var(--muted);
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.account-menu__logout {
  width: 100%;
  min-height: 40px;
  margin-top: 0.3rem;
  padding: 0.45rem 0.7rem;
  border: 0;
  border-top: 1px solid var(--border);
  border-radius: 0 0 var(--radius-sm) var(--radius-sm);
  background: transparent;
  color: var(--error);
  font: inherit;
  font-size: var(--text-sm);
  font-weight: 600;
  text-align: left;
  cursor: pointer;
}

.account-menu__logout:hover {
  background: var(--error-bg);
}

.menu-toggle {
  display: none;
  width: 44px;
  height: 44px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  cursor: pointer;
}

.menu-toggle span {
  display: block;
  width: 18px;
  height: 2px;
  border-radius: 2px;
  background: var(--text);
}

/* Main -------------------------------------------------------------------- */

.site-main {
  flex: 1;
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(1.5rem, 3.5vw, 2.5rem) clamp(1rem, 4vw, 2rem) clamp(3rem, 6vw, 4.5rem);
}

.site-main--flush {
  max-width: none;
  padding: 0;
}

/* Footer ------------------------------------------------------------------ */

.site-footer {
  border-top: 1px solid var(--border);
  background: var(--surface);
}

.site-footer__inner {
  display: flex;
  flex-wrap: wrap;
  max-width: var(--container);
  gap: clamp(1.5rem, 4vw, 3rem);
  justify-content: space-between;
  margin: 0 auto;
  padding: clamp(2rem, 5vw, 3rem) clamp(1rem, 4vw, 2rem) 2rem;
}

.site-footer__brand {
  flex: 1 1 20rem;
  max-width: 24rem;
}

.brand--footer {
  margin-bottom: 0.7rem;
}

.site-footer__tagline {
  margin: 0;
  color: var(--muted);
  font-size: var(--text-sm);
  line-height: 1.65;
}

.site-footer__col {
  display: flex;
  flex: 0 1 10rem;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.55rem;
}

.site-footer__heading {
  margin: 0 0 0.2rem;
  color: var(--text);
  font-size: var(--text-xs);
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.site-footer__col a {
  color: var(--muted);
  font-size: var(--text-sm);
  transition: color var(--transition-fast);
}

.site-footer__col a:hover {
  color: var(--brand);
}

.site-footer__bottom {
  display: flex;
  max-width: var(--container);
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin: 0 auto;
  padding: 1rem clamp(1rem, 4vw, 2rem) 1.4rem;
  border-top: 1px solid var(--border);
  color: var(--muted);
  font-size: var(--text-xs);
}

/* Drawer ------------------------------------------------------------------ */

.drawer-overlay {
  position: fixed;
  inset: 0;
  z-index: 110;
  background: rgb(15 23 42 / 42%);
  backdrop-filter: blur(2px);
}

.drawer {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 120;
  display: flex;
  width: min(88vw, 360px);
  height: 100dvh;
  flex-direction: column;
  overflow-y: auto;
  padding: 1rem 1.1rem 1.5rem;
  border-left: 1px solid var(--border);
  background: var(--surface);
  box-shadow: var(--shadow-lg);
}

.drawer__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid var(--border);
}

.drawer__close {
  display: grid;
  width: 44px;
  height: 44px;
  place-items: center;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  color: var(--text-soft);
  cursor: pointer;
}

.drawer__close:hover {
  background: var(--bg-subtle);
}

.drawer__nav {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.9rem 0;
}

.drawer__link {
  display: flex;
  min-height: 48px;
  align-items: center;
  padding: 0.6rem 0.7rem;
  border-radius: var(--radius-md);
  color: var(--text-soft);
  font-size: var(--text-base);
  font-weight: 600;
}

.drawer__link:hover,
.drawer__link.router-link-active {
  background: var(--brand-50);
  color: var(--brand-700);
}

.drawer__section {
  padding: 0.6rem 0;
  border-top: 1px solid var(--border);
}

.drawer__label {
  margin: 0;
  padding: 0 0.7rem;
  color: var(--muted);
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.drawer__account {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin-top: auto;
  padding-top: 1.2rem;
  border-top: 1px solid var(--border);
}

.drawer__user {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-bottom: 0.3rem;
  min-width: 0;
}

.drawer__user div {
  display: flex;
  min-width: 0;
  flex-direction: column;
}

.drawer__user strong {
  overflow: hidden;
  font-size: var(--text-sm);
  text-overflow: ellipsis;
  white-space: nowrap;
}

.drawer__user span {
  overflow: hidden;
  color: var(--muted);
  font-size: var(--text-xs);
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Transitions ------------------------------------------------------------- */

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-base);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: transform var(--transition-base);
}

.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}

.pop-enter-active,
.pop-leave-active {
  transition: opacity var(--transition-fast), transform var(--transition-fast);
  transform-origin: top right;
}

.pop-enter-from,
.pop-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}

/* Responsive -------------------------------------------------------------- */

@media (max-width: 1024px) {
  .primary-nav {
    gap: 0;
  }

  .nav-link {
    padding: 0 0.6rem;
    font-size: 0.82rem;
  }

  .account__name {
    display: none;
  }
}

@media (max-width: 900px) {
  .primary-nav {
    display: none;
  }

  .header-login,
  .header-actions > .btn {
    display: none;
  }

  .menu-toggle {
    display: flex;
  }

  .site-header__inner {
    min-height: 64px;
  }
}

@media (max-width: 480px) {
  .brand__text {
    font-size: 1.02rem;
  }

  .site-footer__brand {
    flex-basis: 100%;
  }

  .site-footer__col {
    flex: 1 1 8rem;
  }

  .site-footer__bottom {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}
</style>

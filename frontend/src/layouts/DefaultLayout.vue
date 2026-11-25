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
  <div class="admin-shell">
    <a class="skip-link" href="#admin-main">Lewati ke konten</a>

    <header class="admin-header" :class="{ 'is-scrolled': scrolled }">
      <div class="admin-header__inner">
        <div class="admin-brand">
          <RouterLink to="/" class="brand" aria-label="CampusEvent — beranda">
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
          <span class="admin-brand__tag">Pengelola</span>
        </div>

        <nav class="admin-nav" aria-label="Navigasi pengelola">
          <RouterLink to="/" class="admin-link">Beranda</RouterLink>
          <RouterLink :to="{ name: 'events' }" class="admin-link">Event</RouterLink>
          <RouterLink :to="{ name: 'dashboard' }" class="admin-link">Dashboard</RouterLink>
          <RouterLink v-if="isAdmin" :to="{ name: 'manage-events' }" class="admin-link">Kelola event</RouterLink>
          <RouterLink v-if="isAdmin" :to="{ name: 'organizations' }" class="admin-link">Organisasi</RouterLink>
          <RouterLink v-if="isOfficer" :to="{ name: 'check-in' }" class="admin-link">Check-in</RouterLink>
        </nav>

        <div class="admin-actions">
          <RouterLink to="/" class="site-link">
            Lihat situs
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M7 17 17 7M9 7h8v8" />
            </svg>
          </RouterLink>

          <div ref="accountRef" class="account">
            <button
              type="button"
              class="account__trigger"
              :aria-expanded="accountOpen"
              aria-haspopup="true"
              aria-controls="admin-account-menu"
              @click="accountOpen = !accountOpen"
            >
              <span class="avatar" aria-hidden="true">{{ initials }}</span>
              <span class="account__name">{{ auth.user?.name }}</span>
            </button>

            <Transition name="pop">
              <div v-if="accountOpen" id="admin-account-menu" class="account-menu">
                <div class="account-menu__head">
                  <strong>{{ auth.user?.name }}</strong>
                  <span>{{ auth.user?.email }}</span>
                </div>
                <RouterLink :to="{ name: 'registrations' }" class="account-menu__item">Registrasi saya</RouterLink>
                <RouterLink :to="{ name: 'tickets' }" class="account-menu__item">Tiket saya</RouterLink>
                <button type="button" class="account-menu__logout" @click="logout">Keluar</button>
              </div>
            </Transition>
          </div>

          <button
            ref="menuButton"
            type="button"
            class="menu-toggle"
            :aria-expanded="mobileOpen"
            aria-controls="admin-drawer"
            aria-label="Buka menu navigasi"
            @click="toggleMobile"
          >
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </header>

    <main id="admin-main" class="admin-main" tabindex="-1">
      <RouterView />
    </main>

    <footer class="admin-footer">
      CampusEvent &middot; Panel pengelola &middot; Sistem Informasi Manajemen Event Kampus
    </footer>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="mobileOpen" class="drawer-overlay" aria-hidden="true" @click="closeMobile(true)"></div>
      </Transition>

      <Transition name="drawer">
        <div
          v-if="mobileOpen"
          id="admin-drawer"
          class="drawer"
          role="dialog"
          aria-modal="true"
          aria-label="Menu pengelola"
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

          <p class="drawer__label">Jelajahi</p>
          <nav class="drawer__nav" aria-label="Navigasi umum">
            <RouterLink to="/" class="drawer__link" @click="closeMobile()">Beranda</RouterLink>
            <RouterLink :to="{ name: 'events' }" class="drawer__link" @click="closeMobile()">Event</RouterLink>
          </nav>

          <p class="drawer__label">Akun</p>
          <nav class="drawer__nav" aria-label="Navigasi akun">
            <RouterLink :to="{ name: 'dashboard' }" class="drawer__link" @click="closeMobile()">Dashboard</RouterLink>
            <RouterLink :to="{ name: 'registrations' }" class="drawer__link" @click="closeMobile()">Registrasi saya</RouterLink>
            <RouterLink :to="{ name: 'tickets' }" class="drawer__link" @click="closeMobile()">Tiket saya</RouterLink>
          </nav>

          <template v-if="isAdmin || isOfficer">
            <p class="drawer__label">Manajemen</p>
            <nav class="drawer__nav" aria-label="Navigasi manajemen">
              <RouterLink v-if="isAdmin" :to="{ name: 'manage-events' }" class="drawer__link" @click="closeMobile()">Kelola event</RouterLink>
              <RouterLink v-if="isAdmin" :to="{ name: 'organizations' }" class="drawer__link" @click="closeMobile()">Organisasi</RouterLink>
              <RouterLink v-if="isOfficer" :to="{ name: 'check-in' }" class="drawer__link" @click="closeMobile()">Check-in</RouterLink>
            </nav>
          </template>

          <div class="drawer__account">
            <div class="drawer__user">
              <span class="avatar" aria-hidden="true">{{ initials }}</span>
              <div>
                <strong>{{ auth.user?.name }}</strong>
                <span>{{ auth.user?.email }}</span>
              </div>
            </div>
            <AppButton variant="secondary" block @click="logout">Keluar</AppButton>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.admin-shell {
  display: flex;
  min-height: 100vh;
  flex-direction: column;
  background: var(--bg);
}

.admin-header {
  position: sticky;
  top: 0;
  z-index: 80;
  border-top: 3px solid var(--brand);
  border-bottom: 1px solid transparent;
  background: rgb(255 255 255 / 92%);
  backdrop-filter: blur(14px);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.admin-header.is-scrolled {
  border-bottom-color: var(--border);
  box-shadow: 0 8px 24px -20px rgb(15 23 42 / 35%);
}

.admin-header__inner {
  display: flex;
  width: 100%;
  max-width: var(--container);
  min-height: 66px;
  align-items: center;
  gap: 1rem;
  margin: 0 auto;
  padding: 0 clamp(1rem, 4vw, 2rem);
}

.admin-brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.brand__mark {
  display: inline-flex;
  color: var(--brand);
}

.brand__text {
  color: var(--text);
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: var(--tracking-tight);
}

.brand__text span {
  color: var(--brand);
}

.admin-brand__tag {
  padding: 0.18rem 0.5rem;
  border: 1px solid var(--brand-200);
  border-radius: var(--radius-full);
  background: var(--brand-50);
  color: var(--brand-700);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.admin-nav {
  display: flex;
  align-items: center;
  gap: 0.1rem;
  margin-left: auto;
}

.admin-link {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  padding: 0 0.7rem;
  border-radius: var(--radius-full);
  color: var(--text-soft);
  font-size: 0.82rem;
  font-weight: 600;
  transition: background-color var(--transition-fast), color var(--transition-fast);
}

.admin-link:hover {
  background: var(--bg-subtle);
  color: var(--brand-700);
}

.admin-link.router-link-active {
  background: var(--brand-50);
  color: var(--brand-700);
}

.admin-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: auto;
}

.site-link {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.4rem 0.6rem;
  color: var(--muted);
  font-size: var(--text-xs);
  font-weight: 600;
}

.site-link:hover {
  color: var(--brand);
}

.account {
  position: relative;
}

.account__trigger {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  gap: 0.45rem;
  padding: 0.25rem 0.6rem 0.25rem 0.3rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-full);
  background: var(--surface);
  color: var(--text);
  cursor: pointer;
}

.account__trigger:hover {
  border-color: var(--brand-200);
}

.avatar {
  display: grid;
  width: 1.9rem;
  height: 1.9rem;
  flex-shrink: 0;
  place-items: center;
  border-radius: 50%;
  background: var(--brand);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
}

.account__name {
  max-width: 8rem;
  overflow: hidden;
  font-size: var(--text-xs);
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  z-index: 90;
  width: 16rem;
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
  padding: 0.65rem 0.7rem;
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
}

.account-menu__item:hover {
  background: var(--brand-50);
  color: var(--brand-700);
}

.account-menu__logout {
  width: 100%;
  min-height: 40px;
  margin-top: 0.3rem;
  padding: 0.45rem 0.7rem;
  border: 0;
  border-top: 1px solid var(--border);
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
  width: 42px;
  height: 42px;
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

.admin-main {
  flex: 1;
  width: 100%;
  max-width: var(--container);
  margin: 0 auto;
  padding: clamp(1.5rem, 3.5vw, 2.5rem) clamp(1rem, 4vw, 2rem) clamp(3rem, 6vw, 4rem);
}

.admin-footer {
  padding: 1.2rem clamp(1rem, 4vw, 2rem);
  border-top: 1px solid var(--border);
  background: var(--surface);
  color: var(--muted);
  font-size: var(--text-xs);
  text-align: center;
}

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

.drawer__label {
  margin: 0.9rem 0 0.2rem;
  padding: 0 0.7rem;
  color: var(--muted);
  font-size: 0.64rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.drawer__nav {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.drawer__link {
  display: flex;
  min-height: 46px;
  align-items: center;
  padding: 0.55rem 0.7rem;
  border-radius: var(--radius-md);
  color: var(--text-soft);
  font-size: var(--text-sm);
  font-weight: 600;
}

.drawer__link:hover,
.drawer__link.router-link-active {
  background: var(--brand-50);
  color: var(--brand-700);
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
  min-width: 0;
  align-items: center;
  gap: 0.6rem;
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

@media (max-width: 1120px) {
  .admin-brand__tag,
  .site-link {
    display: none;
  }

  .admin-link {
    padding: 0 0.55rem;
    font-size: 0.78rem;
  }
}

@media (max-width: 940px) {
  .admin-nav {
    display: none;
  }

  .admin-actions {
    margin-left: auto;
  }

  .account__name {
    display: none;
  }

  .menu-toggle {
    display: flex;
  }

  .admin-header__inner {
    min-height: 62px;
  }
}

@media (max-width: 400px) {
  .brand__text {
    font-size: 0.98rem;
  }
}
</style>

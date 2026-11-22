import { createRouter, createWebHistory } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import HomeView from '@/views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: () => import('@/views/VerifyEmailView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/organizations',
      name: 'organizations',
      component: () => import('@/views/organizations/OrganizationsView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
    {
      path: '/organizations/:id',
      name: 'organization-detail',
      component: () => import('@/views/organizations/OrganizationDetailView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
    {
      path: '/events',
      name: 'events',
      component: () => import('@/views/events/EventsView.vue'),
    },
    {
      path: '/events/:id',
      name: 'event-detail',
      component: () => import('@/views/events/EventDetailView.vue'),
    },
    {
      path: '/registrations',
      name: 'registrations',
      component: () => import('@/views/registrations/MyRegistrationsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/payments/:id',
      name: 'payment',
      component: () => import('@/views/payments/PaymentView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/tickets',
      name: 'tickets',
      component: () => import('@/views/tickets/MyTicketsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/tickets/:id',
      name: 'ticket-detail',
      component: () => import('@/views/tickets/TicketView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/check-in',
      name: 'check-in',
      component: () => import('@/views/checkin/CheckInView.vue'),
      meta: { requiresAuth: true, roles: ['CHECK_IN_OFFICER', 'SUPER_ADMIN'] },
    },
    {
      path: '/manage/events',
      name: 'manage-events',
      component: () => import('@/views/events/ManageEventsView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
    {
      path: '/manage/events/new',
      name: 'event-create',
      component: () => import('@/views/events/EventFormView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
    {
      path: '/manage/events/:id/edit',
      name: 'event-edit',
      component: () => import('@/views/events/EventFormView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
    {
      path: '/reports/events/:id',
      name: 'event-report',
      component: () => import('@/views/reports/ReportView.vue'),
      meta: { requiresAuth: true, roles: ['SUPER_ADMIN', 'ORGANIZATION_ADMIN'] },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.roles && !to.meta.roles.some((role) => auth.roles.includes(role))) {
    return { name: 'dashboard' }
  }

  return true
})

export default router

import axios from 'axios'
import { defineStore } from 'pinia'

import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => state.user !== null,
    roles: (state) => state.user?.roles ?? [],
    hasRole: (state) => (role) => state.user?.roles?.includes(role) ?? false,
    hasAnyRole: (state) => (roles) => roles.some((role) => state.user?.roles?.includes(role)),
  },
  actions: {
    async fetchUser() {
      this.loading = true

      try {
        const { data } = await api.get('/auth/user')
        this.user = data.data
      } catch (error) {
        if (error.response?.status !== 401) {
          console.error('Gagal memuat sesi pengguna.', error)
        }

        this.user = null
      } finally {
        this.loading = false
        this.initialized = true
      }
    },

    async login(credentials) {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

      const { data } = await api.post('/auth/login', credentials)
      this.user = data.data
      this.initialized = true

      return data.data
    },

    async register(payload) {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

      const { data } = await api.post('/auth/register', payload)

      // Registration does not authenticate the user. The account must verify
      // its email address with the OTP that was just sent.
      return data
    },

    async verifyOtp(payload) {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

      const { data } = await api.post('/auth/verify-otp', payload)

      return data
    },

    async resendOtp(payload) {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

      const { data } = await api.post('/auth/resend-otp', payload)

      return data
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } finally {
        this.user = null
        this.initialized = true
      }
    },

    clear() {
      this.user = null
    },
  },
})

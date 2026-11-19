import { defineStore } from 'pinia'

import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => state.user !== null,
  },
  actions: {
    async fetchUser() {
      this.loading = true

      try {
        const { data } = await api.get('/auth/user')
        this.user = data.data ?? data
      } catch {
        this.user = null
      } finally {
        this.loading = false
      }
    },
    clear() {
      this.user = null
    },
  },
})

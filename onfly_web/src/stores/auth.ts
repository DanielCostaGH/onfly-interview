import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { LoginResponse, User } from 'src/types/api';

function getStored<T>(key: string): T | null {
  if (typeof localStorage === 'undefined') return null;
  const raw = localStorage.getItem(key);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as T;
  } catch {
    return null;
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null,
    user: getStored<User>('user'),
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },
  actions: {
    async login(email: string, password: string) {
      this.loading = true;
      try {
        const { data } = await api.post<LoginResponse>('/api/auth/login', {
          email,
          password,
        });
        this.token = data.token;
        this.user = data.user;
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
      } finally {
        this.loading = false;
      }
    },
    async fetchMe() {
      const { data } = await api.get<User>('/api/auth/me');
      this.user = data;
      localStorage.setItem('user', JSON.stringify(data));
    },
    async logout() {
      await api.post('/api/auth/logout');
      this.token = null;
      this.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    },
  },
});

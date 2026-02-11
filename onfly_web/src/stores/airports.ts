import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { Airport } from 'src/types/api';

export const useAirportsStore = defineStore('airports', {
  state: () => ({
    items: [] as Airport[],
    loading: false,
  }),
  actions: {
    async fetchAll(force = false) {
      if (this.loading) return;
      if (!force && this.items.length > 0) return;
      this.loading = true;
      try {
        const { data } = await api.get<{ data: Airport[] }>('/api/airports');
        this.items = data.data;
      } finally {
        this.loading = false;
      }
    },
  },
});

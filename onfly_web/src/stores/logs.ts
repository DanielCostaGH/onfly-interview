import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { TravelRequestLog, TravelStatus } from 'src/types/api';

export interface LogFilters {
  status?: TravelStatus | null;
  user_id?: number | null;
  code?: string | null;
  created_from?: string | null;
  created_to?: string | null;
}

export const useLogsStore = defineStore('logs', {
  state: () => ({
    items: [] as TravelRequestLog[],
    loading: false,
  }),
  actions: {
    async fetchAll(filters: LogFilters = {}) {
      this.loading = true;
      try {
        const params = Object.fromEntries(
          Object.entries(filters).filter(([, value]) => value !== null && value !== undefined && value !== '')
        );
        const { data } = await api.get<{ data: TravelRequestLog[] }>('/api/travel-request-logs', { params });
        this.items = data.data;
      } finally {
        this.loading = false;
      }
    },
  },
});

import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { TravelRequest, TravelStatus } from 'src/types/api';

export interface TravelFilters {
  status?: TravelStatus | null;
  destination_airport_id?: number | null;
  created_from?: string | null;
  created_to?: string | null;
  travel_from?: string | null;
  travel_to?: string | null;
}

export const useTravelRequestsStore = defineStore('travelRequests', {
  state: () => ({
    items: [] as TravelRequest[],
    loading: false,
  }),
  actions: {
    async fetchAll(filters: TravelFilters = {}) {
      this.loading = true;
      try {
        const params = Object.fromEntries(
          Object.entries({
            ...filters,
            per_page: 100,
          }).filter(([, value]) => value !== null && value !== undefined && value !== '')
        );
        const { data } = await api.get<{ data: TravelRequest[] }>('/api/travel-requests', { params });
        this.items = data.data;
      } finally {
        this.loading = false;
      }
    },
    async createRequest(payload: {
      destination_airport_id: number;
      departure_date: string;
      return_date: string;
    }) {
      const { data } = await api.post<{ data: TravelRequest }>('/api/travel-requests', payload);
      this.items.unshift(data.data);
      return data.data;
    },
    async updateStatus(id: number, status: TravelStatus) {
      const { data } = await api.patch<{ data: TravelRequest }>(`/api/travel-requests/${id}`, { status });
      const index = this.items.findIndex((item) => item.id === id);
      if (index !== -1) {
        this.items[index] = data.data;
      }
      return data.data;
    },
  },
});

import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { NotificationItem } from 'src/types/api';

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    items: [] as NotificationItem[],
    loading: false,
    lastFetchedAt: null as string | null,
  }),
  getters: {
    unreadCount: (state) => state.items.filter((item) => !item.read_at).length,
  },
  actions: {
    async fetchAll(params: { unread?: boolean } = {}) {
      this.loading = true;
      try {
        const { data } = await api.get<{ data: NotificationItem[] }>('/api/notifications', {
          params: {
            ...params,
            per_page: 100,
          },
        });
        this.items = data.data;
        this.lastFetchedAt = new Date().toISOString();
      } finally {
        this.loading = false;
      }
    },
    async markAsRead(id: string) {
      await api.post(`/api/notifications/${id}/read`);
      const item = this.items.find((notification) => notification.id === id);
      if (item) {
        item.read_at = new Date().toISOString();
      }
    },
    clear() {
      this.items = [];
      this.lastFetchedAt = null;
      this.loading = false;
    },
  },
});

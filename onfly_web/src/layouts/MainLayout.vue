<template>
  <q-layout view="hHh lpR fFf">
    <q-header class="onfly-header text-white">
      <div class="onfly-toolbar-wrap">
        <q-toolbar class="onfly-toolbar">
          <q-toolbar-title class="row items-center q-gutter-sm">
            <img src="/logo-onfly-branco.svg" alt="Onfly" class="onfly-logo" />
          </q-toolbar-title>

          <div class="row items-center q-gutter-sm">
            <q-chip v-if="authStore.isAdmin" color="white" text-color="primary" size="sm">
              Admin
            </q-chip>
            <div class="text-body2">{{ authStore.user?.name }}</div>
            <q-btn flat dense round icon="notifications">
              <q-badge
                v-if="notificationsStore.unreadCount"
                color="negative"
                floating
                :label="notificationsStore.unreadCount"
              />
              <q-menu class="onfly-notifications-menu" anchor="bottom right" self="top right">
                <q-list class="onfly-notifications-list">
                  <q-item>
                    <q-item-section>
                      <div class="text-subtitle2">Notificações</div>
                      <div class="text-caption text-grey-6">
                        {{ notificationsStore.unreadCount }} não lidas
                      </div>
                    </q-item-section>
                    <q-item-section side>
                      <q-btn
                        flat
                        dense
                        round
                        icon="refresh"
                        :loading="notificationsStore.loading"
                        @click.stop="refreshNotifications"
                      />
                    </q-item-section>
                  </q-item>
                  <q-separator />

                  <q-item v-if="!notificationsStore.items.length">
                    <q-item-section class="text-grey-7 text-caption">
                      Sem notificações
                    </q-item-section>
                  </q-item>

                  <q-item
                    v-for="notification in notificationsStore.items"
                    :key="notification.id"
                    clickable
                    class="onfly-notification-item"
                    @click="handleNotificationClick(notification)"
                  >
                    <q-item-section>
                      <div class="row items-center q-gutter-xs">
                        <q-icon name="flight" size="16px" class="text-primary" />
                        <div class="text-body2">
                          Pedido {{ notification.data.code || '—' }}
                        </div>
                        <q-badge
                          v-if="!notification.read_at"
                          color="primary"
                          label="Nova"
                        />
                      </div>
                      <div class="text-caption text-grey-7">
                        {{ notificationMessage(notification) }}
                      </div>
                      <div
                        v-if="notification.data?.destination"
                        class="text-caption text-grey-7"
                      >
                        {{ destinationLabel(notification) }}
                      </div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
            <q-btn flat dense round icon="logout" @click="handleLogout">
              <q-tooltip>Sair</q-tooltip>
            </q-btn>
          </div>
        </q-toolbar>
      </div>
    </q-header>

    <q-page-container>
      <div class="onfly-page-container">
        <router-view />
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';
import { useNotificationsStore } from 'src/stores/notifications';
import type { NotificationItem, TravelStatus } from 'src/types/api';

const router = useRouter();
const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();

const pollingId = ref<ReturnType<typeof setInterval> | null>(null);

function startPolling() {
  if (pollingId.value) return;
  pollingId.value = setInterval(() => {
    void notificationsStore.fetchAll();
  }, 15000);
}

function stopPolling() {
  if (!pollingId.value) return;
  clearInterval(pollingId.value);
  pollingId.value = null;
}

function refreshNotifications() {
  void notificationsStore.fetchAll();
}

function statusLabel(status?: TravelStatus) {
  return {
    requested: 'Solicitado',
    approved: 'Aprovado',
    canceled: 'Cancelado',
  }[status ?? 'requested'];
}

function notificationMessage(notification: NotificationItem) {
  const fromStatus = notification.data.from_status;
  const toStatus = notification.data.to_status;
  if (fromStatus && toStatus) {
    return `Status: ${statusLabel(fromStatus)} → ${statusLabel(toStatus)}`;
  }
  if (toStatus) {
    return `Status atualizado para ${statusLabel(toStatus)}.`;
  }
  return 'Atualização do pedido.';
}

function destinationLabel(notification: NotificationItem) {
  const destination = notification.data.destination;
  if (!destination) return '';
  const dates = notification.data.departure_date && notification.data.return_date
    ? ` • ${notification.data.departure_date} → ${notification.data.return_date}`
    : '';
  return `${destination.city}/${destination.state} (${destination.iata_code})${dates}`;
}

async function handleNotificationClick(notification: NotificationItem) {
  if (!notification.read_at) {
    await notificationsStore.markAsRead(notification.id);
  }
}

async function handleLogout() {
  await authStore.logout();
  await router.push('/login');
}

onMounted(() => {
  if (authStore.isAuthenticated) {
    void notificationsStore.fetchAll();
    startPolling();
  }
});

onUnmounted(() => {
  stopPolling();
});

watch(
  () => authStore.isAuthenticated,
  (isAuthenticated) => {
    if (isAuthenticated) {
      void notificationsStore.fetchAll();
      startPolling();
      return;
    }
    stopPolling();
    notificationsStore.clear();
  }
);
</script>

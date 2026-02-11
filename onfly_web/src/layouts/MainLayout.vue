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
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

async function handleLogout() {
  await authStore.logout();
  await router.push('/login');
}
</script>

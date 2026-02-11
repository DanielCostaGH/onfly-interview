<template>
  <q-page class="row items-center justify-center">
    <q-card class="q-pa-lg" style="width: 100%; max-width: 420px">
      <q-card-section>
        <div class="text-h6 text-weight-medium">Onfly Travel</div>
        <div class="text-caption text-grey-7">Entre para continuar</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="handleLogin" class="q-gutter-md">
          <q-input
            v-model="email"
            type="email"
            label="E-mail"
            outlined
            dense
            :rules="[(val) => !!val || 'E-mail obrigatório']"
          />
          <q-input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="Senha"
            outlined
            dense
            :rules="[(val) => !!val || 'Senha obrigatória']"
          >
            <template #append>
              <q-icon
                :name="showPassword ? 'visibility' : 'visibility_off'"
                class="cursor-pointer"
                @click="showPassword = !showPassword"
              />
            </template>
          </q-input>

          <q-btn
            label="Entrar"
            type="submit"
            color="primary"
            class="full-width"
            :loading="authStore.loading"
            unelevated
          />
        </q-form>
      </q-card-section>

      <q-separator />

      <q-card-section class="text-caption text-grey-7">
        <div class="text-weight-medium q-mb-xs">Credenciais</div>
        <div>Admin: maria@empresa.com / 123456</div>
        <div>Usuário: joao@empresa.com / 123456</div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { Notify } from 'quasar';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);

function extractMessage(error: unknown) {
  const err = error as { response?: { data?: { message?: string } } };
  return err?.response?.data?.message || 'Erro ao fazer login.';
}

async function handleLogin() {
  try {
    await authStore.login(email.value, password.value);
    Notify.create({ type: 'positive', message: 'Login realizado com sucesso.' });
    await router.push('/dashboard');
  } catch (error) {
    Notify.create({ type: 'negative', message: extractMessage(error) });
  }
}
</script>

<template>
  <q-page class="onfly-login-page row items-center justify-center">
    <q-card class="q-pa-lg onfly-login-card" style="width: 100%; max-width: 420px">
      <div class="onfly-login-brand q-mb-md">
        <div class="onfly-logo-badge">
          <img src="/logo-onfly-branco.svg" alt="Onfly" />
        </div>
        <div>
          <div class="text-h6 text-weight-medium">Onfly Travel</div>
          <div class="text-caption text-grey-8">Entre para continuar</div>
        </div>
      </div>
      <div class="text-caption text-grey-7 q-mb-xl">
        Viagens corporativas mais simples, do pedido à aprovação.
      </div>

      <q-card-section class="q-pt-none">
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

          <div v-if="isTemporarilyBlocked" class="text-caption text-negative">
            Limite de tentativas excedido. Tente novamente em {{ blockTimeLabel }}.
          </div>

          <div v-else-if="attemptsRemaining !== null" class="text-caption text-grey-8">
            Tentativas restantes: {{ attemptsRemaining }} de {{ attemptsLimit ?? 5 }}.
          </div>

          <q-btn
            label="Entrar"
            type="submit"
            color="primary"
            class="full-width"
            :loading="authStore.loading"
            :disable="isTemporarilyBlocked"
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
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Notify } from 'quasar';
import { useAuthStore } from 'src/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const attemptsRemaining = ref<number | null>(null);
const attemptsLimit = ref<number | null>(null);
const blockedUntil = ref<number | null>(null);
const nowMs = ref(Date.now());
let timer: ReturnType<typeof setInterval> | null = null;

type HeaderValue = string | number | string[] | undefined;
type ResponseHeaders = Record<string, HeaderValue>;
type ApiError = {
  response?: {
    status?: number;
    headers?: ResponseHeaders;
    data?: { message?: string; retry_after?: number | string };
  };
};

const blockSecondsRemaining = computed(() => {
  if (!blockedUntil.value) {
    return 0;
  }
  return Math.max(0, Math.ceil((blockedUntil.value - nowMs.value) / 1000));
});

const isTemporarilyBlocked = computed(() => blockSecondsRemaining.value > 0);

const blockTimeLabel = computed(() => {
  const totalSeconds = blockSecondsRemaining.value;
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

function getHeaderNumber(headers: ResponseHeaders, key: string): number | null {
  const raw = headers[key] ?? headers[key.toLowerCase()] ?? headers[key.toUpperCase()];
  const value = Array.isArray(raw) ? raw[0] : raw;
  if (value === undefined) {
    return null;
  }
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

function applyRateLimitFeedback(error: unknown) {
  const err = error as ApiError;
  const headers = err.response?.headers ?? {};
  const limit = getHeaderNumber(headers, 'x-ratelimit-limit');
  const remaining = getHeaderNumber(headers, 'x-ratelimit-remaining');
  const retryAfterHeader = getHeaderNumber(headers, 'retry-after');
  const retryAfterBodyRaw = err.response?.data?.retry_after;
  const retryAfterBody = retryAfterBodyRaw === undefined ? null : Number(retryAfterBodyRaw);
  const retryAfter = retryAfterHeader ?? (Number.isFinite(retryAfterBody) ? retryAfterBody : null);

  if (limit !== null) {
    attemptsLimit.value = limit;
  }

  if (remaining !== null) {
    attemptsRemaining.value = remaining;
  }

  if (err.response?.status === 429) {
    const lockSeconds = retryAfter ?? 300;
    blockedUntil.value = Date.now() + (lockSeconds * 1000);
    attemptsRemaining.value = 0;
  }
}

function extractMessage(error: unknown): string {
  const err = error as ApiError;
  if (err.response?.status === 429) {
    return `Muitas tentativas de login. Aguarde ${blockTimeLabel.value}.`;
  }
  return err?.response?.data?.message || 'Erro ao fazer login.';
}

async function handleLogin() {
  if (isTemporarilyBlocked.value) {
    Notify.create({
      type: 'warning',
      message: `Login temporariamente bloqueado. Aguarde ${blockTimeLabel.value}.`,
    });
    return;
  }

  try {
    await authStore.login(email.value, password.value);
    attemptsRemaining.value = null;
    attemptsLimit.value = null;
    blockedUntil.value = null;
    Notify.create({ type: 'positive', message: 'Login realizado com sucesso.' });
    await router.push('/dashboard');
  } catch (error) {
    applyRateLimitFeedback(error);
    Notify.create({ type: 'negative', message: extractMessage(error) });
  }
}

onMounted(() => {
  timer = setInterval(() => {
    nowMs.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (timer) {
    clearInterval(timer);
  }
});
</script>

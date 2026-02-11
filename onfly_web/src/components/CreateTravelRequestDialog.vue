<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    position="right"
  >
    <q-card class="onfly-side-panel">
      <q-card-section class="row items-center">
        <div class="text-h6">Nova Solicitação</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-form ref="formRef" @submit="handleSubmit" class="q-gutter-md">
          <q-select
            v-model="form.destination_airport_id"
            :options="airportOptions"
            label="Destino"
            outlined
            dense
            rounded
            class="onfly-field"
            emit-value
            map-options
            :loading="airportsStore.loading"
            :rules="[(val) => !!val || 'Selecione um destino.']"
          />

          <q-input
            v-model="rangeLabel"
            label="Período da viagem"
            outlined
            dense
            rounded
            class="onfly-field"
            readonly
            :rules="[validateRangeRequired, validateDepartureDate, validateReturnDate]"
          >
            <template #append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy transition-show="scale" transition-hide="scale">
                  <q-date v-model="dateRange" range mask="YYYY-MM-DD" />
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>

          <div class="row justify-end q-gutter-sm">
            <q-btn flat color="grey" label="Cancelar" v-close-popup />
            <q-btn
              color="primary"
              label="Criar"
              type="submit"
              :loading="submitting"
              unelevated
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Notify } from 'quasar';
import { useAirportsStore } from 'src/stores/airports';
import { useTravelRequestsStore } from 'src/stores/travelRequests';

defineProps<{ modelValue: boolean }>();
const emit = defineEmits<{ 'update:modelValue': [value: boolean]; created: [] }>();

const airportsStore = useAirportsStore();
const travelStore = useTravelRequestsStore();

const formRef = ref<{ validate: () => Promise<boolean> } | null>(null);

const form = ref({
  destination_airport_id: null as number | null,
});

const submitting = ref(false);

const airportOptions = computed(() =>
  airportsStore.items.map((airport) => ({
    label: `${airport.city} (${airport.iata_code})`,
    value: airport.id,
  }))
);

const dateRange = ref<{ from: string; to: string } | null>(null);

const rangeLabel = computed(() => {
  if (!dateRange.value) return '';
  return `${dateRange.value.from} → ${dateRange.value.to}`;
});

const today = computed(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
});

function validateRangeRequired() {
  if (!dateRange.value?.from || !dateRange.value?.to) {
    return 'Selecione data de partida e retorno.';
  }
  return true;
}

function validateDepartureDate() {
  if (!dateRange.value?.from) return true;
  if (dateRange.value.from < today.value) {
    return 'Data de partida não pode ser menor que a data de hoje.';
  }
  return true;
}

function validateReturnDate() {
  if (!dateRange.value?.from || !dateRange.value?.to) return true;
  if (dateRange.value.to < dateRange.value.from) {
    return 'Data de retorno não pode ser menor que a data de partida.';
  }
  return true;
}

async function handleSubmit() {
  const isValid = await formRef.value?.validate();
  if (!isValid) return;
  const destinationId = form.value.destination_airport_id;
  const range = dateRange.value;
  if (!destinationId || !range?.from || !range?.to) return;
  submitting.value = true;
  try {
    await travelStore.createRequest({
      destination_airport_id: destinationId,
      departure_date: range.from,
      return_date: range.to,
    });
    Notify.create({ type: 'positive', message: 'Pedido criado com sucesso.' });
    emit('update:modelValue', false);
    emit('created');
    form.value = { destination_airport_id: null };
    dateRange.value = null;
  } catch (error) {
    const err = error as { response?: { data?: { message?: string } } };
    const message = err?.response?.data?.message || 'Erro ao criar pedido.';
    Notify.create({ type: 'negative', message });
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  if (airportsStore.items.length === 0) {
    await airportsStore.fetchAll();
  }
});
</script>

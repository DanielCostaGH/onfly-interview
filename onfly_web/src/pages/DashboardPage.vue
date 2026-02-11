<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md onfly-dashboard-header">
      <div class="col-12 col-sm">
        <div class="text-h5">Pedidos de viagem</div>
        <div class="text-label text-bold text-grey-7">Gestão de solicitações corporativas</div>
      </div>
      <div class="col-12 col-sm-auto">
        <q-btn
          color="primary"
          label="Nova Solicitação"
          icon="add"
          unelevated
          class="onfly-new-request"
          @click="showCreate = true"
        />
      </div>
    </div>

    <q-card flat bordered class="q-mb-md onfly-glass-card">
      <q-expansion-item
        icon="filter_list"
        label="Filtros"
        default-opened
        expand-separator
      >
        <q-card-section>
          <div class="row q-col-gutter-md items-center">
            <div class="col-12 col-md-3">
              <q-select
                v-model="filters.status"
                :options="statusOptions"
                label="Status"
                outlined
                dense
                rounded
                clearable
                emit-value
                map-options
                class="onfly-field"
              >
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <div class="onfly-chip-wrap">
                        <q-chip
                          :color="scope.opt.color"
                          text-color="white"
                          class="onfly-status-chip"
                        >
                          {{ scope.opt.label }}
                        </q-chip>
                      </div>
                    </q-item-section>
                  </q-item>
                </template>
                <template #selected-item="scope">
                  <q-chip
                    :color="scope.opt.color"
                    text-color="white"
                    class="onfly-status-chip"
                  >
                    {{ scope.opt.label }}
                  </q-chip>
                </template>
              </q-select>
            </div>
            <div class="col-12 col-md-3">
              <q-select
                v-model="filters.destination_airport_id"
                :options="filteredAirportOptions"
                label="Destino"
                outlined
                dense
                rounded
                clearable
                emit-value
                map-options
                use-input
                input-debounce="150"
                @filter="filterAirportOptions"
                class="onfly-field"
              />
            </div>
            <div class="col-12 col-md-2">
              <q-input
                v-model="filters.travel_from"
                label="Viagem de"
                outlined
                dense
                rounded
                readonly
                clearable
                class="onfly-field"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-date v-model="filters.travel_from" mask="YYYY-MM-DD" />
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-2">
              <q-input
                v-model="filters.travel_to"
                label="Viagem até"
                outlined
                dense
                rounded
                readonly
                clearable
                class="onfly-field"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-date v-model="filters.travel_to" mask="YYYY-MM-DD" />
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-2 row items-center justify-end q-gutter-sm">
              <q-btn
                label="Aplicar"
                color="primary"
                unelevated
                rounded
                size="sm"
                @click="fetchRequests"
              />
              <q-btn flat rounded size="sm" icon="cleaning_services" @click="resetFilters">
                <q-tooltip>Limpar</q-tooltip>
              </q-btn>
            </div>
          </div>
        </q-card-section>
      </q-expansion-item>
    </q-card>

    <q-card flat bordered class="onfly-glass-card">
      <q-table
        :rows="travelStore.items"
        :columns="columns"
        row-key="id"
        :loading="travelStore.loading"
        flat
        class="onfly-table"
      >
        <template #body-cell-code="props">
          <q-td :props="props">
            <div class="text-weight-medium">{{ props.row.code }}</div>
          </q-td>
        </template>

        <template #body-cell-user="props">
          <q-td :props="props">
            <div>{{ props.row.user?.name || '-' }}</div>
            <div class="text-caption text-grey-7">{{ props.row.user?.email || '' }}</div>
          </q-td>
        </template>

        <template #body-cell-destination="props">
          <q-td :props="props">
            <div>{{ props.row.destination_airport?.city || '-' }}</div>
            <div class="text-caption text-grey-7">{{ props.row.destination_airport?.iata_code || '' }}</div>
          </q-td>
        </template>

        <template #body-cell-dates="props">
          <q-td :props="props">
            <div>{{ props.row.departure_date }}</div>
            <div class="text-caption text-grey-7">até {{ props.row.return_date }}</div>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props">
            <q-badge
              :color="statusColor(props.row.status)"
              :label="statusLabel(props.row.status)"
              class="onfly-status-badge"
            />
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td :props="props">
            <div v-if="canChangeStatus(props.row)" class="q-gutter-xs">
              <q-btn
                flat
                dense
                round
                color="positive"
                icon="check"
                :loading="statusLoadingId === props.row.id && statusLoadingAction === 'approved'"
                @click="updateStatus(props.row.id, 'approved')"
              />
              <q-btn
                flat
                dense
                round
                color="negative"
                icon="close"
                :loading="statusLoadingId === props.row.id && statusLoadingAction === 'canceled'"
                @click="updateStatus(props.row.id, 'canceled')"
              />
            </div>
            <div v-else class="text-caption text-grey-6">-</div>
          </q-td>
        </template>

        <template #no-data>
          <div class="full-width row flex-center text-grey-7 q-pa-md">
            Nenhum pedido encontrado
          </div>
        </template>
      </q-table>
    </q-card>

    <create-travel-request-dialog v-model="showCreate" @created="fetchRequests" />
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Notify } from 'quasar';
import CreateTravelRequestDialog from 'src/components/CreateTravelRequestDialog.vue';
import { useAirportsStore } from 'src/stores/airports';
import { useAuthStore } from 'src/stores/auth';
import { useTravelRequestsStore } from 'src/stores/travelRequests';
import type { TravelStatus } from 'src/types/api';

const authStore = useAuthStore();
const travelStore = useTravelRequestsStore();
const airportsStore = useAirportsStore();

const showCreate = ref(false);
const statusLoadingId = ref<number | null>(null);
const statusLoadingAction = ref<TravelStatus | null>(null);

const filters = reactive({
  status: null as TravelStatus | null,
  destination_airport_id: null as number | null,
  travel_from: null as string | null,
  travel_to: null as string | null,
});

const columns = [
  { name: 'code', label: 'Código', field: 'code', align: 'left' as const },
  { name: 'user', label: 'Solicitante', field: 'user', align: 'left' as const },
  { name: 'destination', label: 'Destino', field: 'destination', align: 'left' as const },
  { name: 'dates', label: 'Período', field: 'dates', align: 'left' as const },
  { name: 'status', label: 'Status', field: 'status', align: 'left' as const },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'center' as const },
];

const statusOptions: Array<{ label: string; value: TravelStatus; color: string }> = [
  { label: 'Solicitado', value: 'requested', color: 'orange' },
  { label: 'Aprovado', value: 'approved', color: 'positive' },
  { label: 'Cancelado', value: 'canceled', color: 'negative' },
];

const airportOptions = computed(() =>
  airportsStore.items.map((airport) => ({
    label: `${airport.city} (${airport.iata_code})`,
    value: airport.id,
  }))
);

const filteredAirportOptions = ref(airportOptions.value);

watch(airportOptions, (options) => {
  filteredAirportOptions.value = options;
});

function statusLabel(status: TravelStatus) {
  return {
    requested: 'Solicitado',
    approved: 'Aprovado',
    canceled: 'Cancelado',
  }[status];
}

function statusColor(status: TravelStatus) {
  return {
    requested: 'orange',
    approved: 'positive',
    canceled: 'negative',
  }[status];
}

function filterAirportOptions(val: string, update: (fn: () => void) => void) {
  update(() => {
    if (!val) {
      filteredAirportOptions.value = airportOptions.value;
      return;
    }
    const needle = val.toLowerCase();
    filteredAirportOptions.value = airportOptions.value.filter((option) =>
      option.label.toLowerCase().includes(needle)
    );
  });
}

function canChangeStatus(row: { status: TravelStatus }) {
  return authStore.isAdmin && row.status === 'requested';
}

async function fetchRequests() {
  try {
    await travelStore.fetchAll(filters);
  } catch (error) {
    const err = error as { response?: { data?: { message?: string } } };
    const message = err?.response?.data?.message || 'Erro ao carregar pedidos.';
    Notify.create({ type: 'negative', message });
  }
}

function resetFilters() {
  filters.status = null;
  filters.destination_airport_id = null;
  filters.travel_from = null;
  filters.travel_to = null;
  void fetchRequests();
}

async function updateStatus(id: number, status: TravelStatus) {
  statusLoadingId.value = id;
  statusLoadingAction.value = status;
  try {
    await travelStore.updateStatus(id, status);
    Notify.create({ type: 'positive', message: 'Status atualizado.' });
  } catch (error) {
    const err = error as { response?: { data?: { message?: string } } };
    const message = err?.response?.data?.message || 'Erro ao atualizar status.';
    Notify.create({ type: 'negative', message });
  } finally {
    statusLoadingId.value = null;
    statusLoadingAction.value = null;
  }
}

onMounted(async () => {
  await airportsStore.fetchAll();
  await fetchRequests();
});
</script>

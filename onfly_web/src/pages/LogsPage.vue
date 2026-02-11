<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md onfly-dashboard-header">
      <div class="col-12 col-sm">
        <div class="text-h5">Logs</div>
        <div class="text-label text-bold text-grey-7">Histórico de alterações</div>
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
            <div class="col-12 col-md-2">
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
                        <q-chip :color="scope.opt.color" text-color="white" class="onfly-status-chip">
                          {{ scope.opt.label }}
                        </q-chip>
                      </div>
                    </q-item-section>
                  </q-item>
                </template>
                <template #selected-item="scope">
                  <q-chip :color="scope.opt.color" text-color="white" class="onfly-status-chip">
                    {{ scope.opt.label }}
                  </q-chip>
                </template>
              </q-select>
            </div>
            <div class="col-12 col-md-3">
              <q-input
                v-model="filters.code"
                label="Código do pedido"
                outlined
                dense
                rounded
                clearable
                class="onfly-field"
              />
            </div>
            <div class="col-12 col-md-1">
              <q-input
                v-model.number="filters.user_id"
                type="number"
                label="User ID"
                outlined
                dense
                rounded
                clearable
                class="onfly-field"
              />
            </div>
            <div class="col-12 col-md-2">
              <q-input
                v-model="filters.created_from"
                label="Data de"
                outlined
                dense
                rounded
                readonly
                clearable
                :display-value="formatDate(filters.created_from)"
                class="onfly-field"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-date v-model="filters.created_from" mask="YYYY-MM-DD" />
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-2">
              <q-input
                v-model="filters.created_to"
                label="Data até"
                outlined
                dense
                rounded
                readonly
                clearable
                :display-value="formatDate(filters.created_to)"
                class="onfly-field"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-date v-model="filters.created_to" mask="YYYY-MM-DD" />
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
                @click="fetchLogs"
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
        :rows="logsStore.items"
        :columns="columns"
        row-key="id"
        :loading="logsStore.loading"
        flat
        class="onfly-table"
      >
        <template #body-cell-code="props">
          <q-td :props="props">
            <div class="text-weight-medium">{{ props.row.travel_request_code || '-' }}</div>
            <div class="text-caption text-grey-7">ID {{ props.row.travel_request_id }}</div>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-xs">
              <q-badge
                v-if="props.row.from_status"
                :color="statusColor(props.row.from_status)"
                :label="statusLabel(props.row.from_status)"
              />
              <q-icon name="east" size="14px" class="text-grey-6" />
              <q-badge
                v-if="props.row.to_status"
                :color="statusColor(props.row.to_status)"
                :label="statusLabel(props.row.to_status)"
              />
            </div>
          </q-td>
        </template>

        <template #body-cell-user="props">
          <q-td :props="props">
            <div>{{ props.row.changed_by?.name || '-' }}</div>
            <div class="text-caption text-grey-7">{{ props.row.changed_by?.email || '' }}</div>
          </q-td>
        </template>

        <template #body-cell-date="props">
          <q-td :props="props">
            <div>{{ formatDateTime(props.row.created_at) }}</div>
          </q-td>
        </template>

        <template #no-data>
          <div class="full-width row flex-center text-grey-7 q-pa-md">
            Nenhum log encontrado
          </div>
        </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive } from 'vue';
import { Notify } from 'quasar';
import { useLogsStore } from 'src/stores/logs';
import type { TravelStatus } from 'src/types/api';
import { formatDate, formatDateTime } from 'src/utils/date';

const logsStore = useLogsStore();

const filters = reactive({
  status: null as TravelStatus | null,
  user_id: null as number | null,
  code: null as string | null,
  created_from: null as string | null,
  created_to: null as string | null,
});

const columns = [
  { name: 'code', label: 'Pedido', field: 'code', align: 'left' as const },
  { name: 'status', label: 'Status', field: 'status', align: 'left' as const },
  { name: 'user', label: 'Alterado por', field: 'user', align: 'left' as const },
  { name: 'date', label: 'Data', field: 'date', align: 'left' as const },
];

const statusOptions: Array<{ label: string; value: TravelStatus; color: string }> = [
  { label: 'Solicitado', value: 'requested', color: 'orange' },
  { label: 'Aprovado', value: 'approved', color: 'positive' },
  { label: 'Cancelado', value: 'canceled', color: 'negative' },
];

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

async function fetchLogs() {
  try {
    await logsStore.fetchAll(filters);
  } catch (error) {
    const err = error as { response?: { data?: { message?: string } } };
    const message = err?.response?.data?.message || 'Erro ao carregar logs.';
    Notify.create({ type: 'negative', message });
  }
}

function resetFilters() {
  filters.status = null;
  filters.user_id = null;
  filters.code = null;
  filters.created_from = null;
  filters.created_to = null;
  void fetchLogs();
}

onMounted(async () => {
  await fetchLogs();
});
</script>

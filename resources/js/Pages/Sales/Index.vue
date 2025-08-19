<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

// Props vindas do controller
defineProps({
  sales: Object,    // paginator (sales.data, links, etc.)
  sellers: Array,   // [{id, name}]
  statuses: Array,  // ['pending','paid','canceled']
})

// Helpers
const money = (v) => {
  const n = Number(v ?? 0)
  return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
const pct = (v) => `${Number(v ?? 0).toFixed(2)}%`
const formatedDate = (date) => date ? new Date(date)?.toLocaleDateString('pt-BR') : ''

// Lê filtros atuais da URL
const params = new URLSearchParams(window.location.search)
const get = (k, d = '') => (params.has(k) ? params.get(k) : d)

const searchRef = ref(get('search', ''))
const statusRef = ref(get('status', ''))       // '', 'pending', 'paid', 'canceled'
const sellerRef = ref(get('seller', ''))       // user_id
const fromRef = ref(get('from', ''))           // yyyy-mm-dd
const toRef = ref(get('to', ''))               // yyyy-mm-dd

// Mapeia status para Tag do PrimeVue
const statusLabel = (s) => (s === 'paid' ? 'Pago' : s === 'pending' ? 'Pendente' : 'Cancelado')
const statusSeverity = (s) => (s === 'paid' ? 'success' : s === 'pending' ? 'warning' : 'danger')

// Normaliza datas para yyyy-mm-dd (Calendar pode retornar Date)
const asYmd = (v) => {
  if (!v) return ''
  if (typeof v === 'string') return v
  try { return new Date(v).toISOString().slice(0, 10) } catch { return '' }
}

const loadSales = () => {
  router.get(
    route('sales.index'),
    {
      search: searchRef.value || '',
      status: statusRef.value || '',
      seller: sellerRef.value || '',
      from: asYmd(fromRef.value),
      to: asYmd(toRef.value),
    },
    { preserveState: true }
  )
}

const importForm = useForm({ file: null });


function handleImportFileChange(e) {
  const input = e.target;
  const file = input?.files?.[0];
  if (!file) return;
  importForm.file = file;
  importForm.post(route('sales.import'), {
    forceFormData: true,
    onSuccess: () => {
      const message = usePage().props.jetstream?.flash?.message || 'Importação concluída';
      const type = usePage().props.jetstream?.flash?.type || 'success';
      toast.add({ severity: type, summary: 'Importação', detail: message, life: 4000 });
      if (input) input.value = '';
      importForm.reset();
    }
  });
}

// + abrir seletor
function openImportPicker() {
  document.getElementById('sales-import-input')?.click();
}
</script>

<template>
  <AppLayout title="Vendas">
    <div class="grid">
      <div class="col-12">
        <div class="card">
          <DataTable
            :value="sales.data"
            tableStyle="min-width: 70rem;"
          >
            <!-- Header com título e filtros no mesmo estilo -->
            <template #header>
              <div class="mb-4 flex justify-content-between align-items-center">
                <h1 class="text-4xl font-bold text-gray-800">Vendas</h1>

                <div>
                    <Button class="mr-2 mr-2" @click="openImportPicker">
                        Importar vendas (.xlsx)
                    </Button>

                    <Link class="p-button p-component p-button-primary" :href="route('sales.create')">
                      <span class="p-button-icon pi pi-plus mr-2"></span>
                      <span class="p-button-label">Nova Venda</span>
                    </Link>
    
                    <input
                        id="sales-import-input"
                        type="file"
                        accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                        class="hidden"
                        @change="handleImportFileChange"
                        />
                </div>
              </div>

              <div class="flex flex-column gap-3 md:flex-row md:justify-content-between">
                <!-- Busca -->
                <IconField iconPosition="left" class="w-full md:w-30rem">
                  <InputIcon class="pi pi-search" />
                  <InputText
                    v-model="searchRef"
                    placeholder="Nome, CPF, Proposta, Produto, Banco..."
                    @keyup.enter.native="loadSales()"
                    class="w-full"
                  />
                </IconField>

                <!-- Filtros à direita -->
                <div class="flex gap-3 w-full md:w-auto">
                  <Dropdown
                    v-model="sellerRef"
                    :options="sellers"
                    optionLabel="name"
                    optionValue="id"
                    showClear
                    placeholder="Vendedor"
                    class="w-14rem"
                    @change="loadSales()"
                  />

                  <Dropdown
                    v-model="statusRef"
                    :options="[
                      { name: 'Todos', value: '' },
                      { name: 'Pendentes', value: 'pending' },
                      { name: 'Pagos', value: 'paid' },
                      { name: 'Cancelados', value: 'canceled' },
                    ]"
                    optionLabel="name"
                    optionValue="value"
                    showClear
                    placeholder="Status"
                    class="w-12rem"
                    @change="loadSales()"
                  />

                  <Calendar
                    v-model="fromRef"
                    dateFormat="yy-mm-dd"
                    showIcon
                    placeholder="De (YYYY-MM-DD)"
                    class="w-13rem"
                    @date-select="loadSales()"
                    @clear-click="loadSales()"
                  />
                  <Calendar
                    v-model="toRef"
                    dateFormat="yy-mm-dd"
                    showIcon
                    placeholder="Até (YYYY-MM-DD)"
                    class="w-13rem"
                    @date-select="loadSales()"
                    @clear-click="loadSales()"
                  />

                  <Button class="p-button-success" @click="loadSales()">
                    <span class="pi pi-filter mr-2" /> Filtrar
                  </Button>
                </div>
              </div>
            </template>

            <!-- Colunas -->
            <Column field="id" header="#" style="width: 80px" />
            <Column header="Data">
              <template #body="{ data }">
                {{ formatedDate(data.sale_date) || '-' }}
              </template>
            </Column>
            <Column field="proposal_number" header="Proposta" />
            <Column field="name" header="Cliente" />
            <Column field="cpf" header="CPF" />
            <Column field="product" header="Produto" />
            <Column field="bank" header="Banco" />
            <Column header="Valor">
              <template #body="{ data }">
                {{ money(data.amount) }}
              </template>
            </Column>
            <Column header="%">
              <template #body="{ data }">
                {{ pct(data.commission_percentage) }}
              </template>
            </Column>
            <Column header="Comissão">
              <template #body="{ data }">
                {{ money(data.commission_value) }}
              </template>
            </Column>
            <Column header="Status">
              <template #body="{ data }">
                <Tag :severity="statusSeverity(data.payment_status)">
                  {{ statusLabel(data.payment_status) }}
                </Tag>
              </template>
            </Column>
            <Column header="Vendedor">
              <template #body="{ data }">
                {{ data.user?.name ?? '-' }}
              </template>
            </Column>
          </DataTable>

          <!-- Paginação no mesmo padrão -->
          <Pagination :data="sales" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

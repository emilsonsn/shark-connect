<script setup>
import { reactive, watch, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { PrimeIcons } from 'primevue/api';
import AppLayout from '@/Layouts/AppLayout.vue'
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  sellers: Array,
  clients: Array,
  defaults: Object,
});

const clientSuggestions = ref([]);
const selectedClient = ref(null);

const form = useForm({
  user_id: '',
  client_id: '',

  name: '',
  cpf: '',
  proposal_number: '',
  amount: '',
  product: '',
  bank: '',
  commission_percentage: '',
  commission_value: '',
  payment_status: props.defaults?.payment_status ?? 'pending',

  sale_date: props.defaults?.sale_date ?? '',
  paid_at: '',
});

// Calcula comissão no front
watch(
  () => [form.amount, form.commission_percentage],
  () => {
    const amount = Number(String(form.amount).replace(',', '.')) || 0;
    const pct = Number(String(form.commission_percentage).replace(',', '.')) || 0;
    form.commission_value = (amount * (pct / 100)).toFixed(2);
  }
);

async function searchClients(e) {
  const q = (e?.query || '').trim();
  if (q.length < 2) {
    clientSuggestions.value = [];
    return;
  }
  const res = await fetch(route('clients.search', { q }));
  clientSuggestions.value = await res.json();
}

function onClientSelect(e) {
  const c = e.value;
  form.client_id = c?.id || '';
  form.name = c?.name || '';
  form.cpf = c?.cpf || '';
}

function submit() {
  form.post(route('sales.store'), {
    onSuccess: () => {
      // redirecionamento padrão pelo controller (flash message)
    },
  });
}
</script>

<template>
  <AppLayout title="Nova Venda">
    <div class="grid">
      <div class="col-12">
        <div class="card">
          <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
              Nova Venda
            </h1>
          </div>

          <form @submit.prevent="submit">
            <div class="grid gap-4 mb-4">
              <!-- Coluna 1 -->
              <div class="rounded dark:bg-gray-800 col">
                <div class="mt-4">
                    <InputLabel for="client" value="Cliente *" />
                    <AutoComplete
                        id="client"
                        v-model="selectedClient"
                        :suggestions="clientSuggestions"
                        field="name"
                        class="mt-1"
                        style="width: 100% !important;"
                        placeholder="Digite para buscar..."
                        forceSelection
                        @complete="searchClients"
                        @item-select="onClientSelect"
                    />
                    <InputError class="mt-2" :message="form.errors.client_id" />
                </div>

                <div class="mt-4">
                  <InputLabel for="name" value="Nome do Cliente *" />
                  <InputText
                    id="name"
                    readonly
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                  <InputLabel for="cpf" value="CPF *" />
                  <InputText
                    id="cpf"
                    readonly
                    v-model="form.cpf"
                    type="text"
                    placeholder="000.000.000-00"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.cpf" />
                </div>

                <div class="mt-4">
                  <InputLabel for="sale_date" value="Data da Venda" />
                  <InputText
                    id="sale_date"
                    v-model="form.sale_date"
                    type="date"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.sale_date" />
                </div>                

                <div class="mt-4">
                  <InputLabel for="proposal_number" value="Nº Proposta *" />
                  <InputText
                    id="proposal_number"
                    v-model="form.proposal_number"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.proposal_number" />
                </div>
              </div>

              <!-- Coluna 2 -->
              <div class="rounded dark:bg-gray-800 col">
                <div class="mt-4">
                  <InputLabel for="product" value="Produto *" />
                  <InputText
                    id="product"
                    v-model="form.product"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.product" />
                </div>

                <div class="mt-4">
                  <InputLabel for="bank" value="Banco *" />
                  <InputText
                    id="bank"
                    v-model="form.bank"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.bank" />
                </div>                
                <div class="mt-4">
                  <InputLabel for="amount" value="Valor (R$) *" />
                  <InputText
                    id="amount"
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.amount" />
                </div>

                <div class="mt-4">
                  <InputLabel for="commission_percentage" value="% Comissão *" />
                  <InputText
                    id="commission_percentage"
                    v-model="form.commission_percentage"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.commission_percentage" />
                </div>

                <div class="mt-4">
                  <InputLabel for="commission_value" value="Valor da Comissão (auto)" />
                  <InputText
                    id="commission_value"
                    v-model="form.commission_value"
                    type="text"
                    class="mt-1 block w-full"
                    :readonly="true"
                  />
                  <InputError class="mt-2" :message="form.errors.commission_value" />
                </div>

                <div class="mt-4">
                  <InputLabel for="payment_status" value="Status *" />
                  <Dropdown
                    id="payment_status"
                    v-model="form.payment_status"
                    :options="[
                      { label: 'Pendente', value: 'pending' },
                      { label: 'Pago', value: 'paid' },
                      { label: 'Cancelado', value: 'canceled' }
                    ]"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Selecione"
                    class="w-full mt-1"
                  />
                  <InputError class="mt-2" :message="form.errors.payment_status" />
                </div>

                <div class="mt-4" v-if="form.payment_status === 'paid'">
                  <InputLabel for="paid_at" value="Pago em" />
                  <InputText
                    id="paid_at"
                    v-model="form.paid_at"
                    type="datetime-local"
                    class="mt-1 block w-full"
                  />
                  <InputError class="mt-2" :message="form.errors.paid_at" />
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end mt-4 gap-2">
              <Button :disabled="form.processing" type="submit">
                Salvar
              </Button>
              <a class="btn btn-outline-secondary" :href="route('sales.index')">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
    subordinates: {
        type: [Object, Array],
    },
});

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const loadDashboard = () => {
    router.get(route('dashboard'), { search: searchRef.value }, { preserveState: true });
}

</script>

<template>
    <AppLayout title="Dashboard">

        <div class="grid">
            <div class="col-12 lg:col-6 xl:col-3">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Mais consumidos no 
                                <span class="text-primary-500 font-medium">mês</span>
                            </span>
                            
                            <div class="text-900 font-medium text-xl">152</div>
                        </div>
                        <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                            <i class="pi pi-user text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <span class="text-primary-500 font-medium">Usuário: </span>
                    <span class="text-500">Grupo</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Mais consumidos 
                                <span class="text-primary-500 font-medium">hoje</span>
                            </span>
                            <div class="text-900 font-medium text-xl">152</div>
                        </div>
                        <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                            <i class="pi pi-user text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <span class="text-primary-500 font-medium">Usuário: </span>
                    <span class="text-500">Grupo</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Campanha mais consumida do 
                                <span class="text-primary-500 font-medium">mês</span>
                            </span>
                            <div class="text-900 font-medium text-xl">152</div>
                        </div>

                    </div>
                    <span class="text-500">ESTADO DE SAO PAULO</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Campanha mais consumida
                                <span class="text-primary-500 font-medium">hoje</span>
                            </span>
                            <div class="text-900 font-medium text-xl">152</div>
                        </div>

                    </div>
                    <span class="text-500">GOV DO RIO DE JANEIRO</span>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <DataTable 
                        :value="subordinates.data" 
                        :rowHover="true" 
                        tableStyle="min-width: 50rem"
                    >
                        <template #header>
                            <div class="flex flex-column flex-wrap gap-2">
                                <span class="text-xl text-900 font-bold">Relatório do dia</span>
                                <div class="flex gap-2">
                                    <IconField iconPosition="left">
                                        <InputIcon class="pi pi-search"> </InputIcon>
                                        <InputText
                                            v-model="searchRef"
                                            placeholder="Nome, grupo ..."
                                            @keyup.enter.native="loadDashboard()"
                                        />
                                    </IconField>
                                    <Button @click="loadDashboard()">Pesquisar</Button>
                                </div>
                            </div>
                        </template>
                        <Column field="id" header="ID"></Column>
                        <Column field="name" header="Name"></Column>
                        <Column v-if="$page.props.permissions.includes('list-count-leads-taken-by-all')" field="group_name" header="Grupo"></Column>
                        <Column field="leads_taken_today" header="Consumidos"></Column>
                        <Column header="Ações">
                            <template #body="slotProps">
                                <Link :href="route('dashboard.campanhasByUser', {userId: slotProps.data.id})">
                                    <Button label="Ver" class="mr-2 mb-2"></Button>
                                </Link>
                            </template>
                        </Column>
                    </DataTable>

                    <Pagination :data="subordinates"></Pagination>

                </div>
            </div>
            
        </div>
    </AppLayout>
</template>
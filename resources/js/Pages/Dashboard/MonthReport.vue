<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue'
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    report: {
        type: Object,
    },
    groups: {
        type: [Array, Object],
    }
});

const page = usePage()

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const currentDate = new Date().toJSON().slice(0,10)

const inicio = params.has('inicio') ? params.get('inicio') : currentDate
const inicioRef = ref(inicio)

const fim = params.has('fim') ? params.get('fim') : currentDate
const fimRef = ref(fim)

const group = params.has('group') ? params.get('group') : defaultValue
const groupRef = ref(group)

const loadDashboard = () => {

    let dados = {
        search: searchRef.value,
        inicio: inicioRef.value,
        fim: fimRef.value
    }

    // Se o usuário tiver permissão para gerenciar grupos, ele pode filtrar por grupos
    if (page.props.permissions.includes('manage-users')) {
        dados.group = groupRef.value
    }

    router.get(route('dashboard.relatorio.mensal'), dados, { preserveState: true });
}

</script>

<template>
    <AppLayout title="Dashboard">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <DataTable :value="report.data">

                        <template #header>
                            <div class="mb-4">
                                <h1 class="text-4xl font-bold text-gray-800">
                                    Relatorio geral por data
                                </h1>
                            </div>


                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search"> </InputIcon>
                                <InputText
                                    v-model="searchRef"
                                    placeholder="Login..."
                                    @keyup.enter.native="loadDashboard()"
                                />
                            </IconField>
                     

                            <div class="flex justify-content-between mt-4">

                                <div v-if="$page.props.permissions.includes('manage-users')" class="my-2">
                                    <InputLabel for="grupo" value="Grupo" class="mb-2"/>
                                    <Dropdown 
                                        v-model="groupRef" 
                                        :options="groups" 
                                        optionLabel="id"
                                        optionValue="name"
                                        class="w-full md:w-14rem mb-4"
                                        @change="loadDashboard()"
                                    />
                                </div>

                                <div class="flex gap-4 justify-content-end">

                                    <div>
                                        <InputLabel for="inicio" value="Inicio" />
                                        <InputText
                                            v-model="inicioRef"
                                            id="inicio" 
                                            type="date" 
                                            class="mt-1 block w-full"
                                            @change="loadDashboard"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel for="fim" value="Fim" />
                                        <InputText
                                            v-model="fimRef"
                                            id="fim" 
                                            type="date" 
                                            class="mt-1 block w-full"
                                            @change="loadDashboard"
                                        />
                                    </div>

                                </div>

                            </div>

                        </template>
                        <Column field="login" header="Login"></Column>
                        <Column field="group_name" header="Grupo"></Column>
                        <Column field="total" header="Pegos"></Column>
                        <Column header="Ações">
                            <template #body="slotProps">
                                <Link :href="route('dashboard.campanhasByUser', { userId: slotProps.data.id, inicio: inicioRef, fim: fimRef })">
                                    <Button>
                                        Ver
                                    </Button>
                                </Link>
                            </template>
                        </Column>
                    </DataTable>

                    <Pagination :data="report"></Pagination>
                </div>

            </div>
                    
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import CreateCampaingModal from './Partials/CreateCampaignModal.vue';
import { useToast } from 'primevue/usetoast';

defineProps({
    campaigns: Object,
});

const createModal = ref(false);

const params = new URLSearchParams(window.location.search)
const defaultValue = 'active'

const search = params.has('search') ? params.get('search') : ""
const searchRef = ref(search)

const status = params.has('status') ? params.get('status') : defaultValue
const statusRef = ref(status)

const statusList = ref([
    { name: 'Ativas', value: 'active' },
    { name: 'Inativas', value: 'inactive' },
    { name: 'Todos', value: 'all' },
]);

const openOrCloseCreateModal = () => {
    createModal.value = !createModal.value;
}

const formatedDate = (date) => {
    return date ? new Date(date)?.toLocaleString("pt-BR"): "";
}

const activateDeactivate = (campaign) => {
    if (confirm("Tem certeza que deseja " + (campaign.status ? "desativar" : "ativar") + " esta campanha?")) {
        
        const rota = campaign.status ? 'lead-distribution.deactivate' : 'lead-distribution.activate';

        router.patch(route(rota, { leadDistributionCampaign: campaign.id }), {
            onSuccess: () => {
                alert('Campanha atualizada com sucesso!');
            },
        });
    }
}

const loadCampaignList = () => {
    router.get(route('lead-distribution.index'), { 
        status: statusRef.value,
        search: searchRef.value 
    }, { preserveState: true });
}

const toast = useToast();

const recycleCampaign = (campaign) => {
    if (confirm("Tem certeza que deseja reciclar esta campanha?")) {

        router.patch(route('lead-distribution.recycle', { leadDistributionCampaign: campaign.id }), {}, {
            onSuccess: () => {
                const message = usePage().props.jetstream.flash?.message || '';
                const style = usePage().props.jetstream.flash?.type || 'success';
                toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
            }
        });
    }
}

function cancelar(campaignId){
    router.post(route('lead-distribution.cancel'), {
        campaignId: campaignId
    }, {
        onSuccess: () => {
            alert('Campanha cancelada com sucesso!');
        },
        onError: (error) => {
            console.log(error)
        }
    })
    
}

const expandedRows = ref([]);

</script>

<template>
    <AppLayout title="Campanhas">

    <div class="grid">
        <div class="col-12">
            <div class="card">

                <CreateCampaingModal v-model:visible="createModal" @close="openOrCloseCreateModal" />

                <DataTable 
                    :value="campaigns.data" 
                    tableStyle="min-width: 50rem"
                    v-model:expandedRows="expandedRows"
                >
                    <template #header>
                        <div class="mb-4 flex justify-content-between">
                            <h1 class="text-4xl font-bold text-gray-800">
                                Campanhas
                            </h1>
                            <div class="flex-col flex gap-2">
                                <Button @click="openOrCloseCreateModal">Criar</Button>
                                
                            </div>
                        </div>

                        <div class="flex justify-content-between">
                            
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search"> </InputIcon>
                                <InputText
                                    v-model="searchRef"
                                    placeholder="Nome da campanha"
                                    @keyup.enter.native="loadCampaignList()"
                                />
                            </IconField>

                            <Dropdown 
                                v-model="statusRef" 
                                :options="statusList" 
                                optionLabel="name"
                                optionValue="value"
                                class="w-full md:w-14rem"
                                @change="loadCampaignList()"
                            />
                            
                        </div>
                    </template>
                    <Column :expander="true" headerStyle="width: 3rem" />
                    <Column field="name" header="Name"></Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.status" class="text-green-500">Ativa</span>
                            <span v-else class="text-red-500">Inativa</span>
                        </template>
                    </Column>
                    <Column header="Criado em">
                        <template #body="slotProps">
                            <p class="text-sm text-black">
                                {{ formatedDate(slotProps.data.created_at)  ?? ""}}
                            </p>
                        </template>
                    </Column>
                    <Column header="Total">
                        <template #body="slotProps">
                            <p class="text-sm text-black">
                                {{ slotProps.data.total ?? "0" }}
                            </p>
                        </template>
                    </Column>
                    <Column header="Restantes">
                        <template #body="slotProps">
                            <p class="text-sm text-black">
                                {{ slotProps.data.remaining  ?? "0" }}
                            </p>
                        </template>
                    </Column>
                    <Column header="Ações" class="flex gap-1">
                        <template #body="slotProps">
                            <Button
                                v-if="slotProps.data.progress != null && slotProps.data.progress.status == 'Processando...' && slotProps.data.progress.progress != 100" 
                                icon="pi pi-times"
                                severity="danger"
                                rounded
                                @click="cancelar(slotProps.data.id)"
                            />

                            <Link :href="route('lead-distribution.edit', { leadDistributionCampaign: slotProps.data.id })">
                                <Button icon="pi pi-pencil" rounded/>
                            </Link>

                            <Button
                                :icon="slotProps.data.status ? 'pi pi-pause' : 'pi pi-play'"
                                rounded
                                @click="activateDeactivate(slotProps.data)"
                            />

                            <Button
                                icon="pi pi-sync"
                                rounded
                                @click="recycleCampaign(slotProps.data)"
                            />

                            <Link :href="route('lead-distribution.ranking', { id: slotProps.data.id })">
                                <Button
                                    icon="pi pi-chart-bar"
                                    rounded
                                />
                            </Link>   

                        </template>
                    </Column>
                    <template #expansion="slotProps">
                        <div class="p-3">

                            <h5>Dados processamento</h5>

                            <div class="table-container">
                                <table class="beautiful-table">
                                    <thead>
                                        <tr>
                                            <th class="table-header">Progresso</th>
                                            <th class="table-header text-center">Total upload</th>
                                            <th class="table-header text-center">Processados</th>
                                            <th class="table-header text-center">Falhas</th>
                                            <th class="table-header text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-row">
                                            <td class="table-cell">{{ slotProps.data.progress.progress }}%</td>
                                            <td class="table-cell text-center">{{ slotProps.data.progress.totalJobs }}</td>
                                            <td class="table-cell text-center">{{ slotProps.data.progress.processedJobs }}</td>
                                            <td class="table-cell text-center">{{ slotProps.data.progress.failedJobs }}</td>
                                            <td class="table-cell text-center">
                                                <span 
                                                    class="text-white text-xs font-medium me-2 px-2 py-1 border-1 border-round"
                                                    :class="slotProps.data.progress.status == 'Finalizado' || slotProps.data.progress.progress == 100 ? 'bg-green-600 border-green-300' : slotProps.data.progress.status == 'Cancelado' ? 'bg-red-300 border-red-300': 'bg-blue-300 border-blue-300'"
                                                >
                                                    {{ slotProps.data.progress.progress == 100 ? 'Finalizado' : slotProps.data.progress.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </DataTable>

                <Pagination :data="campaigns"></Pagination>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>

.table-container {
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
    background-color: white;
}

.beautiful-table {
    width: 100%;
    border-collapse: collapse;
}

.table-header {
    padding: 20px 25px;
    text-align: left;
    background-color: var(--surface-ground);
    color: var(--text-color);
    font-size: 14px;
}

.table-cell {
    padding: 20px 25px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    font-size: 12px;
}

</style>
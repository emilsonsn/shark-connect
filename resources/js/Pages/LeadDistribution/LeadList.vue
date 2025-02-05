
<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted } from 'vue';
import VueSelect from "vue-select";
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    leads: Object,
    currentCampaign: Number,
});

const formatStatus = (status) => {
    if (status == 1) {
        return "Aberto";
    } else if (status == 2) {
        return "Fechado";
    } else if (status == 3) {
        return "Sem Saldo";
    } else if (status == 4){
        return "Sem interesse";
    } else if (status == 5){
        return "Mensagem Enviada";
    } else if (status == 6){
        return "Sem WhatsApp";
    }


}

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const defaultCampaign = {
    value: null,
    label: "Selecione a campanha"
};

const currentCampaignRef = ref(defaultCampaign)

const toast = useToast();

const startAttendance = async () => {
    router.get(route('lead-distribution.handle.treatNewLead'), {}, {
        preserveState: true,
        onFinish: () => {
            const message = usePage().props.jetstream.flash?.message || '';
            const style = usePage().props.jetstream.flash?.type || 'success';
            toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
        }
    });
}

const campaigns = ref([]);

onMounted(async () => {
    let resp = await fetch(route("usuarios.api.available-campaigns"))

    resp = await resp.json()

    campaigns.value = resp.map(campaign => {
        return {
            value: campaign.id,
            label: campaign.name
        }
    })

    if (props.currentCampaign) {
        currentCampaignRef.value = campaigns.value.find(campaign => campaign.value == props.currentCampaign)
    }
});

const loadLeadList = () => {
    router.get(route('lead-distribution.handle.index'), { search: searchRef.value }, { preserveState: true });
}

</script>
<template>
    <AppLayout title="Minha lista">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <DataTable 
                        :value="leads.data" 
                        tableStyle="min-width: 50rem;"
                    >
                        <template #header>
                            <div class="flex justify-content-between">
                                <div class="mb-4">
                                    <h1 class="text-4xl font-bold text-gray-800">
                                        Minha Lista
                                    </h1>
                                </div>

                                <div class="flex">
                                    <!-- <div class="mr-3">
                                    
                                        <vue-select 
                                            v-model="currentCampaignRef"
                                            :options="campaigns"
                                            :clearable="false"
                                            @option:selected="setCurrentCampaign"
                                            style="min-width: 300px"
                                            class="mt-1 flex-grow-1 bg-white border border-[1.5px] border-stroke text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        >
                                        </vue-select>
                                    </div> -->
                                    <div>
                                        <Button  @click="startAttendance">
                                            Iniciar Atendimento
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-content-between">
                                <div class="flex justify-content-end flex-grow">
                                    <IconField iconPosition="left">
                                        <InputIcon class="pi pi-search"> </InputIcon>
                                        <InputText
                                            v-model="searchRef"
                                            placeholder="Nome, CPF, Telefone..."
                                            @keyup.enter.native="loadLeadList()"
                                        />
                                    </IconField>                                
                                    
                                </div>
                            </div>

                        </template>

                        <Column field="campaign_name" header="Campanha" />
                        <Column field="client_name" header="Nome" />
                        <Column header="Status">
                            <template #body="{ data }">
                                {{ formatStatus(data.tabulation_id) }}
                            </template>
                        </Column>
                        <Column header="Convênio">
                            <template #body="{ data }">
                                {{ data.convenant ?? "0" }}
                            </template>
                        </Column>
                        <Column header="Orgão">
                            <template #body="{ data }">
                                {{ data.organ  ?? "0" }}
                            </template>
                        </Column>
                        <Column header="Ações">
                            <template #body="{ data }">
                                <Link :href="route('lead-distribution.handle.treatLead', data.id)" >
                                    <Button>
                                        Atendimento
                                    </Button>
                                </Link>
                            </template>
                        </Column>
                    </DataTable>

                    <Pagination :data="leads"></Pagination>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@import "vue-select/dist/vue-select.css";
</style>
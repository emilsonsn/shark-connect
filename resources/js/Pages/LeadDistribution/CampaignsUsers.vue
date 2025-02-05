<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueSelect from "vue-select";
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    users: Object,
    campaigns: Object,
});

const usersRef = ref([]);

usersRef.value = props.users.data.map((item) => {
    return {
        ...item,
        lead_distribution_campaigns: item.lead_distribution_campaigns.map(
            (campaign) => {
                return {
                    value: campaign.value,
                    label: campaign.label,
                    limit: campaign.pivot.limit,
                };
            }
        ),
    };
});

const toast = useToast();

function updateIndividualLimit(userId, campaignId, limit) {
    router.put(route("lead-distribution.campaigns.users.updateLimit"),
        {
            user_id: userId,
            campaign_id: campaignId,
            limit: limit,
        },
        {
            preserveState: true,
            onSuccess: () => {
                const message = usePage().props.jetstream.flash?.message || "";
                const style =
                    usePage().props.jetstream.flash?.type || "success";
                toast.add({
                    severity: style,
                    summary: "Info",
                    detail: message,
                    life: 3000,
                });
            },
        }
    );
}

function sendRequest(user) {
    //clone the object without proxy
    const userClone = JSON.parse(JSON.stringify(user));

    const campaignsUser = userClone.lead_distribution_campaigns.map(
        (item) => item.value
    );

    //doing with axios
    axios
        .put(route("lead-distribution.campaigns.users.update"), {
            user_id: user.id,
            campaigns: campaignsUser,
        })
        .then(function (response) {
            const message = response.data.message || "";
            const style = response.data.type || "success";
            toast.add({
                severity: style,
                summary: "Info",
                detail: message,
                life: 3000,
            });
        })
        .catch(function (error) {
            console.log(error);
            const message = error.response.data.message || "";
            const style = error.response.data.type || "error";
            toast.add({
                severity: style,
                summary: "Info",
                detail: message,
                life: 3000,
            });
        });
}

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const loadUserList = () => {

    router.get(route('lead-distribution.campaigns.users'), { 
        search: searchRef.value,
    });
}

</script>

<template>
    <AppLayout title="Campanhas usuários">
        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <div class="flex justify-content-between mb-4">
                        <IconField iconPosition="left">
                            <InputIcon class="pi pi-search"> </InputIcon>
                            <InputText
                                v-model="searchRef"
                                placeholder="Nome..."
                                @keyup.enter.native="loadUserList()"
                            />
                        </IconField>
                    </div>
                    <div class="relative mb-5">
                        <table
                            class="w-full text-sm text-left text-gray-500 border-1 border-gray-300"
                            style="border-collapse: collapse"
                        >
                            <thead
                                class="text text-gray-700 uppercase bg-white border-1 border-gray-300"
                            >
                                <tr class="border-1 border-gray-300">
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Nome</th>
                                    <th scope="col" class="px-6 py-3">
                                        Campanhas
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center"
                                    >
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in usersRef"
                                    :key="user.id"
                                    style="border: 1px solid #e2e8f0 !important"
                                >
                                    <th
                                        scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                    >
                                        {{ user.id }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Selecione as campanhas:
                                        <vue-select
                                            :options="campaigns"
                                            v-model="
                                                user.lead_distribution_campaigns
                                            "
                                            multiple
                                            clearable
                                            :closeOnSelect="false"
                                            v-tooltip.bottom="
                                                'Selecione as campanhas que deseja distribuir para esse usuário'
                                            "
                                            class="mt-1 bg-white text-gray-900 text-sm border-round block w-full border-1 border-gray-300"
                                        >
                                        </vue-select>
                                        <div class="mt-4">
                                            <div
                                                v-for="campaign in user.lead_distribution_campaigns"
                                                class="mt-4"
                                            >
                                                Campanha: {{ campaign.label }}
                                                <div class="flex">
                                                    <div
                                                        class="flex justify-content-center align-items-center"
                                                    >
                                                        Limite:
                                                        <InputText
                                                            v-model="
                                                                campaign.limit
                                                            "
                                                            size="small"
                                                            v-tooltip.bottom="
                                                                'Digite o limite individual dessa campanha'
                                                            "
                                                            type="text"
                                                            class="mt-1 block w-3/4 mx-2"
                                                        />
                                                    </div>
                                                    <div
                                                        class="flex justify-content-center align-items-end"
                                                    >
                                                        <Button
                                                            v-tooltip.top="
                                                                'Salva o limite individual dessa campanha'
                                                            "
                                                            size="small"
                                                            @click="
                                                                updateIndividualLimit(
                                                                    user.id,
                                                                    campaign.value,
                                                                    campaign.limit
                                                                )
                                                            "
                                                        >
                                                            Salvar
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div
                                            class="flex justify-content-evenly align-items-center"
                                        >
                                            <Button
                                                @click="sendRequest(user)"
                                                v-tooltip.bottom="
                                                    'Salva as campanhas seleciondas, somente'
                                                "
                                            >
                                                Salvar geral
                                            </Button>
                                        </div>
                                    </td>
                                    <hr />
                                </tr>
                            </tbody>
                        </table>
                        <Pagination :data="users"></Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>

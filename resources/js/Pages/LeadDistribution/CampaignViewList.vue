
<script setup>
import StandardPageLayout from '../../Layouts/StandardPageLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Pagination from '../../Components/Pagination.vue';
import { ref } from 'vue';
import CircleButton from '../../Components/CircleButton.vue';

defineProps({
    campaigns: Object,
});

const params = new URLSearchParams(window.location.search)
const defaultValue = 'active'

const search = params.has('search') ? params.get('search') : ""
const searchRef = ref(search)

const status = params.has('status') ? params.get('status') : defaultValue
const statusRef = ref(status)


const formatedDate = (date) => {
    return date ? new Date(date)?.toLocaleString("pt-BR"): "";
}

const loadCampaignList = () => {
    router.get(route('lead-distribution.viewOnly'), { 
        status: statusRef.value,
        search: searchRef.value 
    }, { preserveState: true });
}

</script>

<template>
    <StandardPageLayout>

        <Head title="Campanhas" />

        <div class="mb-4 flex justify-between">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">
                Campanhas
            </h1>
        </div>

        <div class="flex justify-between my-2">
            <div class="">
                
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        @keyup.enter.native="loadCampaignList" 
                        id="search" 
                        class="bg-white border border-[1.5px] border-stroke text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                        placeholder="Nome da campanha"
                        v-model="searchRef"
                    >
                </div>

            </div>
            
        </div>

        <div class="rounded-sm border border-stroke bg-white dark:border-strokedark dark:bg-boxdark mb-4">

            <div class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 dark:border-strokedark sm:grid-cols-8 md:px-6 2xl:px-7.5">
                <div class="col-span-1 flex items-center">
                    <p class="font-medium">ID</p>
                </div>
                <div class="col-span-3 hidden items-center sm:flex">
                    <p class="font-medium">Nome</p>
                </div>
                <div class="col-span-1 flex items-center">
                    <p class="font-medium">Status</p>
                </div>
                <div class="col-span-1 flex items-center">
                    <p class="font-medium">Criado em</p>
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <p class="font-medium">Total</p>
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <p class="font-medium">Restantes</p>
                </div>

                
            </div>

            <div 
                class="grid grid-cols-6 border-t border-stroke py-4.5 px-4 dark:border-strokedark sm:grid-cols-8 md:px-6 2xl:px-7.5"
                v-for="campaign in campaigns.data"
                :key="campaign.id"  
            >
                
                <div class="col-span-1 flex items-center">
                    <p class="text-sm text-black dark:text-white">
                        {{ campaign.id }}
                    </p>
                </div>
                <div class="col-span-3 hidden items-center sm:flex">
                    <p class="text-sm text-black dark:text-white">
                        {{ campaign.name }}
                    </p>
                </div>
                <div class="col-span-1 flex items-center">
                    <p class="text-sm text-black dark:text-white">
                        {{ campaign.status ? "Ativo" : "Inativo" }}
                    </p>
                </div>
                <div class="col-span-1 flex items-center">
                    <p class="text-sm text-black dark:text-white">
                        {{ formatedDate(campaign.created_at)  ?? ""}}
                    </p>
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <p class="text-sm text-black dark:text-white">
                        {{ campaign.total ?? "0" }}
                    </p>
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <p class="text-sm text-black dark:text-white">
                        {{ campaign.remaining  ?? "0" }}
                    </p>
                </div>
                
            </div>

        </div>

        <Pagination :data="campaigns"></Pagination>

    </StandardPageLayout>
</template>

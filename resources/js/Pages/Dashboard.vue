<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
    // subordinates: {
    //     type: [Object, Array],
    // },
    moreConsumedOnMonth: {
        type: Object,
    },
    moreConsumedToday: {
        type: Object,
    },
    moreUsedCampaignOnMonth: {
        type: Object,
    },
    moreUsedCampaignToday: {
        type: Object,
    }
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

        <div class="grid" v-if="$page.props.permissions.includes('list-count-leads-taken-by-all') || $page.props.permissions.includes('view-campaigns')">
            <div class="col-12 lg:col-6 xl:col-3" v-if="$page.props.permissions.includes('list-count-leads-taken-by-all') || $page.props.permissions.includes('view-campaigns')">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Mais consumidos no 
                                <span class="text-primary-500 font-medium">mês</span>
                            </span>
                            
                            <div class="text-900 font-medium text-xl">{{ moreConsumedOnMonth?.total ?? '' }}</div>
                        </div>
                        <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                            <i class="pi pi-user text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <span class="text-primary-500 font-medium">{{ moreConsumedOnMonth?.name ?? '' }}: </span>
                    <span class="text-500">{{ moreConsumedOnMonth?.group_name ?? '' }}</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3" v-if="$page.props.permissions.includes('list-count-leads-taken-by-all') || $page.props.permissions.includes('view-campaigns')">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Mais consumidos 
                                <span class="text-primary-500 font-medium">hoje</span>
                            </span>
                            <div class="text-900 font-medium text-xl">{{ moreConsumedToday?.total ?? '' }}</div>
                        </div>
                        <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                            <i class="pi pi-user text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <span class="text-primary-500 font-medium">{{ moreConsumedToday?.name ?? '' }}: </span>
                    <span class="text-500">{{ moreConsumedToday?.group_name ?? '' }}</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3" v-if="$page.props.permissions.includes('list-count-leads-taken-by-all')">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Campanha mais consumida do 
                                <span class="text-primary-500 font-medium">mês</span>
                            </span>
                            <div class="text-900 font-medium text-xl">{{ moreUsedCampaignOnMonth?.consumed  ?? ''}}</div>
                        </div>

                    </div>
                    <span class="text-500">{{ moreUsedCampaignOnMonth?.name  ?? ''}}</span>
                </div>
            </div>
            <div class="col-12 lg:col-6 xl:col-3" v-if="$page.props.permissions.includes('list-count-leads-taken-by-all')">
                <div class="card mb-0">
                    <div class="flex justify-content-between mb-3">
                        <div>
                            <span class="block text-500 font-medium mb-3">Campanha mais consumida
                                <span class="text-primary-500 font-medium">hoje</span>
                            </span>
                            <div class="text-900 font-medium text-xl">{{ moreUsedCampaignToday?.total ?? '' }}</div>
                        </div>

                    </div>
                    <span class="text-500">{{ moreUsedCampaignToday?.name ?? '' }}</span>
                </div>
            </div>
        </div>
        <div v-else>
            <!-- welcome message -->
            <div class="grid">
                <div class="col-12">
                    <div class="card">
                        <div class="">
                            <h1 class="text-2xl font-bold text-gray-800">
                                Bem-vindo, {{ $page.props.auth.user.name }}
                            </h1>
                            <p class="text-gray-500 mt-4">
                                
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import Pagination from '../../Components/Pagination.vue';
import VueSelect from "vue-select";
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    ranking: Object,
    groups: Object
});

const params = new URLSearchParams(window.location.search)

const currentUrl = window.location.href

const page = usePage();

const group = params.has('group') ? params.get('group') : page.props.auth.user.group_id
const groupRef =  ref(group)

const users = ref([]);
const user = params.has('user') ? params.get('user') : ''
const userRef = ref(user)

const loadRankingList = () => {
    router.get(currentUrl, { 
        group: groupRef.value,
        user: userRef.value
    }, { preserveState: true });
}

const loadUserList = () => {
    axios.get(route('usuarios.api.all-by-group', {
        groupId: groupRef.value
    })).then(response => {
        users.value = response.data.map((item) => {
            return {
                label: item.name,
                value: item.id,
            }
        })

        //add empty option
        users.value.unshift({
            label: 'Todos',
            value: '',
        })

    }).catch((error) => {
        console.log(error)
    })
}

loadUserList()

</script>

<template>
    <AppLayout title="Ranking campanha">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <div class="mb-4 flex justify-between">
                        <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">
                            Ranking por campanha
                        </h1>
                    </div>

                    <div class="flex justify-content-end mb-4">

                        <div class="flex items-center">
                            <div class="mx-4 flex align-items-center">
                                Grupos:
                            </div>

                            <vue-select
                                v-model="groupRef"  
                                :options="groups"
                                :reduce="group => group.value"
                                :clearable="false"
                                @option:selected="loadRankingList(), loadUserList()"
                                id="grupos"
                                style="min-width: 300px"
                                class="mt-1 bg-white border-1 border-200 border-round text-gray-900 text-sm block w-full p-1"
                            >
                            </vue-select>

                            <div class="mx-4 flex align-items-center">
                                Usuarios:
                            </div>

                            <vue-select
                                v-model="userRef"
                                :options="users"
                                :reduce="user => user.value"
                                :clearable="false"
                                @option:selected="loadRankingList"
                                id="usuarios"
                                style="min-width: 300px"
                                class="mt-1 bg-white border-1 border-200 border-round text-gray-900 text-sm block w-full p-1"
                            >
                            </vue-select>
                        </div>

                    </div>

                    <DataTable :value="ranking.data" tableStyle="min-width: 50rem;">
                        <Column field="name" header="Nome"></Column>
                        <Column field="group_name" header="Grupo"></Column>
                        <Column field="total"  header="Total"></Column>
                    </DataTable>

                    <Pagination :data="ranking"></Pagination>

                </div>
            </div>
        </div>
            
    </AppLayout>
</template>
<style>
@import "vue-select/dist/vue-select.css";
.step-content {
    border: 1px solid #ccc;
    padding: 20px;
}

.vs__dropdown-toggle {
    border: 0;
}
</style>

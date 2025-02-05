<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

defineProps({
    groups: Object,
});

const formatedDate = (date) => {
    return date ? new Date(date)?.toLocaleString("pt-BR"): "";
}

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const status = params.has('status') ? params.get('status') : 'active'
const statusRef = ref(status)

const activateDeactivate = (group) => {
    if (confirm("Tem certeza que deseja " + (group.is_active ? "desativar" : "ativar") + " este grupo?")) {

        const rota = group.is_active ? 'grupos.deactivate' : 'grupos.activate';

        router.patch(route(rota, { group: group.id }), {
            onSuccess: () => {
                alert('Grupo atualizado com sucesso!');
            },
        });
    }
}

const loadGroupList = () => {
    router.get(route('grupos.index'), {
        search: searchRef.value,
        status: statusRef.value
    }, { preserveState: true });
}

const statusList = ref([
    { name: 'Ativas', value: 'active' },
    { name: 'Inativas', value: 'inactive' },
    { name: 'Todos', value: 'all' },
]);

</script>

<template>

    <AppLayout title="Grupos">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <DataTable 
                        :value="groups.data" 
                        tableStyle="min-width: 50rem;"
                    >
                        <template #header>

                            <div class="mb-4">
                                <h1 class="text-4xl font-bold text-gray-800">
                                    Grupos
                                </h1>
                            </div>
                            <div class="flex justify-content-between">
                                <IconField iconPosition="left">
                                    <InputIcon class="pi pi-search"> </InputIcon>
                                    <InputText
                                        v-model="searchRef"
                                        placeholder="Grupo..."
                                        @keyup.enter.native="loadGroupList()"
                                    />
                                </IconField>

                                <Dropdown 
                                    v-model="statusRef" 
                                    :options="statusList" 
                                    optionLabel="name"
                                    optionValue="value"
                                    class="w-full md:w-14rem"
                                    @change="loadGroupList()"
                                />
                            </div>
                        </template>
                        <Column field="name" header="Nome"></Column>
                        <Column field="updated_at" header="Atualizado em">
                            <template #body="{ data }">
                                {{ formatedDate(data.updated_at)  ?? ""}}
                            </template>
                        </Column>
                        <Column header="Ações" class="flex gap-1">
                            <template #body="{ data }">
                                <Link :href="route('grupos.edit', { group: data.id })">
                                    <Button icon="pi pi-pencil" rounded/>
                                </Link>

                                <Button 
                                    :icon="data.is_active ? 'pi pi-pause' : 'pi pi-play'" 
                                    rounded
                                    @click="activateDeactivate(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>            

                    <Pagination :data="groups"></Pagination>
                </div>
            </div>
        </div>
    </AppLayout>

</template>

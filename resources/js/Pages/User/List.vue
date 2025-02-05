<script setup>

import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    users: Object,
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

const activateDeactivate = (user) => {
    if (confirm("Tem certeza que deseja " + (user.is_active ? "desativar" : "ativar") + " este usuario?")) {

        const rota = user.is_active ? 'usuarios.deactivate' : 'usuarios.activate';

        router.patch(route(rota, { user: user.id }), {
            onSuccess: () => {
                alert('Usuario atualizado com sucesso!');
            },
        });
    }
}

const loadUserList = () => {
    router.get(route('usuarios.index'), { 
        search: searchRef.value,
        status: statusRef.value,
    }, { preserveState: true });
}

const resetToken = (user) => {
    if (confirm("Tem certeza que deseja resetar o token deste usuario?")) {

        router.patch(route('usuarios.resetToken', { user: user.id }), {
            onSuccess: () => {
                alert('Token resetado com sucesso!');
            },
        });
    }
}

const statusList = ref([
    { name: 'Ativas', value: 'active' },
    { name: 'Inativas', value: 'inactive' },
    { name: 'Todos', value: 'all' },
]);

</script>

<template>
    <AppLayout title="Usuarios">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <DataTable 
                        :value="users.data" 
                        tableStyle="min-width: 50rem;"
                    >
                        <template #header>
                        
                            <div class="mb-4">
                                <h1 class="text-4xl font-bold text-gray-800">
                                    Usuarios
                                </h1>
                            </div>

                            <div class="flex justify-content-between">
                                <IconField iconPosition="left">
                                    <InputIcon class="pi pi-search"> </InputIcon>
                                    <InputText
                                        v-model="searchRef"
                                        placeholder="Nome, Login, Grupo..."
                                        @keyup.enter.native="loadUserList()"
                                    />
                                </IconField>

                                <Dropdown 
                                    v-model="statusRef" 
                                    :options="statusList" 
                                    optionLabel="name"
                                    optionValue="value"
                                    class="w-full md:w-14rem"
                                    @change="loadUserList()"
                                />
                                    
                            </div>
                        </template>
                        <Column field="login" header="Login"></Column>
                        <Column header="Superior">
                            <template #body="{ data }">
                                {{ data?.superior?.nome ?? ""}}
                            </template>
                        </Column>
                        <Column header="Grupo">
                            <template #body="{ data }">
                                {{ data.group?.name  ?? ""}}
                            </template>
                        </Column>
                        <Column header="Atualizado em">
                            <template #body="{ data }">
                                {{ formatedDate(data.updated_at)  ?? ""}}
                            </template>
                        </Column>
                        <Column header="Ações" class="flex gap-1">
                            <template #body="{ data }">
                                <Link :href="route('usuarios.edit', { user: data.id })" >
                                    <Button icon="pi pi-pencil" rounded/>
                                </Link>

                                <Button 
                                    :icon="data.is_active ? 'pi pi-pause' : 'pi pi-play'" 
                                    rounded
                                    @click="activateDeactivate(data)"
                                />
                                
                                <!-- <Button
                                    class="ml-3"
                                    @click="resetToken(data)"
                                >
                                    Reset Token
                                </Button> -->
                            </template>
                        </Column>
                    </DataTable>

                    <Pagination :data="users"></Pagination>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
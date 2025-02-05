<script setup>
import { onMounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import VueSelect from "vue-select";
import { useToast } from 'primevue/usetoast';

const form = useForm({
    name: '',
    campaigns: []
});

const toast = useToast();

const submit = () => {
    form
    .transform(( data ) => ({
        ...data,
        campaigns: data.campaigns.map(item => item.value),
    }))
    .post(route('grupos.store'), {
        onSuccess: () => {
            form.reset()
            const message = usePage().props.jetstream.flash?.message || '';
            const style = usePage().props.jetstream.flash?.type || 'success';
            toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
        }
    });
};

const campaigns = ref([]);

onMounted(async () => {
    const response = await fetch(route("lead-distribution.api.campaigns"))
    campaigns.value = await response.json().then(data => data.map(item => ({ value: item.id, label: item.name })));
});

const addAllCampaigns = () => {

    form.campaigns = campaigns.value;

};

</script>

<template>
    <AppLayout title="Criar grupo">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <div class="mb-4">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                            Criar grupo
                        </h1>
                    </div>

                    <div class="w-full sm:max-w-full mt-6 bg-white shadow-md sm:rounded-lg">
                        <form @submit.prevent="submit" class="">
                            <div class="gap-4 mb-4">

                                <div>
                                    <InputLabel for="name" value="Nome" />
                                    <InputText 
                                        id="name" 
                                        v-model="form.name" 
                                        type="text" 
                                        class="mt-1 block w-1/2" 
                                        required
                                        autofocus 
                                        autocomplete="name" 
                                    />
                                    <InputError class="mt-2" :message="form.errors.name"  />
                                </div>

                                <div class="mt-4">
                                    <div>
                                        <InputLabel class="mt-2" for="campanhas" value="Campanhas" />
                                        <vue-select 
                                            v-model="form.campaigns" :options="campaigns" multiple clearable :closeOnSelect="false"
                                            id="campanhas"
                                            class="mt-1 bg-white text-gray-900 text-sm rounded-lg w-full p-2.5"
                                        >
                                        </vue-select>
                                        <InputError class="mt-2" :message="form.errors.campaigns"  />
                                    </div>

                                    <div class="mt-2 text-blue-700 cursor-pointer" @click="addAllCampaigns()">
                                        + Adicionar todas
                                    </div>
                                </div>

                            </div>

                            <div class="flex align-items-center justify-content-end mt-4">

                                <Button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
                                    Criar
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    </AppLayout>
</template>

<style>
@import "vue-select/dist/vue-select.css";
</style>
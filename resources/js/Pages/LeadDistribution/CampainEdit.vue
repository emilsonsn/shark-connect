<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import VueSelect from "vue-select";
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    campaign: Object,
    groups: Object,
    selectedGroups: Object,
});

const selectedGroups = props.selectedGroups.map(item => ({ value: item.value, label: item.label }));

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description,
    groups: selectedGroups
});

const submit = () => {

    form.transform(( data ) => ({
        ...data,
        groups: data.groups.map(item => item.value),
    }))
    .put(route('lead-distribution.update', { leadDistributionCampaign: props.campaign.id }), {
        onSuccess: () => {
            alert('Campanha atualizada com sucesso!');
        },
    });

};

const voltar = () => {
    router.visit(route('lead-distribution.index'));
}

</script>

<template>
    <AppLayout title="Editar campanha">

        <div class="grid">
            <div class="col-12">
                <div class="card">
                    <div class="mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">
                            Editar Campanha
                        </h3>
                    </div>

                    <div class="w-full sm:max-w-full mt-6 px-6 py-4 bg-white shadow-md sm:rounded-lg">
                        <form @submit.prevent="submit" class="">

                            <div class="grid gap-4 mb-4">
                                <div class="col">
                                    <div>
                                        <InputLabel for="name" value="Nome" />
                                        <InputText 
                                            v-model="form.name"
                                            id="name" type="text" class="mt-1 block w-full" required
                                            autofocus />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel class="mt-2" for="description" value="Descricao" />
                                        <Textarea 
                                            v-model="form.description"
                                            id="description" 
                                            name="description" 
                                            rows="4" 
                                            class="block p-2.5 mt-1 w-full text-sm text-gray-900 bg-white border" placeholder="Escreva uma breve descrição...">
                                        </Textarea>
                                        <InputError class="mt-2" :message="form.errors.description"  />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel class="mt-2" for="grupos" value="Grupos" />
                                        <vue-select 
                                            v-model="form.groups" 
                                            :options="$page.props.groups" 
                                            multiple 
                                            clearable 
                                            :closeOnSelect="false"
                                            id="grupos"
                                            class="mt-1 bg-white border border-[1.5px] border-stroke text-gray-900 text-sm rounded-lg block w-full"
                                        >
                                        </vue-select>
                                        <InputError class="mt-2" :message="form.errors.groups"  />
                                    </div>

                                </div>
                                    
                            </div>
                            

                            <div class="flex gap-2 justify-content-end text-right mt-4">
                                <Button type="submit">Editar</Button>
                                <Button severity="secondary" type="button" @click="voltar()">Voltar</Button>
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
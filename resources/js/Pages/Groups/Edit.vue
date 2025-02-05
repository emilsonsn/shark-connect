<script setup>

import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    group: Array,
});

const form = useForm({
    name: props.group.name,
});

const toast = useToast();

const submit = () => {
    form.put(route('grupos.update', { group: props.group.id}),{
        onSuccess: () => {
            const message = usePage().props.jetstream.flash?.message || '';
            const style = usePage().props.jetstream.flash?.type || 'success';
            toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
        },
    });
};

</script>

<template>
    <AppLayout title="Editar grupo">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                <div class="mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Editar grupo
                    </h1>
                </div>
                    <form @submit.prevent="submit" class="">
                        <div class="grid gap-4 mb-4">
                            <div class="rounded col">
                                <div>
                                    <InputLabel for="name" value="Nome" />
                                    <InputText 
                                        id="name" 
                                        v-model="form.name" 
                                        type="text" 
                                        class="mt-1" 
                                        required
                                        autofocus 
                                        autocomplete="name" 
                                    />
                                    <InputError class="mt-2" :message="form.errors.name"  />
                                </div>

                            </div>

                        </div>

                        <div class="flex align-items-center justify-content-end mt-4">

                            <Button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
                                Editar
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
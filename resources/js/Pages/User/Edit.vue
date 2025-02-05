<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import RadioButton from '@/Components/RadioButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    groups: Array,
    user: Object,
    superiorUserList: Array
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    group_id: props.user.group.id,
    superior_user_id: props.user.superior_id,
    isExterno: props.user.is_externo,
    can_escobs: props.user.can_escobs,
});

const superiorUserList = ref(props.superiorUserList)

const toast = useToast();

const submit = () => {
    form.put(route('usuarios.update', {user: props.user.id}), {
        onSuccess: () => {
            form.reset()
            const message = usePage().props.jetstream.flash?.message || '';
            const style = usePage().props.jetstream.flash?.type || 'success';
            toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
        }
    });
};

async function loadSecondSelect() {

    if (!form.group_id) {
        superiorUserList.value = null
        return
    }

    const response = await fetch('/grupos/' + form.group_id + '/usuarios')
    superiorUserList.value = await response.json()
}
</script>

<template>
    <AppLayout title="Editar usuario">

<div class="grid">
    <div class="col-12">
        <div class="card">

        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Editar usuário
            </h1>
        </div>

        <div class="w-full sm:max-w-full mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <form @submit.prevent="submit" class="">
                <div class="grid gap-4 mb-4">
                    <div class="rounded col">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <InputText id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                                autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name"  />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="grupo" value="Grupo" />
                            <Dropdown 
                                id="grupo" 
                                v-model="form.group_id" 
                                :options="groups" 
                                optionLabel="name" 
                                optionValue="id"
                                placeholder="Selecione um"
                                class="w-full mt-1"
                                @change="loadSecondSelect"
                            >
                            </Dropdown>
                            <InputError class="mt-2" :message="form.errors.group_id"  />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="superior" value="Superior" />
                            <Dropdown 
                                id="superior" 
                                v-model="form.superior_user_id" 
                                :options="superiorUserList" 
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Selecione um"
                                class="w-full mt-1"
                                @change="loadSecondSelect"
                            >
                            </Dropdown>
                            <InputError class="mt-2" :message="form.errors.superior_id"  />
                        </div>

                    </div>

                    <div class="rounded col">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <InputText id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                                autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email"  />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Senha" />
                            <InputText id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Confirmar senha" />
                            <InputText id="password_confirmation" v-model="form.password_confirmation" type="password"
                                class="mt-1 block w-full" autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
</div>
    </AppLayout>
</template>

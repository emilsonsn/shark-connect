<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { useLayout } from '@/Layouts/composables/layout';
import { computed } from 'vue';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const { layoutConfig } = useLayout();

const logoUrl = computed(() => {
    return `/images/logo-secundaria.png`;
});

</script>

<template>
    <Head title="Log in" />

    <div class="surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
        <div class="flex flex-column align-items-center justify-content-center">
            <img :src="logoUrl" alt="logo" class="mb-5 w-10 flex-shrink-0" />
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full surface-card py-8 px-5 sm:px-8" style="border-radius: 53px">
                    <div class="text-center mb-5">
                        <div class="text-900 text-3xl font-medium mb-3">Bem vindo!</div>
                        <span class="text-600 font-medium">Faça login para continuar</span>
                    </div>
                    <form @submit.prevent="submit">
                        <div>
                            <label for="login" class="block text-900 text-xl font-medium mb-2">Login</label>
                            <InputText 
                                v-model="form.email" 
                                id="login" 
                                type="text" 
                                placeholder="Seu login" 
                                class="w-full md:w-30rem mb-1" 
                                style="padding: 1rem"
                                :invalid="form.errors.email"
                            />
                            <InputError class="mb-2" :message="form.errors.email" />

                            <label for="password1" class="block text-900 font-medium text-xl my-2">Senha</label>
                            <Password 
                                id="password1" 
                                v-model="form.password" 
                                placeholder="Sua senha" 
                                :toggleMask="true" 
                                class="w-full mb-1" 
                                inputClass="w-full" 
                                :inputStyle="{ padding: '1rem' }"
                                :feedback="false"
                                :invalid="form.errors.password"
                            >
                            </Password>
                            <InputError class="mb-2" :message="form.errors.password" />

                            <Button 
                                label="Log in" 
                                class="w-full p-3 mt-5 text-xl"
                                type="submit"
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing">
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

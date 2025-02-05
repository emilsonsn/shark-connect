<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import VueSelect from "vue-select";
import { onMounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const emit = defineEmits(['close']);

const groups = ref([]);

const form = useForm({
    name: '',
    description: '',
    file: '',
    groups: [],
    max_per_user: 100,
});

const toast = useToast();

const submit = () => {

    form
    .transform(( data ) => ({
        ...data,
        groups: data.groups.map(item => item.value),
    }))
    .post(route('lead-distribution.store'), {
        onSuccess: () => {
            form.reset()
            const message = usePage().props.jetstream.flash?.message || '';
            const style = usePage().props.jetstream.flash?.type || 'success';
            toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });
            emit('close');
        },
    });

};

const close = () => {
    emit('close');
};

const addAllGroups = () => {

    form.groups = groups.value;
    
};

const handleFile = (file) => 
{
    form.file = file.files[0];
};

onMounted(async () => {
    const response = await fetch(route("grupos.api.index"))
    groups.value = await response.json().then(data => data.map(item => ({ value: item.id, label: item.name })));

});

</script>

<template>
    <Dialog 
        modal
        dismissableMask
        style="width: 50%;"
    >
        <template #header>
            <div>
                <h1 class="text-2xl font-medium text-gray-900">
                    Criar Campanha
                </h1>
            </div>

        </template>
        <form @submit.prevent="submit" class="">
            <div class="px-4 py-2">

                <div class="text-sm text-gray-600">
                    <div>
                        
                        <InputLabel for="name" value="Nome" />
                        <InputText 
                            v-model="form.name"
                            id="name" 
                            type="text" 
                            class="mt-1 block w-full"
                            autofocus 
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    
                        <InputLabel class="mt-2" for="description" value="Descricao" />
                        <Textarea 
                            v-model="form.description"
                            id="description" 
                            name="description" 
                            rows="4" 
                            class="block w-full text-sm text-gray-900 bg-white " 
                            placeholder="Escreva uma breve descrição..."
                        >
                        </Textarea>
                        <InputError class="mt-2" :message="form.errors.description"  />

                        <InputLabel class="mt-2" for="grupos" value="Grupos" />
                        <vue-select 
                            v-model="form.groups" :options="groups" multiple clearable :closeOnSelect="false"
                            id="grupos"
                            class="mt-1 bg-white text-gray-900 text-sm border-round block w-full border-1 border-gray-300"
                        >
                        </vue-select>
                        <InputError class="mt-2" :message="form.errors.groups"  />

                        <div class="mt-2 text-blue-700 cursor-pointer w-fit" @click="addAllGroups()">
                            + Adicionar todos
                        </div>

                        <InputLabel class="mt-2 mb-1" for="file_input" value="Arquivo" />
                        <FileUpload 
                            mode="basic" 
                            @select="handleFile" 
                            customUpload 
                            @uploader="() => { form.file = '' }"
                            chooseLabel="Escolher"
                        />

                        <InputError class="mt-2" :message="form.errors.file"  />
                        
                    </div>
                </div>
            </div>

            <div class="flex justify-content-end px-6 py-4 bg-gray-100 text-right border-round">
                <Button class="py-2 px-3 font-semibold mr-3" type="submit">Criar</Button>
                <Button class="py-2 px-3 font-semibold" @click="close()" severity="danger" type="button">Fechar</Button>

            </div>
        </form>
    </Dialog>
</template>
<style>
@import "vue-select/dist/vue-select.css";
.p-dialog-content{
    overflow-y: visible !important;
}
</style>
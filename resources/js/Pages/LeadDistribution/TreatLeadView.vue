<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    lead: Object,
    campaign: Object,
    isNew: Boolean,
});

const contacts = ref([]);

const formatCPF = (cpf) => {

    if (cpf.length != 11) return cpf;

    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
}

const formatPhone = (phone) => {

    if (phone.length != 11) return phone;

    return phone.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
}

const toast = useToast();

const tabular = (tabulacao_id) => {

    // if(!confirm("Tem certeza que deseja tabular?")) return;

    axios.put(route("lead-distribution.handle.updateStatus", { id: props.lead.id}), {
        status: tabulacao_id
    }).then(response => {

        const message = 'Tabulado com sucesso';
        const style = 'success';
        toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });

        //reload this same page
        if(props.isNew){
            router.get(route('lead-distribution.handle.treatNewLead'), {}, {
                preserveState: true,
                onFinish: async () => {
                    const message = usePage().props.jetstream.flash?.message || '';
                    const style = usePage().props.jetstream.flash?.type || 'success';
                    toast.add({ severity: style, summary: 'Info', detail: message, life: 3000 });

                    await getContacts();
                }
            });
        }

    }).catch((error) => {
        
        console.log(error.response.data.message)
        alert(error.response.data.message)
        
    })

}

async function getContacts(){
    contacts.value = await fetch(route("clientes.api.contatos.index", { id: props.lead.client_id }))
    .then(response => response.json());
}

onMounted(async () => {
    await getContacts();
});

const tabList = ref([
    { name: 'Fechado', value: 2 },
    { name: 'Sem Saldo', value: 3 },
    { name: 'Sem interesse', value: 4 },
    { name: 'Mensagem Enviada', value: 5 },
    { name: 'Sem WhatsApp', value: 6 },
]);

const selectedTab = ref(props.isNew ? 5 : props.lead.tabulation_id);

</script>

<template>
    <AppLayout title="Minha Lista">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <div class="w-full px-6 py-4 bg-white shadow-1">
                        
                        <div class="flex justify-content-between">
                            
                            <div class="flex">
                                
                                <div class="text-xl font-bold text-gray-800 flex align-items-center ml-3">
                                    Minha Lista
                                </div>
                            </div>

                            <div class="text-xl font-bold text-gray-800">
                                <Link :href="route('lead-distribution.handle.index')" class="flex align-items-center">
                                    <Button>Voltar</Button>
                                </Link>
                            </div>
                            
                        </div>
                        
                        <hr class="my-4">

                        <div class="flex justify-content-between gap-4">

                            <div class="col-8">
                                <div class="flex gap-4">
                                    <div class="col-6 text-sm font-bold text-gray-600 mb-4 p-0">
                                        
                                        <InputLabel for="name" value="Nome:" />
                                        <InputText 
                                            id="name" 
                                            :value="lead.name" 
                                            type="text" 
                                            class="mt-1 w-full" 
                                            disabled
                                        />
                                    </div>

                                    <div class="col-6 text-sm font-bold text-gray-600 p-0 mb-4">
                                        <InputLabel for="cpf" value="CPF:" />
                                        <InputText 
                                            id="cpf" 
                                            :value="formatCPF(lead.cpf)" 
                                            type="text" 
                                            class="mt-1 w-full" 
                                            disabled
                                        />
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="col-3 p-0">
                                        <InputLabel for="margem" value="Margem:" />
                                        <InputText 
                                            id="margem" 
                                            :value="lead.margin" 
                                            type="text" 
                                            class="mt-1 w-full" 
                                            disabled
                                        />
                                    </div>
                                    <div class="col-3 p-0">
                                        <InputLabel for="convenio" value="Convenio:" />
                                        <InputText 
                                            id="convenio" 
                                            :value="lead.convenant" 
                                            type="text" 
                                            class="mt-1 w-full" 
                                            disabled
                                        />
                                    </div>
                                    <div class="col-3 p-0">
                                        <InputLabel for="orgao" value="Orgão:" />
                                        <InputText 
                                            id="orgao" 
                                            :value="lead.organ" 
                                            type="text" 
                                            class="mt-1 w-full" 
                                            disabled
                                        />
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div>
                                    <div 
                                        v-for="(contact, index) in contacts"
                                        :key="contact.id"
                                        class="text-sm font-bold text-gray-600 dark:text-gray-200 my-2"
                                    >
                                        <p>Telefone {{ index + 1 }}:</p>
                                        <div class="border-1 border-gray-300 border-round p-2" style="width: fit-content;">
                                            <a :href="'https://wa.me/55' + contact.ddd + '' + contact.number" target="_blank"
                                                 > {{ contact.ddd + "" + contact.number }} </a>                                            
                                        </div>
                                    
                                    </div>
                                </div>

                            </div>

                            <div class="col-4 border-left-1 border-gray-300">

                                <div class="p-2 bg-primary w-full border-round-top">
                                    <p>Tabulação</p>
                                </div>

                                <div class="flex justify-content-center p-2 w-full">
                                    <Dropdown 
                                        v-model="selectedTab" 
                                        :options="tabList" 
                                        optionLabel="name"
                                        optionValue="value"
                                        class="w-full md:w-14rem"
                                    />
                                </div>

                                <div class="flex justify-content-end">
                                    <Button 
                                        @click="tabular(selectedTab)" 
                                        class=""
                                    >
                                        Tabular
                                    </Button>
                                </div>
                                

                            </div>
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
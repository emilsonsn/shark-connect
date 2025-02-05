
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    campaigns: Object,
});

</script>

<template>
    <AppLayout title="Usuarios">

        <div class="grid">
            <div class="col-12">
                <div class="card">

                    <div class="mb-4 flex justify-content-between">
                        <h1 class="text-4xl font-bold text-gray-800">
                            Campanhas usuario
                        </h1>
                    </div>

                    <DataTable :value="campaigns.data">
                        <Column field="name" header="Nome"></Column>
                        <Column field="total" header="Total"></Column>
                    </DataTable>

                    <Pagination :data="campaigns"></Pagination>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>

import Pagination from '@/Components/Pagination.vue';
import formatedDate from '../Utils/formatedDate';
import { computed } from 'vue';

const props = defineProps({
    data: Object,
    columns: Array,
    gridLength: Number,
    hasActions: Boolean,
    hasSearch: Boolean,

    search: String,
    load: Function,
});

const gridLengthClass = computed(() => {
    return 'grid-cols-' + (!props.gridLength ? props.columns.length : props.gridLength);
});

</script>

<template>
    <div class="overflow-x-auto">
        <div class="rounded-sm border border-stroke bg-white mb-4">

            <div 
                class="grid border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                :class="gridLengthClass"
            >
                <div 
                    v-for="column in columns"
                    class="flex items-center"
                    :class="column.class ?? 'col-span-1'"
                >
                    <p class="font-medium">
                        {{ typeof column === 'object' ? (column?.name ?? '') : column }}
                    </p>
                </div>
                
                <div v-if="hasActions" class="col-span-2 flex items-center justify-center">
                    <p class="font-medium">Ações</p>
                </div>
                
            </div>

            <div 
                class="grid border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5"
                :class="gridLengthClass"
                v-for="user in data.data"
                :key="user.id"  
            >
                
                <slot name="row" :user="user"></slot>
                
                <div class="col-span-2 flex items-center justify-center gap-1">
                    <slot name="actions" :user="user"></slot>
                </div>
            </div>

        </div>

        <Pagination :data="data"></Pagination>

    </div>

</template>
<script setup>

import Pagination from '@/Components/Pagination.vue';
import formatedDate from '../Utils/formatedDate';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { reactive } from 'vue';

const props = defineProps({
    data: Object,
    columns: Array,
    gridLength: Number,
    hasActions: {
        type: Boolean,
        default: true
    },
    hasSearch: {
        type: Boolean,
        default: true
    },
    routeLink: String,
    queryParams: Object
});

const gridLengthClass = computed(() => {
    return 'grid-cols-' + (!props.gridLength ? props.columns.length : props.gridLength);
});

function getDescendantProp(obj, desc) {
    var arr = desc.split(".");
    while(arr.length && (obj = obj[arr.shift()]));
    return obj;
}

const params = new URLSearchParams(window.location.search)
const defaultValue = ''

const search = params.has('search') ? params.get('search') : defaultValue
const searchRef = ref(search)

const order = params.has('order') ? params.get('order') : defaultValue
const orderRef = ref(order)

const orderDir = params.has('orderDir') ? params.get('orderDir') : defaultValue
const orderDirRef = ref(orderDir)

let otherParams = reactive({})

if(props.queryParams.length !== 0) {
    props.queryParams.forEach((value) => {
        otherParams[value] = {
            value: params.has(value) ? params.get(value) : defaultValue
        }
    })
}
    
function loadList() {
    
    let otherParamsObj = {}

    if(props.queryParams.length !== 0) {
        otherParamsObj = Object.entries(otherParams).reduce((acc, [key, value]) => {
            acc[key] = value.value
            return acc
        }, {})
    }

    const routeParams = {
        search: searchRef.value,
        ...otherParamsObj,
    }

    if(orderRef.value) {
        routeParams.order = orderRef.value
        routeParams.orderDir = orderDirRef.value
    }
    
    router.get(props.routeLink, routeParams, { preserveState: true })
}

function orderBy(column){
    
    if(orderRef.value === column) {
        orderDirRef.value = orderDirRef.value === 'asc' ? 'desc' : 'asc'
    } else {
        orderRef.value = column
        orderDirRef.value = 'asc'
    }

    loadList()
}

</script>

<template>
    <div v-if="hasSearch" class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </div>
        <input 
            type="text" 
            v-model="searchRef"
            id="search" 
            class="bg-white border border-[1.5px] border-stroke text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
            placeholder="Nome, Login, Grupo..."
            @keyup.enter.native="loadList"
        >
    </div>
    <div class="flex justify-end">
        <slot name="additionalFilters" :params="{ ...otherParams }" :loadList="loadList"></slot>
    </div>
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
                    @click="orderBy(column.property)"
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
                
                <div 
                    v-for="column in columns"
                    class="flex items-center"
                    :class="column.class ?? 'col-span-1'"
                >
                    <p v-if="!column.isDate" class="text-sm text-black dark:text-white">
                        {{ user[column.property] ?? getDescendantProp(user, column.property ?? '') ?? ""}}
                    </p>

                    <p v-else class="text-sm text-black dark:text-white">
                        {{ 
                            formatedDate(user[column.property]) ?? 
                            formatedDate(getDescendantProp(user, column.property ?? '')) ?? ""
                        }}
                    </p>
                </div>
                
                <div v-if="hasActions" class="col-span-2 flex items-center justify-center gap-1">
                    <slot name="actions" :user="user"></slot>
                </div>
            </div>

        </div>

        <Pagination :data="data"></Pagination>

    </div>

</template>
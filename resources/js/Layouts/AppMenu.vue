<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import { PrimeIcons } from 'primevue/api';

const model = ref([
    {
        label: 'Menu',
        items: [
            { label: 'Dashboard', icon: PrimeIcons.CHART_LINE, to: 'dashboard', permission: true },
            
            { label: 'Campanhas', icon: PrimeIcons.MEGAPHONE, to: 'lead-distribution.index', permission: 'manage-campaigns'},
            
            { 
                label: 'Usuários', 
                permission: 'manage-users',
                icon: PrimeIcons.USER, 
                items: [
                    { label: 'Listar', icon: PrimeIcons.USERS, to: 'usuarios.index', permission: 'manage-users'},
                    { label: 'Criar', icon: PrimeIcons.USER_PLUS, to: 'usuarios.create', permission: 'manage-users'},
                ] 
            },
            { 
                label: 'Grupos', 
                permission: 'manage-groups',
                icon: PrimeIcons.USERS, 
                items: [
                    { label: 'Listar', icon: PrimeIcons.LIST, to: 'grupos.index', permission: 'manage-groups'},
                    { label: 'Criar', icon: PrimeIcons.PLUS, to: 'grupos.create', permission: 'manage-groups'},
                ] 
            },

            { label: 'Minha Lista', icon: PrimeIcons.LIST, to: 'lead-distribution.handle.index', permission: true },
            
            { label: 'Campanhas para usuarios', permission:'view-campaigns' , icon: PrimeIcons.LIST, to: 'lead-distribution.campaigns.users' },
        
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>

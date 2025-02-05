<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { inject } from 'vue'
import SideBarLinkGroup from './SideBarLinkGroup.vue';

const { sidebarOpen, setSidebarOpen } = defineProps(['sidebarOpen', 'setSidebarOpen']);

const sidebar = ref(null);
const trigger = ref(null);

const envMode = inject('envMode');

const storedSidebarExpanded = localStorage.getItem('sidebar-expanded');
const sidebarExpanded = ref(storedSidebarExpanded === null ? false : storedSidebarExpanded === 'true');

const closeSidebarOnClickOutside = (event) => {
    if (!sidebar.value) return;
    if (!sidebarOpen || sidebar.value.contains(event.target)) return;
    setSidebarOpen(false);
};

const closeSidebarOnEsc = (event) => {
    if (!sidebarOpen || event.keyCode !== 27) return;
    setSidebarOpen(false);
};

onMounted(() => {
    document.addEventListener('click', closeSidebarOnClickOutside);
    document.addEventListener('keydown', closeSidebarOnEsc);
});

onUnmounted(() => {
    document.removeEventListener('click', closeSidebarOnClickOutside);
    document.removeEventListener('keydown', closeSidebarOnEsc);
});

watch(sidebarExpanded, (newValue) => {
    localStorage.setItem('sidebar-expanded', newValue.toString());
    if (newValue) {
        document.querySelector('body')?.classList.add('sidebar-expanded');
    } else {
        document.querySelector('body')?.classList.remove('sidebar-expanded');
    }
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const isActive = ref(true);

const activeCondition = (current, condition) => {
    //starts with
    return current.startsWith(condition);
}

</script>

<template>
    <aside
        ref="sidebar"
        :class="[
            'absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-black duration-300 ease-linear dark:bg-boxdark lg:static lg:translate-x-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
    >
        <!-- SIDEBAR HEADER -->
        <div class="flex items-center justify-center gap-2 px-6 py-5.5 lg:py-6.5">
            <Link href="#">
                <img src="/images/logo-4.png" alt="" class="w-[150px]">
            </Link>

            <button
                ref="trigger"
                @click="toggleSidebar"
                :aria-controls="'sidebar'"
                :aria-expanded="sidebarOpen"
                class="block lg:hidden"
            >
                <svg
                    class="fill-current"
                    width="20"
                    height="18"
                    viewBox="0 0 20 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                <path
                    d="M19 8.175H2.98748L9.36248 1.6875C9.69998 1.35 9.69998 0.825 9.36248 0.4875C9.02498 0.15 8.49998 0.15 8.16248 0.4875L0.399976 8.3625C0.0624756 8.7 0.0624756 9.225 0.399976 9.5625L8.16248 17.4375C8.31248 17.5875 8.53748 17.7 8.76248 17.7C8.98748 17.7 9.17498 17.625 9.36248 17.475C9.69998 17.1375 9.69998 16.6125 9.36248 16.275L3.02498 9.8625H19C19.45 9.8625 19.825 9.4875 19.825 9.0375C19.825 8.55 19.45 8.175 19 8.175Z"
                    fill=""
                />
                </svg>
            </button>
        </div>
        <!-- SIDEBAR HEADER -->

        <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
            <!-- Sidebar Menu -->
            <nav class="mt-5 py-4 px-4 lg:mt-9 lg:px-6">
                <!-- Menu Group -->
                <div>
                    <h3 class="mb-4 ml-4 text-sm font-semibold text-bodydark2">
                        MENU
                    </h3>

                    <ul class="mb-6 flex flex-col gap-1.5">
                        <SideBarLinkGroup :activeCondition="activeCondition(route().current(), 'dashboard')">
                            <template v-slot="{ handleClick, open }">
                                <div>
                                    <a
                                        href="#"
                                        :class="{
                                            'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                            'bg-graydark dark:bg-meta-4': activeCondition(route().current(), 'dashboard'),
                                        }"
                                        @click="handleClick"
                                    >
                                        <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path
                                                d="M6.10322 0.956299H2.53135C1.5751 0.956299 0.787598 1.7438 0.787598 2.70005V6.27192C0.787598 7.22817 1.5751 8.01567 2.53135 8.01567H6.10322C7.05947 8.01567 7.84697 7.22817 7.84697 6.27192V2.72817C7.8751 1.7438 7.0876 0.956299 6.10322 0.956299ZM6.60947 6.30005C6.60947 6.5813 6.38447 6.8063 6.10322 6.8063H2.53135C2.2501 6.8063 2.0251 6.5813 2.0251 6.30005V2.72817C2.0251 2.44692 2.2501 2.22192 2.53135 2.22192H6.10322C6.38447 2.22192 6.60947 2.44692 6.60947 2.72817V6.30005Z"
                                                fill=""
                                            />
                                            <path
                                                d="M15.4689 0.956299H11.8971C10.9408 0.956299 10.1533 1.7438 10.1533 2.70005V6.27192C10.1533 7.22817 10.9408 8.01567 11.8971 8.01567H15.4689C16.4252 8.01567 17.2127 7.22817 17.2127 6.27192V2.72817C17.2127 1.7438 16.4252 0.956299 15.4689 0.956299ZM15.9752 6.30005C15.9752 6.5813 15.7502 6.8063 15.4689 6.8063H11.8971C11.6158 6.8063 11.3908 6.5813 11.3908 6.30005V2.72817C11.3908 2.44692 11.6158 2.22192 11.8971 2.22192H15.4689C15.7502 2.22192 15.9752 2.44692 15.9752 2.72817V6.30005Z"
                                                fill=""
                                            />
                                            <path
                                                d="M6.10322 9.92822H2.53135C1.5751 9.92822 0.787598 10.7157 0.787598 11.672V15.2438C0.787598 16.2001 1.5751 16.9876 2.53135 16.9876H6.10322C7.05947 16.9876 7.84697 16.2001 7.84697 15.2438V11.7001C7.8751 10.7157 7.0876 9.92822 6.10322 9.92822ZM6.60947 15.272C6.60947 15.5532 6.38447 15.7782 6.10322 15.7782H2.53135C2.2501 15.7782 2.0251 15.5532 2.0251 15.272V11.7001C2.0251 11.4188 2.2501 11.1938 2.53135 11.1938H6.10322C6.38447 11.1938 6.60947 11.4188 6.60947 11.7001V15.272Z"
                                                fill=""
                                            />
                                            <path
                                                d="M15.4689 9.92822H11.8971C10.9408 9.92822 10.1533 10.7157 10.1533 11.672V15.2438C10.1533 16.2001 10.9408 16.9876 11.8971 16.9876H15.4689C16.4252 16.9876 17.2127 16.2001 17.2127 15.2438V11.7001C17.2127 10.7157 16.4252 9.92822 15.4689 9.92822ZM15.9752 15.272C15.9752 15.5532 15.7502 15.7782 15.4689 15.7782H11.8971C11.6158 15.7782 11.3908 15.5532 11.3908 15.272V11.7001C11.3908 11.4188 11.6158 11.1938 11.8971 11.1938H15.4689C15.7502 11.1938 15.9752 11.4188 15.9752 11.7001V15.272Z"
                                                fill=""
                                            />
                                        </svg>
                                        Dashboard
                                        <svg
                                            :class="{
                                                'absolute right-4 top-1/2 -translate-y-1/2 fill-current':true,
                                                'transform rotate-180': open,
                                            }"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                clipRule="evenodd"
                                                d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                                fill=""
                                            />
                                        </svg>
                                    </a>

                                    <div :class="{ 'translate transform overflow-hidden': true, hidden: !open }">
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('dashboard')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white':true,
                                                        '!text-white': route().current('dashboard'),
                                                    }"
                                                >
                                                    Relatorio do dia
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                    <div 
                                        v-if="$page.props.permissions.includes('monthly-report') || $page.props.permissions.includes('view-campaigns')" 
                                        :class="{ 'translate transform overflow-hidden': true, hidden: !open }"
                                    >
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('dashboard.relatorio.mensal')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white': true,
                                                        '!text-white': route().current('dashboard.relatorio.mensal'),
                                                    }"
                                                >
                                                    Relatorio geral por data
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </SideBarLinkGroup>

                        <li v-if="$page.props.permissions.includes('manage-campaigns')">
                            <Link
                                :href="route('lead-distribution.index')"
                                :class="{
                                    'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                    'bg-graydark dark:bg-meta-4': route().current('lead-distribution.index') ,
                                }"
                            >
                                <svg
                                    class="fill-current"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 18 18"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path fill-rule="evenodd"
                                        d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z"
                                        clip-rule="evenodd">
                                    </path>
                                </svg>
                                Campanhas
                            </Link>
                        </li>

                        <li v-if="$page.props.permissions.includes('view-campaigns') && !$page.props.permissions.includes('manage-campaigns')">
                            <Link
                                :href="route('lead-distribution.viewOnly')"
                                :class="{
                                    'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                    'bg-graydark dark:bg-meta-4': route().current('lead-distribution.viewOnly'),
                                }"
                            >
                                <svg
                                    class="fill-current"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 18 18"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M9.0002 7.79065C11.0814 7.79065 12.7689 6.1594 12.7689 4.1344C12.7689 2.1094 11.0814 0.478149 9.0002 0.478149C6.91895 0.478149 5.23145 2.1094 5.23145 4.1344C5.23145 6.1594 6.91895 7.79065 9.0002 7.79065ZM9.0002 1.7719C10.3783 1.7719 11.5033 2.84065 11.5033 4.16252C11.5033 5.4844 10.3783 6.55315 9.0002 6.55315C7.62207 6.55315 6.49707 5.4844 6.49707 4.16252C6.49707 2.84065 7.62207 1.7719 9.0002 1.7719Z"
                                        fill=""
                                    />
                                    <path
                                        d="M10.8283 9.05627H7.17207C4.16269 9.05627 1.71582 11.5313 1.71582 14.5406V16.875C1.71582 17.2125 1.99707 17.5219 2.3627 17.5219C2.72832 17.5219 3.00957 17.2407 3.00957 16.875V14.5406C3.00957 12.2344 4.89394 10.3219 7.22832 10.3219H10.8564C13.1627 10.3219 15.0752 12.2063 15.0752 14.5406V16.875C15.0752 17.2125 15.3564 17.5219 15.7221 17.5219C16.0877 17.5219 16.3689 17.2407 16.3689 16.875V14.5406C16.2846 11.5313 13.8377 9.05627 10.8283 9.05627Z"
                                        fill=""
                                    />
                                </svg>
                                Campanhas
                            </Link>
                        </li>

                        <SideBarLinkGroup  v-if="$page.props.permissions.includes('manage-users')" :activeCondition="activeCondition(route().current(), 'usuarios')">
                            <template v-slot="{ handleClick, open }">
                                <div>
                                    <a
                                        href="#"
                                        :class="{
                                            'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                            'bg-graydark dark:bg-meta-4': activeCondition(route().current(), 'usuarios'),
                                        }"
                                        @click="handleClick"
                                    >
                                        <svg
                                            class="fill-current"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 18 18"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M9.0002 7.79065C11.0814 7.79065 12.7689 6.1594 12.7689 4.1344C12.7689 2.1094 11.0814 0.478149 9.0002 0.478149C6.91895 0.478149 5.23145 2.1094 5.23145 4.1344C5.23145 6.1594 6.91895 7.79065 9.0002 7.79065ZM9.0002 1.7719C10.3783 1.7719 11.5033 2.84065 11.5033 4.16252C11.5033 5.4844 10.3783 6.55315 9.0002 6.55315C7.62207 6.55315 6.49707 5.4844 6.49707 4.16252C6.49707 2.84065 7.62207 1.7719 9.0002 1.7719Z"
                                                fill=""
                                            />
                                            <path
                                                d="M10.8283 9.05627H7.17207C4.16269 9.05627 1.71582 11.5313 1.71582 14.5406V16.875C1.71582 17.2125 1.99707 17.5219 2.3627 17.5219C2.72832 17.5219 3.00957 17.2407 3.00957 16.875V14.5406C3.00957 12.2344 4.89394 10.3219 7.22832 10.3219H10.8564C13.1627 10.3219 15.0752 12.2063 15.0752 14.5406V16.875C15.0752 17.2125 15.3564 17.5219 15.7221 17.5219C16.0877 17.5219 16.3689 17.2407 16.3689 16.875V14.5406C16.2846 11.5313 13.8377 9.05627 10.8283 9.05627Z"
                                                fill=""
                                            />
                                        </svg>
                                        Usuarios
                                        <svg
                                            :class="{
                                                'absolute right-4 top-1/2 -translate-y-1/2 fill-current':true,
                                                'transform rotate-180': open,
                                            }"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                clipRule="evenodd"
                                                d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                                fill=""
                                            />
                                        </svg>
                                    </a>

                                    <div :class="{ 'translate transform overflow-hidden': true, hidden: !open }">
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('usuarios.create')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white':true,
                                                        '!text-white': route().current('usuarios.create'),
                                                    }"
                                                >
                                                    Criar
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                    <div 
                                        :class="{ 'translate transform overflow-hidden': true, hidden: !open }"
                                    >
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('usuarios.index')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white': true,
                                                        '!text-white': route().current('usuarios.index'),
                                                    }"
                                                >
                                                    Lista
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </SideBarLinkGroup>

                        <SideBarLinkGroup 
                            v-if="$page.props.permissions.includes('manage-groups')" 
                            :activeCondition="activeCondition(route().current(), 'grupos')"
                        >
                            <template v-slot="{ handleClick, open }">
                                <div>
                                    <a
                                        href="#"
                                        :class="{
                                            'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                            'bg-graydark dark:bg-meta-4': activeCondition(route().current(), 'grupos'),
                                        }"
                                        @click="handleClick"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 w-6 h-6 text-gray-600 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        Grupos
                                        <svg
                                            :class="{
                                                'absolute right-4 top-1/2 -translate-y-1/2 fill-current':true,
                                                'transform rotate-180': open,
                                            }"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                clipRule="evenodd"
                                                d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                                fill=""
                                            />
                                        </svg>
                                    </a>

                                    <div :class="{ 'translate transform overflow-hidden': true, hidden: !open }">
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('grupos.index')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white':true,
                                                        '!text-white': route().current('grupos.index'),
                                                    }"
                                                >
                                                    Listar
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                    <div 
                                        :class="{ 'translate transform overflow-hidden': true, hidden: !open }"
                                    >
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('grupos.create')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white': true,
                                                        '!text-white': route().current('grupos.create'),
                                                    }"
                                                >
                                                    Criar
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </SideBarLinkGroup>

                        <SideBarLinkGroup 
                        v-if="$page.props.permissions.includes('configurate')"
                            :activeCondition="activeCondition(route().current(), 'configuracao')"
                        >
                            <template v-slot="{ handleClick, open }">
                                <div>
                                    <a
                                        href="#"
                                        :class="{
                                            'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                            'bg-graydark dark:bg-meta-4': activeCondition(route().current(), 'configuracao'),
                                        }"
                                        @click="handleClick"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 w-6 h-6 text-gray-600 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Configurações
                                        <svg
                                            :class="{
                                                'absolute right-4 top-1/2 -translate-y-1/2 fill-current':true,
                                                'transform rotate-180': open,
                                            }"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                clipRule="evenodd"
                                                d="M4.41107 6.9107C4.73651 6.58527 5.26414 6.58527 5.58958 6.9107L10.0003 11.3214L14.4111 6.91071C14.7365 6.58527 15.2641 6.58527 15.5896 6.91071C15.915 7.23614 15.915 7.76378 15.5896 8.08922L10.5896 13.0892C10.2641 13.4147 9.73651 13.4147 9.41107 13.0892L4.41107 8.08922C4.08563 7.76378 4.08563 7.23614 4.41107 6.9107Z"
                                                fill=""
                                            />
                                        </svg>
                                    </a>

                                    <div :class="{ 'translate transform overflow-hidden': true, hidden: !open }">
                                        <ul class="mt-4 mb-5.5 flex flex-col gap-2.5 pl-6">
                                            <li>
                                                <Link
                                                    :href="route('configuracao.leads')"
                                                    :class="{
                                                        'group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white':true,
                                                        '!text-white': route().current('configuracao.leads'),
                                                    }"
                                                >
                                                Leads por usuario
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </template>
                        </SideBarLinkGroup>

                        <li 
                            v-if="!$page.props.permissions.includes('view-campaigns') || envMode == 'development'"
                        >
                            <Link
                                :href="route('lead-distribution.handle.index')"
                                :class="{
                                    'group relative flex items-center gap-2.5 rounded-sm py-2 px-4 font-medium text-bodydark1 duration-300 ease-in-out hover:bg-graydark dark:hover:bg-meta-4': true,
                                    'bg-graydark dark:bg-meta-4': route().current('lead-distribution.handle.index') ,
                                }"
                            >
                                <svg
                                    class="fill-current"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 18 18"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M9.0002 7.79065C11.0814 7.79065 12.7689 6.1594 12.7689 4.1344C12.7689 2.1094 11.0814 0.478149 9.0002 0.478149C6.91895 0.478149 5.23145 2.1094 5.23145 4.1344C5.23145 6.1594 6.91895 7.79065 9.0002 7.79065ZM9.0002 1.7719C10.3783 1.7719 11.5033 2.84065 11.5033 4.16252C11.5033 5.4844 10.3783 6.55315 9.0002 6.55315C7.62207 6.55315 6.49707 5.4844 6.49707 4.16252C6.49707 2.84065 7.62207 1.7719 9.0002 1.7719Z"
                                        fill=""
                                    />
                                    <path
                                        d="M10.8283 9.05627H7.17207C4.16269 9.05627 1.71582 11.5313 1.71582 14.5406V16.875C1.71582 17.2125 1.99707 17.5219 2.3627 17.5219C2.72832 17.5219 3.00957 17.2407 3.00957 16.875V14.5406C3.00957 12.2344 4.89394 10.3219 7.22832 10.3219H10.8564C13.1627 10.3219 15.0752 12.2063 15.0752 14.5406V16.875C15.0752 17.2125 15.3564 17.5219 15.7221 17.5219C16.0877 17.5219 16.3689 17.2407 16.3689 16.875V14.5406C16.2846 11.5313 13.8377 9.05627 10.8283 9.05627Z"
                                        fill=""
                                    />
                                </svg>
                                Minha Lista
                            </Link>
                        </li>
                        
                    </ul>
                </div>

            </nav>
            <!-- Sidebar Menu -->
        </div>
    </aside>
</template>
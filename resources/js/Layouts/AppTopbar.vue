<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/Layouts/composables/layout';
import { Link, router } from '@inertiajs/vue3';

const { layoutConfig, onMenuToggle } = useLayout();

const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    return `/images/logo-secundaria.png`;
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
};

const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value
    };
});

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};

const menu = ref(null);

const toggleMenu = (event) => {
    menu.value.toggle(event);
};

const overlayMenuItems = ref([
    {
        label: 'Sair',
        icon: 'pi pi-fw pi-sign-out',
        method: logout
    },
]);
</script>

<template>
    <div class="layout-topbar" style="background-color: #044b57;">
        <Link href="/" class="layout-topbar-logo" style="width: fit-content;">
            <img :src="logoUrl" alt="logo" style="height: 4rem !important; "/>
        </Link>

        <button class="p-link layout-menu-button layout-topbar-button text-white hover:bg-none" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>

        <button class="p-link layout-topbar-menu-button layout-topbar-button text-white" @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <button @click="toggleMenu" class="p-link layout-topbar-button">
                <i class="pi pi-user text-white"></i>
                <span>Profile</span>
            </button>
            <Menu ref="menu" :model="overlayMenuItems" :popup="true">
                <template #item="{ item, props }">
                    <a v-ripple class="flex items-center" v-bind="props.action" @click="item.method">
                        <span :class="item.icon + ' mr-2'" />
                        <span>{{ item.label }}</span>
                    </a>
                </template>
            </Menu>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>

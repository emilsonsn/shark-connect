<script setup>
import { Head, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import SideBar from '../Components/SideBar.vue';
import Header from '../Components/Header.vue';

defineProps({
    title: String,
});

const logout = () => {
    router.post(route('logout'));
};

</script>

<template>
    <div class="dark:bg-boxdark-2 dark:text-bodydark text-base text-body bg-whiten relative z-1">

        <Head :title="title" />

        <Banner />

        <div className="flex h-screen overflow-hidden">
            <SideBar/>

            <div className="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">

                <Header></Header>
                <main>
                    <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                        <slot />
                    </div>
                </main>
            </div>
        </div>

       
    </div>

</template>

<style scoped>
.notification-icon {
  position: relative;
  display: inline-block;
}

.notification-badge {
  position: absolute;
  top: -6px;
  right: -8px;
  background-color: red;
  color: white;
  border-radius: 50%;
  padding: 0.5px 5px;
  font-size: 11px;
}

</style>

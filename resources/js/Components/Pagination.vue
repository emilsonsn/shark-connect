<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: "Pagination",
    props: {
        data: Object,
    },
    computed: {
        cleanLinks() {
            const cleanLinks = [...this.data.links];
            cleanLinks.shift();
            cleanLinks.pop();
            return cleanLinks;
        },
    },
    components: { Link }
};
</script>
<template>
    <nav>
        <ul class="inline-flex align-items-center list-none bg-white pl-0">
            <li
                v-if="data.prev_page_url"
            >
                <Link 
                    :href="data.prev_page_url"
                    class="flex h-2rem w-2rem align-items-center justify-content-center text-primary border-round-left-md border-1 border-gray-200 hover:border-primary hover:bg-gray-200 hover:text-primary"
                >
                    <svg aria-hidden="true" class="w-1rem h-1rem" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd">
                        </path>
                    </svg>
                </Link>
            </li>
            
            <li
                v-for="link in cleanLinks"
                :key="link.label"
            >
                <Link 
                    :href="link.url"
                    :class="[
                        link.active
                            ? 'flex align-items-center justify-content-center border-1 border-primary bg-white text-primary py-2 px-3 font-medium border-round'
                            : 'flex align-items-center justify-content-center border-1 border-gray-200 px-4 text-primary font-medium hover:border-primary hover:bg-gray-200 hover:text-primary'
                    ]"
                    style="padding-top: 5px; padding-bottom: 5px;"
                >
                    {{ link.label }}
                </Link>
            </li>

            <li
                v-if="data.next_page_url"
            >
                <Link 
                    :href="data.next_page_url"
                    class="flex h-2rem w-2rem align-items-center justify-content-center text-primary border-round-right-md border-1 hover:border-primary hover:bg-gray-200 hover:text-primary">
                    <svg aria-hidden="true" class="w-1rem h-1rem" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </Link>
            </li>
        </ul>
    </nav>
</template>
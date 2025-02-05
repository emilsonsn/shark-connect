<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center px-4 py-2 bg-meta-1 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-meta-1 active:bg-meta-1 focus:outline-none focus:ring-2 focus:ring-meta-1 focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </button>
</template>

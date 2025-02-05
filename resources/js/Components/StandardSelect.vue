<script setup>

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);
</script>
<template>
    <select 
        :value="modelValue" 
        class="rounded-lg border-[1.5px] border-stroke bg-white py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
        @change="$emit('update:modelValue', $event.target.value)"
    >
        <slot></slot>
    </select>
</template>
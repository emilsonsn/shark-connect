<template>
    <li>
        <slot :handleClick="handleClick" :open="open"></slot>
    </li>
</template>
  
<script setup>
import { ref } from 'vue';

const props = defineProps({
    activeCondition: Boolean,
});

const open = ref(props.activeCondition);

const handleClick = () => {
    open.value = !open.value;
};
</script>
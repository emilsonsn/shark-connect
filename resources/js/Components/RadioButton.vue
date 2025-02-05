<script setup>
import { computed } from 'vue';


const props = defineProps({
    color: {
        type: String,
        default: 'primary'
    },
    modelValue: [String, Number],
})

const colorComp = computed(() => {
    if(props.color == 'red'){
        return 'meta-1'
    }else if(props.color == 'green'){
        return 'meta-3'
    }else if(props.color == 'yellow'){
        return 'meta-6'
    }else if(props.color == 'blue'){
        return 'primary'
    }
})

const classes = computed(() => {
    return [
        'w-4 h-4 bg-gray-100 border-gray-300 text-meta-1 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600',
        `focus:ring-${colorComp.value}`,
        `dark:focus:ring-${colorComp.value}`,
        `text-${colorComp.value}`
    ]
})

defineEmits(['update:modelValue']);

</script>

<template>
    <input type="radio" 
        :class=" classes "

    >
</template>
<script setup>
import { computed, defineProps, ref } from 'vue';

const props = defineProps({
    tooltip: {
        type: String,
        default: ''
    },
    withTooltip: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: 'primary'
    }
});

const showTooltip = ref(false);

let tooltipTimeout;

const startTimer = () => {
    tooltipTimeout = setTimeout(() => {
        showTooltip.value = true;
    }, 2000);
};

const clearTimer = () => {
    clearTimeout(tooltipTimeout);
    showTooltip.value = false;
};

const computedClasses = computed(()=>{
    const base = 'w-10 h-10 rounded-full flex items-center justify-center'

    const colors = {
        primary: 'bg-primary hover:bg-secondary',
        secondary: 'bg-secondary hover:bg-primary',
        red: 'bg-meta-1 hover:bg-[#bd2130]',
        green: 'bg-meta-3 hover:bg-[#0c8a60]',
    };

    return colors[props.color] + ' ' + base;

})
</script>

<template>
    <div class="relative inline-block">
        <button
            @mouseenter="startTimer"
            @mouseleave="clearTimer"
            :class="computedClasses"
        >
        
            <slot name="icon"/>

            <div v-if="withTooltip" class="tooltip" :class="{ 'show': showTooltip}">
                {{ tooltip }}
            </div>

        </button>
    </div>
   
</template>

<style scoped>
.tooltip {
  opacity: 0;
  background-color: #3490dc;
  color: #fff;
  text-align: center;
  position: absolute;
  top: 50%;
  right: calc(100% + 1.5rem); /* Position to the right of the button */
  transform: translate(50%, 50%); /* Center vertically and horizontally */
  transition: opacity 0.3s;
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
}

.tooltip.show {
  opacity: 1;
}
</style>
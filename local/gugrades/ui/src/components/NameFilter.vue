<template>
    <div class="mt-4">
        <InitialBar label="First name" @selected="first_selected"></InitialBar>
        <InitialBar label="Last name" @selected="last_selected"></InitialBar>
    </div>
</template>

<script setup>
    import {ref, defineEmits} from '@vue/runtime-core';
    import InitialBar from '@/components/InitialBar.vue';

    const emit = defineEmits(['selected']);

    const first = ref('all');
    const last = ref('all');

    /**
     * Process letter selected in one of the bars
     */
    function first_selected(letter) {
        first.value = letter;
        emit('selected', first.value, last.value);
    }

    function last_selected(letter) {
        last.value = letter;
        emit('selected', first.value, last.value);
    }
</script>
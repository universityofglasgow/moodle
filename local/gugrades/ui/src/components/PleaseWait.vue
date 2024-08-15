<template>
    <div class="d-flex justify-content-center">
        <div class="border rounded m-1 p-2 text-center">
            <p>{{ mstrings.pleasewait }}</p>
            <VueSpinner size="50" color="#005c8a"></VueSpinner>
        </div>
    </div>
</template>

<script setup>
    import {inject} from '@vue/runtime-core';
    import { VueSpinner } from 'vue3-spinners';

    const mstrings = inject('mstrings');
</script>
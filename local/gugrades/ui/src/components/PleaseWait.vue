<template>
    <VueModal v-model="showmodal" enableClose="false" modalClass="col-3 col-lg-2 rounded" :title="mstrings.pleasewait">
        <div class="d-flex justify-content-center">
            <div class="border rounded m-1 p-2 text-center">
                <p>{{ mstrings.pleasewait }}</p>
                <VueSpinner size="50" color="#005c8a"></VueSpinner>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, inject, onMounted, onUnmounted} from '@vue/runtime-core';
    import { VueSpinner } from 'vue3-spinners';

    const mstrings = inject('mstrings');
    const showmodal = ref(false);

    onMounted(() => {
        showmodal.value = true;
    });


    onUnmounted(() => {
        showmodal.value = false;
    });
</script>
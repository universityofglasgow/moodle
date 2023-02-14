<template>
    <ul class="nav nav-pills mt-4">
        <li class="nav-item">
            <a class="nav-link" :class="{active: isCapture}" @click="clickTab('capture')"><MString name="assessmentgradecapture"></MString></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" :class="{active: !isCapture}" @click="clickTab('aggregate')"><MString name="coursegradeaggregation"></MString></a>
        </li>
    </ul> 
</template>

<script setup>
    import {ref, defineEmits} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';

    const isCapture = ref(true);

    const emit = defineEmits(['tabchange']);

    /**
     * Detect change of tab and emit result to parent
     * @param {} item 
     */
    function clickTab(item) {
        if (item == 'aggregate') {
            isCapture.value = false;
            emit('tabchange', 'aggregate');
        } else {
            isCapture.value = true;
            emit('tabchange', 'capture')
        }
    }
</script>
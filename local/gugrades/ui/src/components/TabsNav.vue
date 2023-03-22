<template>
    <ul class="nav nav-pills mb-4 border-bottom">
        <li class="nav-item">
            <a class="nav-link" :class="{active: activetab == 'capture'}" @click="clickTab('capture')">
                <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
                <MString name="assessmentgradecapture"></MString>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" :class="{active: activetab == 'aggregate'}" @click="clickTab('aggregate')">
                <i class="fa fa-compress" aria-hidden="true"></i>&nbsp;
                <MString name="coursegradeaggregation"></MString>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" :class="{active: activetab == 'settings'}" @click="clickTab('settings')">
                <i class="fa fa-cog" aria-hidden="true"></i>&nbsp;
                <MString name="settings"></MString>
            </a>
        </li>        
    </ul> 
</template>

<script setup>
    import {ref, defineEmits} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';

    const activetab = ref('capture');

    const emit = defineEmits(['tabchange']);

    /**
     * Detect change of tab and emit result to parent
     * @param {} item 
     */
    function clickTab(item) {
        activetab.value = item;
        emit('tabchange', item);
    }
</script>
<template>
    <div>
        <h1><MString name="captureaggregation"></MString></h1>
        <div class="row">
            <div class="col">
                <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
            </div>
            <div class="col">
                <router-link to="/settings" class="btn btn-primary"><MString name="settings"></MString></router-link>
            </div>
        </div>

        <TabsNav @tabchange="tabChange"></TabsNav>

        <div v-if="currenttab == 'capture'">
            <ActivitySelect></ActivitySelect>
        </div>
    </div>
</template>

<script setup>
    import {ref} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import TabsNav from '@/components/TabsNav.vue';
    import ActivitySelect from '@/components/ActivitySelect.vue';

    const currenttab = ref('capture');

    /**
     * Capture change to top level category dropdown
     * @param {*} level 
     */
    function levelOneChange(level) {
        window.console.log(level);
    }

    /**
     * Capture change to capture/aggregate tab
     * @param {*} tab 
     */
    function tabChange(tab) {
        currenttab.value = tab;
        window.console.log(tab);
    }
</script>
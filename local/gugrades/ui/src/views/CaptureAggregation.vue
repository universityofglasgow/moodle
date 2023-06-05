<template>
    <div>
        <h1><MString name="captureaggregation"></MString></h1>

        <TabsNav @tabchange="tabChange"></TabsNav>

        <div v-if="currenttab == 'capture' || currenttab == 'aggregate'" class="row">
            <div class="col-12 col-lg-6">
                <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
                <div v-if="currenttab == 'capture'">
                    <ActivitySelect v-if="showactivityselect" :categoryid="level1category" @activityselected="activity_selected"></ActivitySelect>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                &nbsp;
            </div>
        </div>

        <div v-if="currenttab == 'settings'">
            <SettingsPage></SettingsPage>
        </div>

        <div v-if="currenttab == 'audit'">
            <AuditPage></AuditPage>
        </div>

        <CaptureTable v-if="itemid && (currenttab == 'capture')" :itemid="itemid"></CaptureTable>
    </div>
</template>

<script setup>
    import {ref} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import TabsNav from '@/components/TabsNav.vue';
    import ActivitySelect from '@/components/ActivitySelect.vue';
    import CaptureTable from '@/components/CaptureTable.vue';
    import SettingsPage from '@/views/SettingsPage.vue';
    import AuditPage from '@/views/AuditPage.vue';

    const currenttab = ref('capture');
    const level1category = ref(0);
    const showactivityselect = ref(false);
    const itemid = ref(0);

    /**
     * Capture change to top level category dropdown
     * @param {*} level 
     */
    function levelOneChange(level) {
        itemid.value = 0;
        level1category.value = parseInt(level);
        if (level1category.value) {
            showactivityselect.value = true;
        } else {
            showactivityselect.value = false;
        }
    }

    /**
     * Capture change to activity selection
     */
    function activity_selected(newitemid) {
        itemid.value = Number(newitemid);
    }

    /**
     * Capture change to capture/aggregate tab
     * @param {*} tab 
     */
    function tabChange(tab) {
        currenttab.value = tab;
    }
</script>
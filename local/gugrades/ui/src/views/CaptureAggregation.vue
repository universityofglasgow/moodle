<template>
    <div>
        <h1><MString name="captureaggregation"></MString></h1>

        <TabsNav @tabchange="tabChange"></TabsNav>

        <div class="row">
            <div class="col">
                <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
                <div v-if="currenttab == 'capture'">
                    <ActivitySelect v-if="showactivityselect" :categoryid="level1category" @activityselected="activity_selected"></ActivitySelect>
                </div>
            </div>
            <div class="col">
                <router-link to="/settings" class="btn btn-primary"><MString name="settings"></MString></router-link>
            </div>
        </div>

        <CaptureTable v-if="showtable && (currenttab == 'capture')" :users="users"></CaptureTable>
    </div>
</template>

<script setup>
    import {ref} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import TabsNav from '@/components/TabsNav.vue';
    import ActivitySelect from '@/components/ActivitySelect.vue';
    import CaptureTable from '@/components/CaptureTable.vue';

    const currenttab = ref('capture');
    const level1category = ref(0);
    const showactivityselect = ref(false);
    const showtable = ref(false);
    const users = ref([]);

    /**
     * Capture change to top level category dropdown
     * @param {*} level 
     */
    function levelOneChange(level) {
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
    function activity_selected(itemid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;        

        fetchMany([{
            methodname: 'local_gugrades_get_capture_page',
            args: {
                courseid: courseid,
                gradeitemid: itemid,
                pageno: 0,
                pagelength: 0,
            }
        }])[0]
        .then((result) => {
            users.value = JSON.parse(result['users']);
            showtable.value = true;
            window.console.log(users.value);
        })
        .catch((error) => {
            window.console.log(error);
        });
    }

    /**
     * Capture change to capture/aggregate tab
     * @param {*} tab 
     */
    function tabChange(tab) {
        currenttab.value = tab;
    }
</script>
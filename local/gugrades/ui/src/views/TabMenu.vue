<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <GreyLogo></GreyLogo>

    <div v-if="!available" class="alert alert-danger">
        MyGrades cannot be used in this course as it has too many enrolled participants.
    </div>
    <div v-else id="tabmenu">
        <TabsNav @tabchange="tabChange" :viewaggregation="viewaggregation"></TabsNav>

        <div v-if="currenttab == 'configure'">
            <ConfigPage></ConfigPage>
        </div>

        <div v-if="currenttab == 'capture'">
            <CaptureTable></CaptureTable>
        </div>

        <div v-if="currenttab == 'conversion'">
            <ConversionPage></ConversionPage>
        </div>

        <div v-if="(currenttab == 'aggregation') && viewaggregation">
            <AggregationTable></AggregationTable>
        </div>

        <div v-if="currenttab == 'settings'">
            <SettingsPage></SettingsPage>
        </div>

        <div v-if="currenttab == 'audit'">
            <AuditPage></AuditPage>
        </div>
    </div>
</template>

<script setup>
    import {ref, onMounted} from '@vue/runtime-core';
    import TabsNav from '@/components/TabsNav.vue';
    import ConfigPage from '@/views/ConfigPage.vue';
    import CaptureTable from '@/views/CaptureTable.vue';
    import AggregationTable from '@/views/AggregationTable.vue';
    import ConversionPage from '@/views/ConversionPage.vue';
    import SettingsPage from '@/views/SettingsPage.vue';
    import AuditPage from '@/views/AuditPage.vue';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import GreyLogo from '@/components/GreyLogo.vue';

    const currenttab = ref('capture');
    const level1category = ref(0);
    const showactivityselect = ref(false);
    const itemid = ref(0);
    const enabledashboard = ref(false);
    const viewaggregation = ref(true);
    const available = ref(true);
    const debug = ref({});

    const toast = useToast();

    /**
     * Capture change to capture/aggregate tab
     * @param {*} tab
     */
    function tabChange(tab) {
        currenttab.value = tab;
        level1category.value = 0;
        showactivityselect.value = false;
        itemid.value = 0;
    }

    /**
     * Check for aggregation tab permission
     */
     onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Check that MyGrades is available for this course at all.
        fetchMany([{
            methodname: 'local_gugrades_is_mygrades_available',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            available.value = result.available;
        })
        .catch((error) => {
            window.console.log(error);
            debug.value = error;
        });

        // Check capability to use the aggregation tab.
        fetchMany([{
            methodname: 'local_gugrades_has_capability',
            args: {
                courseid: courseid,
                capability: 'local/gugrades:viewaggregation'
            }
        }])[0]
        .then((result) => {
            viewaggregation.value = result.hascapability;
        })
        .catch((error) => {
            window.console.log(error);
            debug.value = error;
        });
    })
</script>
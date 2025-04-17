<template>
    <div id="tabmenu">
        <TabsNav @tabchange="tabChange"></TabsNav>

        <div v-if="currenttab == 'capture'">
            <CaptureTable></CaptureTable>
        </div>

        <div v-if="currenttab == 'conversion'">
            <ConversionPage></ConversionPage>
        </div>

        <div v-if="currenttab == 'aggregation'">
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
    import CaptureTable from '@/views/CaptureTable.vue';
    import AggregationTable from '@/views/AggregationTable.vue';
    import ConversionPage from '@/views/ConversionPage.vue';
    import SettingsPage from '@/views/SettingsPage.vue';
    import AuditPage from '@/views/AuditPage.vue';
    import { useToast } from "vue-toastification";

    const currenttab = ref('capture');
    const level1category = ref(0);
    const showactivityselect = ref(false);
    const itemid = ref(0);
    const enabledashboard = ref(false);

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
     * get enable dashboard and set logo
     */
     onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_settings',
            args: {
                courseid: courseid,
                gradeitemid: 0,
            }
        }])[0]
        .then((settings) => {
            settings.forEach((setting) => {

                // TODO: Something a bit cleverer than this
                if (setting.name == 'enabledashboard') {
                    enabledashboard.value = setting.value ? true : false;
                }
            });

            // Bodge to get jQuery needed for Bootstrap JS.
            const $ = window.jQuery;

            if (enabledashboard.value) {
                $('#mygradeslogo').css('filter', 'grayscale(0)');
            } else {
                $('#mygradeslogo').css('filter', 'grayscale(1)');
            }
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    })
</script>
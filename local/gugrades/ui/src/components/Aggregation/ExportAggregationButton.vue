<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="open_modal()">{{ mstrings.exportaggregation }}</button>

    <VueModal v-model="showexportmodal" enableClose="false" modalClass="col-11 col-lg-6 rounded" :title="mstrings.exportaggregation">

        <PleaseWait v-if="pleasewait"></PleaseWait>

        <div class="mb-5">
            <FormKit
                type="select"
                :label="mstrings.selectexport"
                :options="plugins"
                v-model="selectedplugin"
            ></FormKit>
            <button class="btn btn-primary mt-2" type="button" @click="plugin_selected">{{  mstrings.next }}</button>
        </div>

        <div class="row mt-2">
            <div class="col-sm-12">
                <div class="float-right">
                    <button class="btn btn-warning" type="button" @click="close_modal()">{{  mstrings.cancel }}</button>
                </div>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, inject, watch} from '@vue/runtime-core';
    import PleaseWait from '@/components/PleaseWait.vue';
    import { useToast } from "vue-toastification";
    import { saveAs } from 'file-saver';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showexportmodal = ref(false);
    const allnone = ref(false);
    const pleasewait = ref(false);
    const options = ref([]);
    const plugins = ref([]);
    const selectedplugin = ref('');
    const mstrings = inject('mstrings');

    const toast = useToast();

    const props = defineProps({
        categoryid: Number,
        groupid: Number,
        itemname: String,
    });

    /**
     * Load initial plugin options
     */
    function open_modal() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        pleasewait.value = false;

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_export_plugins',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
            }
        }])[0]
        .then((result) => {
            const options = result;
            plugins.value = [];
            options.forEach(option => {
                plugins.value.push({
                    label: option.description,
                    value: option.name,
                });
            });
        })
        .catch((error) => {
            showexportmodal.value = false;
            debug.value = error;
        });

        showexportmodal.value = true;
    }

    /**
     * Plugin type has been selected
     * Get the settings form for selected (if there is one)
     */
    function plugin_selected() {
        window.console.log(selectedplugin.value);
    }

    /**
     * Watch for all/none changing
     */
    watch(allnone, (newallnone) => {
        options.value.forEach((option) => {
            option.selected = newallnone;
        });
    });

    /**
     * Convert options to version required
     * for web service
     */
    function get_data_options(options) {
        let newoptions = [];
        options.forEach((option) => {
            newoptions.push({
                gradetype: option.gradetype,
                other: option.other,
                selected: option.selected
            });
        });

        return newoptions;
    }

    /**
     * Download the pro-forma csv file
     */
    function submit_export_form() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        pleasewait.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_export_data',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
                viewfullnames: props.revealnames,
                options: get_data_options(options.value),
            }
        }])[0]
        .then((result) => {
            const csv = result['csv'];
            const d = new Date();
            const filename = props.itemname + '_' + d.toLocaleString() + '.csv';
            const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
            saveAs(blob, filename);

            showexportmodal.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            showexportmodal.value = false;
            debug.value = error;
        });
    }



    /**
     * Close the modal
     */
    function close_modal() {
        showexportmodal.value = false;
    }
</script>

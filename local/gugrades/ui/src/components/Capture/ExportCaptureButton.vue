<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="open_modal()">{{ mstrings.exportcapture }}</button>

    <VueModal v-model="showexportmodal" enableClose="false" modalClass="col-11 col-lg-6 rounded" :title="mstrings.exportcapture">

        <div class="alert alert-info">
            {{  mstrings.exportcapturehelp }}
        </div>

        <PleaseWait v-if="pleasewait"></PleaseWait>

        <FormKit
            v-if="!pleasewait"
            type="form"
            :submit-label="mstrings.export"
            @submit="submit_export_form"
        >
            <FormKit
                v-model="allnone"
                type="checkbox"
                :label="mstrings.allnone"
            />
            <div class="mb-1">&nbsp;</div>
            <FormKit
                v-for="option in options"
                type="checkbox"
                v-model="option.selected"
                :label="option.description"
            />
        </FormKit>

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
    const mstrings = inject('mstrings');

    const toast = useToast();

    const props = defineProps({
        itemid: Number,
        groupid: Number,
        itemname: String,
        revealnames: Boolean,
    });

    /**
     * Load initial options
     */
    function open_modal() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        pleasewait.value = false;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_export_options',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
            }
        }])[0]
        .then((result) => {
            options.value = result;
        })
        .catch((error) => {
            showexportmodal.value = false;
            debug.value = error;
        });

        showexportmodal.value = true;
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

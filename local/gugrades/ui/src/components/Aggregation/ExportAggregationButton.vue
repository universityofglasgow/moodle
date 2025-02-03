<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="open_modal()">{{ mstrings.exportaggregation }}</button>

    <VueModal v-model="showexportmodal" enableClose="false" modalClass="col-11 col-lg-5 rounded scrollable-modal" :title="mstrings.exportaggregation">

        <PleaseWait v-if="pleasewait"></PleaseWait>

        <!-- step to select plugin and filename -->
        <div v-if="step == 'selectplugin'" class="mb-5">
            <FormKit
                type="form"
                @submit="plugin_selected()"
                :submit-label="mstrings.next"
            >
                <FormKit
                    type="select"
                    :label="mstrings.selectexport"
                    :options="plugins"
                    v-model="selectedplugin"
                ></FormKit>

                <FormKit
                    class="mt-2"
                    type="text"
                    :label="mstrings.exportfilename"
                    validation="required"
                    validation-visibility="live"
                    v-model="filename"
                ></FormKit>
            </FormKit>
        </div>

        <!-- step to select form fields-->
        <div v-if="(step == 'selectfields') && hasform" class="mb-5 scrollable-content">
            <FormKit
                type="form"
                @submit="fields_selected()"
                :submit-label="mstrings.export"
            >

                <div class="alert alert-primary">{{ mstrings.selectfields }}</div>

                <div class="mb-2">
                    <a href="#" class="btn btn-sm btn-info mr-1" @click="all_selected">{{ mstrings.checkall }}</a>
                    <a href="#" class="btn btn-sm btn-secondary" @click="none_selected">{{ mstrings.checknone }}</a>
                </div>

                <FormKit
                    v-for="field in form"
                    type="checkbox"
                    :label="field.description"
                    :label-class="field.category ? 'font-weight-bold' : ''"
                    v-model="selected[field.identifier]"
                />
            </FormKit>
        </div>

        <!-- alternatively -->
        <div v-if="(step == 'selectfields') && !hasform" class="mb-5 scrollable-content">
            <div class="alert alert-primary">{{ mstrings.noselectfields }}</div>
            <button class="btn btn-primary" type="button" @click="fields_selected()">{{  mstrings.next }}</button>
        </div>

        <div class="row scrollable-modal-footer">
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
    const selectedplugin = ref('custom');
    const mstrings = inject('mstrings');
    const debug = ref({});
    const step = ref('selectplugin');
    const hasform = ref(false);
    const form = ref([]);
    const selected = ref({});
    const filename = ref('');

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

        pleasewait.value = true;
        step.value = 'selectplugin';

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_export_plugins',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
            }
        }])[0]
        .then((result) => {
            const options = result.plugins;
            plugins.value = [];
            options.forEach(option => {
                plugins.value.push({
                    label: option.description,
                    value: option.name,
                });
            });
            filename.value = result.filename;
            pleasewait.value = false;
        })
        .catch((error) => {
            showexportmodal.value = false;
            debug.value = error;
        });

        showexportmodal.value = true;
    }

    /**
     * Initialise selected array
     */
    function initialise_selected() {
        form.value.forEach(field => {
            selected.value[field.identifier] = field.selected;
        });
    }

    /**
     * Make all fields selected
     */
    function all_selected() {
        form.value.forEach(field => {
            selected.value[field.identifier] = true;
        });
    }

    /**
     * Make all fields unselected
     */
     function none_selected() {
        form.value.forEach(field => {
            selected.value[field.identifier] = false;
        });
    }

    /**
     * Plugin type has been selected
     * Get the settings form for selected (if there is one)
     */
    function plugin_selected() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        pleasewait.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_export_form',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
                plugin: selectedplugin.value,
            }
        }])[0]
        .then(result => {
            hasform.value = result.hasform;
            form.value = result.form;
            if (hasform.value) {
                initialise_selected();
            }
            pleasewait.value = false;
            step.value = "selectfields";
        })
        .catch((error) => {
            showexportmodal.value = false;
            debug.value = error;
        });
    }

    /**
     * Fields required have been selected on form
     * (If the plugin has a form)
     *
     */
    function fields_selected() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        pleasewait.value = true;

        // Munge selected array into required form.
        const paramform = [];
        for (const [identifier, isselect] of Object.entries(selected.value)) {
            paramform.push({
                identifier: identifier,
                selected: isselect,
            });
        }

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_export_data',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
                plugin: selectedplugin.value,
                groupid: props.groupid,
                form: paramform,
            }
        }])[0]
        .then(result => {
            const csv = result['csv'];
            const d = new Date();
            const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
            saveAs(blob, filename.value + '.csv');

            showexportmodal.value = false;
        })
        .catch((error) => {
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

<style>
    .scrollable-modal {
    display: flex;
    flex-direction: column;
    height: calc(100% - 150px);
    }
    .scrollable-modal .vm-titlebar {
    flex-shrink: 0;
    }
    .scrollable-modal .vm-content {
    padding: 0;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    }
    .scrollable-modal .vm-content .scrollable-content {
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 15px 10px 15px;
    flex-grow: 1;
    }
    .scrollable-modal .scrollable-modal-footer {
    padding: 15px 0px 15px 0px;
    border-top: 1px solid #e5e5e5;
    margin-left: 0;
    margin-right: 0;
    }
</style>

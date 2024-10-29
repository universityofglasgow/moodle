<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="add_multiple_button_click()">
        {{ mstrings.addmultiple }}
    </button>

    <VueModal v-model="showaddmultiplemodal" enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="mstrings.addmultiple">
        <FormKit class="border rounded" type="form" @submit="submit_form">
            <FormKit
                type="select"
                :label="mstrings.reasonforadditionalgrade"
                name="reason"
                v-model="reason"
                :options="gradetypes"
                :placeholder="mstrings.selectareason"
                validation="required"
            />
            <FormKit
                v-if = 'reason == "OTHER"'
                :label="mstrings.pleasespecify"
                type="text"
                :placeholder="mstrings.pleasespecify"
                name="other"
                v-model="other"
            />
            <FormKit
                type="textarea"
                label="Notes"
                :placeholder="mstrings.reasonforammendment"
                name="notes"
                v-model="notes"
            />
        </FormKit>

        <div class="row mt-2">
            <div class="col-sm-12">
                <div class="float-right">
                    <button class="btn btn-warning" type="button" @click="showaddmultiplemodal = false">{{  mstrings.cancel }}</button>
                </div>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showaddmultiplemodal = ref(false);
    const mstrings = inject('mstrings');
    const gradetypes = ref({});
    const reason = ref('');
    const notes = ref('');
    const other = ref('');
    const debug = ref({});

    const emits = defineEmits([
        'editcolumn'
    ]);

    const toast = useToast();

    const props = defineProps({
        itemid: Number,
    });

    /**
     * Button clicked
     */
    function add_multiple_button_click() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Clear for new form
        other.value = '';
        reason.value = '';

        fetchMany([{
            methodname: 'local_gugrades_get_gradetypes',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
            }
        }])[0]
        .then((result) => {
            gradetypes.value = result.gradetypes;
        })
        .catch((error) => {
            window.console.error(error);
            showaddmultiplemodal.value = false;
            debug.value = error;
        });

        showaddmultiplemodal.value = true;
    }

    /**
     * Get all the details for the cell forms
     * This is called immediately after the submit_form() promise
     * completes.
     */
     function get_capture_cell_form(columnid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_cell_form',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
            }
        }])[0]
        .then((result) => {
            const usescale = result.usescale;
            const grademax = result.grademax;
            const scalemenu = result.scalemenu;
            const adminmenu = result.adminmenu;

            // Add 'use grade' option onto front of adminmenu
            adminmenu.unshift({
                value: 'GRADE',
                label: mstrings.selectnormalgradeshort,
            });

            // send all this stuff back
            emits('editcolumn', {
                columnname: 'GRADE' + columnid,
                gradetype: reason.value,
                other: other.value,
                usescale: usescale,
                grademax: grademax,
                scalemenu: scalemenu,
                adminmenu: adminmenu,
                notes: notes.value,
            });
        })
        .catch((error) => {
            window.console.error(error);
            showaddmultiplemodal.value = false;
            debug.value = error;
        });
    }

    /**
     * Process form submission
     */
    function submit_form() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Where reason looks like OTHER_nn,
        // It's an exiting other, the corresponding
        // label is 'other' and reason is 'OTHER'
        if (reason.value.startsWith('OTHER_')) {
            const gtype = gradetypes.value.find(o => o.value == reason.value);
            if (gtype) {
                reason.value = 'OTHER';
                other.value = gtype.label;
            }
        }

        fetchMany([{
            methodname: 'local_gugrades_write_column',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                reason: reason.value,
                other: other.value,
                notes: notes.value,
            }
        }])[0]
        .then((result) => {
            const columnid = result.columnid;
            get_capture_cell_form(columnid);
        })
        .catch((error) => {
            window.console.error(error);
            showaddmultiplemodal.value = false;
            debug.value = error;
        });

        // close the modal
        showaddmultiplemodal.value = false;
    }
</script>
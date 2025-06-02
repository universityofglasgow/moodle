<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" :disabled="!props.show" @click="showcsvmodal = true">{{ mstrings.csvimport }}</button>

    <VueModal v-model="showcsvmodal" :enableClose="false" modalClass="col-11 col-lg-6 rounded" :title="mstrings.csvimport">

        <PleaseWait v-if="waiting" progresstype="csvimport" :staffuserid="props.staffuserid"></PleaseWait>

        <!-- Doesn't appear to be a CSV -->
        <div v-if="incorrectfiletype" class="alert alert-danger">
            {{ mstrings.incorrectfiletype }}
        </div>

        <div v-if="!incorrectfiletype">

            <!-- Initial download/upload page -->
            <div v-if="pagestate == 'showuploadpage'">
                <div class="border rounded p-5">
                    <p><b>{{  mstrings.csvdownloadhelp }}</b></p>

                    <button class="btn btn-primary" type="button" @click="csv_download()">{{  mstrings.csvdownload }}</button>
                </div>

                <!-- select file / upload bit -->
                <div class="mt-3 p-4 mb-3 border rounded">
                    <p><b>{{ mstrings.csvuploadhelp }}</b></p>
                    <div>
                        <button class="btn btn-primary mr-1" type="button" @click="open()">
                            Choose files
                        </button>
                        <button class="btn btn-warning" type="button" :disabled="!files" @click="reset()">
                            Reset
                        </button>
                        <div class="mt-2" v-if="files">
                            <p>You have selected: <b>{{ `${files.length} ${files.length === 1 ? 'file' : 'files'}` }}</b></p>
                            <li v-for="file of files" :key="file.name">
                                {{ file.name }}
                            </li>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-info mr-1" @click="process_selected">{{ mstrings.upload }}</button>
                    </div>
                </div>
            </div>

            <!-- new drag/drop stuff -->
            <div class="mt-3 p-4 mb-3 border rounded">

                <FileDragDrop></FileDragDrop>
            </div>

            <!-- Test-run / confirm page -->
            <div v-if="pagestate == 'showtestrun'">
                <p>{{ mstrings.csvtestrun }}</p>
                <EasyDataTable :headers="headers" :items="lines10">
                    <template #item-gradevalue="item">
                        <span v-if="item.grade">{{ item.gradevalue }}</span>
                    </template>
                    <template #item-error="item">
                        <i v-if="item.state < 0" class="text-danger fa fa-times" aria-hidden="true"></i>
                        <i v-if="item.state > 0" class="text-success fa fa-check" aria-hidden="true"></i>
                        <i v-if="item.state == 0" class="text-warning fa fa-info" aria-hidden="true"></i>
                        {{ item.error }}
                    </template>
                </EasyDataTable>
                <p v-if="errorcount" class="text-danger mt-1">{{ mstrings.lineswitherrors }}: {{ errorcount }}:</p>
                <ul class="text-danger">
                    <li v-for="error in errorlist" v-key="error.error">
                        <span>{{ error.error }}</span>: <b>{{ error.count }} line(s)</b>
                    </li>
                </ul>

                <!-- submit bit (if no errors) -->
                <div v-if="!errorcount" class="mt-2">
                    <FormKit class="border rounded" type="form" @submit="submit_reason_form">
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
                    </FormKit>
                </div>
            </div>

        </div> <!-- incorrectfiletype -->

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
    import {ref, defineProps, defineEmits, inject, onMounted, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import { saveAs } from 'file-saver';
    import PleaseWait from '@/components/PleaseWait.vue';
    import FileDragDrop from '@/components/FileDragDrop.vue';
    import { useFileDialog } from '@vueuse/core';

    const showcsvmodal = ref(false);
    const pagestate = ref('showuploadpage');
    const csvcontent = ref('');
    const errorcount = ref(0);
    const errorlist = ref([]);
    const addcount = ref(0);
    const lines = ref([]);
    const headers = ref([]);
    const gradetypes = ref([]);
    const reason = ref('');
    const other = ref('');
    const debug = ref({});
    const incorrectfiletype = ref(false);
    const waiting = ref(false);
    const lines10 = computed(() =>{
        return lines.value.slice(0, 10);
    });
    const mstrings = inject('mstrings');

    const toast = useToast();

    const { files, open, reset } = useFileDialog({
        accept: 'text/csv', // Set to accept only csv files
        multiple: false,
        directory: false, // Select directories instead of files if set true
    });

    const props = defineProps({
        itemid: Number,
        groupid: Number,
        itemname: String,
        show: Boolean,
        staffuserid: Number,
    });

    const emits = defineEmits(['uploaded']);

    /**
     * Download the pro-forma csv file
     */
    function csv_download() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        waiting.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_csv_download',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
            }
        }])[0]
        .then((result) => {
            const csv = result['csv'];
            const d = new Date();
            const filename = props.itemname + '_' + d.toLocaleString() + '.csv';
            const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
            saveAs(blob, filename);
            waiting.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            showcsvmodal.value = false;
            debug.value = error;
        });
    }

    /**
     * Process the uploaded CSV data
     * @param testrun true = don't save the data
     */
    function process_uploaded(testrun) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        waiting.value = true;

        fetchMany([{
            methodname: 'local_gugrades_upload_csv',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
                testrun: testrun,
                reason: reason.value,
                other: other.value,
                csv: csvcontent.value,
            }
        }])[0]
        .then((result) => {
            lines.value = result.lines;
            errorcount.value = result.errorcount;
            addcount.value = result.addcount;
            errorlist.value = result.errorlist;
            pagestate.value = 'showtestrun';
            waiting.value = false;
            if (!testrun) {
                toast.success(mstrings.csvgradesadded + ' (' + addcount.value + ')');
                emits('uploaded');
                close_modal();
            }
        })
        .catch((error) => {
            window.console.error(error);
            showcsvmodal.value = false;
            debug.value = error;
        });
    }

    /**
     * Get the add grade form stuff
     */
    function get_gradetypes() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

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
            showcsvmodal.value = false;
            debug.value = error;
        });
    }

    /**
     * Handle the submitted upload form
     * Got working more by luck....
     */
    function submit_csv_form(data) {
        const reader = new FileReader();
        reader.addEventListener('load', (event) => {
            csvcontent.value = event.target.result;

            // Get all the stuffs for the next page
            process_uploaded(true);
            get_gradetypes();
        });
        reader.readAsText(data.csvupload[0].file);
    }

    /**
     * Button clicked to upload CSV
     * Process selected file.
     */
     function process_selected() {
        if (!files.value) {
            toast.warning('No file to import');
            return;
        }

        const file = files.value[0];
        const type = file.type;
        incorrectfiletype.value = type != 'text/csv';

        if (!incorrectfiletype.value) {
            const reader = new FileReader();
            reader.addEventListener('load', (event) => {
                csvcontent.value = event.target.result;

                process_uploaded(true);
                get_gradetypes();
            });
            reader.readAsText(file);
        }
    }

    /**
     * Submit the final form with reason
     */
    function submit_reason_form() {
        process_uploaded(false);
    }

    onMounted(() => {
        incorrectfiletype.value = false;
        headers.value = [
            {text: mstrings.name, value: 'name'},
            {text: mstrings.idnumber, value: 'idnumber'},
            {text: mstrings.grade, value: 'grade'},
            {text: mstrings.gradevalue, value: 'gradevalue'},
            {text: mstrings.status, value: 'error'},
        ];
    });

    /**
     * Close the modal
     */
    function close_modal() {
        incorrectfiletype.value = false;
        reset();
        showcsvmodal.value = false;
        pagestate.value = 'showuploadpage';
    }
</script>

<template>
    <button type="button" class="btn btn-outline-info  mr-1" @click="showcsvmodal = true">{{ mstrings.csvimport }}</button>

    <VueModal v-model="showcsvmodal" modalClass="col-11 col-lg-6 rounded" :title="mstrings.csvimport">

        <!-- Initial download/upload page -->
        <div v-if="pagestate == 'showuploadpage'">
            <div class="border rounded p-5">
                <p><b>{{  mstrings.csvdownloadhelp }}</b></p>

                <button class="btn btn-primary" type="button" @click="csv_download()">{{  mstrings.csvdownload }}</button>
            </div>

            <form>
                <FormKit class="border rounded" type="form" @submit="submit_csv_form">
                    <FormKit
                        type="file"
                        name="csvupload"
                        label="CSV Upload"
                        accept=".csv"
                        :help="mstrings.csvuploadhelp"
                        multiple="false"
                        inner-class="form-group"
                        input-class="form-control-file"
                        fileList-class="d-none"
                        nFiles-class="d-none"
                        />
                </FormKit>
            </form>
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
            <p v-if="errorcount" class="text-danger mt-1">{{ mstrings.lineswitherrors }}: {{ errorcount }}</p>

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
    import { saveAs } from 'file-saver';

    const showcsvmodal = ref(false);
    const pagestate = ref('showuploadpage');
    const csvcontent = ref('');
    const errorcount = ref(0);
    const addcount = ref(0);
    const lines = ref([]);
    const headers = ref([]);
    const gradetypes = ref([]);
    const reason = ref('');
    const other = ref('');
    const lines10 = computed(() =>{
        return lines.value.slice(0, 10);
    });
    const mstrings = inject('mstrings');

    const toast = useToast();

    const props = defineProps({
        itemid: Number,
        groupid: Number,
        itemname: String,
    });

    const emits = defineEmits(['uploaded']);

    /**
     * Download the pro-forma csv file
     */
    function csv_download() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

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
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
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
            pagestate.value = 'showtestrun';
            if (!testrun) {
                toast.success(mstrings.csvgradesadded + ' (' + addcount.value + ')');
                emits('uploaded');
                close_modal();
            }
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
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
            toast.error('Error communicating with server (see console)');
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
     * Submit the final form with reason
     */
    function submit_reason_form() {
        process_uploaded(false);
    }

    onMounted(() => {
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
        showcsvmodal.value = false;
        pagestate.value = 'showuploadpage';
    }
</script>

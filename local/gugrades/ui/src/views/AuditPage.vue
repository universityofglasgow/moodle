<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div>
        <EasyDataTable :headers="headers" :items="items"></EasyDataTable>
    </div>
    <div>
        <button class="mt-2 btn btn-success" @click="download_clicked">{{ mstrings.downloadtocsv }}</button>
    </div>
</template>

<script setup>
    import {ref, onMounted, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import { saveAs } from 'file-saver';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const mstrings = inject('mstrings');
    const items = ref([]);
    const headers = ref([]);
    const debug = ref({});

    const toast = useToast();

    /**
     * Download button clicked
     */
    function download_clicked() {
        let csv =
            mstrings.time + ', ' +
            mstrings.gradeitem + ', ' +
            mstrings.by + ', ' +
            mstrings.relateduser + ', ' +
            mstrings.message + '\n';
        items.value.forEach((item) => {
            csv +=
                '"' + item.time + '", ' +
                '"' + item.gradeitem + '", ' +
                '"' + item.username + '", ' +
                '"' + item.relatedusername + '", ' +
                '"' + item.message.replaceAll('"', '') + '"\n';
        });
        const d = new Date();
        const filename = 'Audit_' + d.toLocaleString() + '.csv';
        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
        saveAs(blob, filename);
    }

    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        headers.value = [
               {text: mstrings.time, value: 'time'},
               {text: mstrings.gradeitem, value: 'gradeitem'},
               {text: mstrings.by, value: 'username'},
               {text: mstrings.relateduser, value: 'relatedusername'},
               {text: mstrings.message, value: 'message'},
            ];

        fetchMany([{
            methodname: 'local_gugrades_get_audit',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            items.value = result;

        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        })
    });
</script>
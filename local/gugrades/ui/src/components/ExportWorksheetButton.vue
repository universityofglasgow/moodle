<template>
    <button v-if="hascapability && (itemtype == 'assign')" type="button" class="btn btn-outline-info  mr-1" @click="export_worksheet()">
        <MString name="exportworksheet"></MString>
    </button>
</template>

<script setup>
    import {ref, defineProps, onMounted} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import { useToast } from "vue-toastification";
    import { saveAs } from 'file-saver';
    import { getstrings } from '@/js/getstrings.js';

    const hascapability = ref(false);
    const strings = ref({});

    const toast = useToast();

    const props = defineProps({
        users: Array,
        itemtype: String,
        itemname: String,
    });

    /**
     * Export data to file
     */
    function export_worksheet() {
        let csv = '';
        let line = [];

        csv += strings.value.recordid + ',' + strings.value.gradenoun + ',' + strings.value.lastmodifiedgrade + ',' + strings.value.idnumber + '\n';
        props.users.forEach((user) => {
            line = [
                strings.value.hiddenuser + ' ' + user.uniqueid,
                user.idnumber,
                '',
                '',
                user.idnumber,
            ];
            csv += line.toString() + '\n';
        });

        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
        saveAs(blob, props.itemname + '.csv');
    }

    /**
     * Check capability
     */
    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany; 

        fetchMany([{
            methodname: 'local_gugrades_has_capability',
            args: {
                courseid: courseid,
                capability: 'local/gugrades:exportworksheet'
            }
        }])[0]
        .then((result) => {
            hascapability.value = result['hascapability'];
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });

        // Strings
        // Get the moodle strings for this page
        const stringslist = [
            'gradenoun',
            'recordid',
            'lastmodifiedgrade',
            'hiddenuser',
            'idnumber'
        ];
        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });
    });

</script>
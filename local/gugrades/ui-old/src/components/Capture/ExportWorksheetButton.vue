<template>
    <button v-if="hascapability && (itemtype == 'assign')" type="button" class="btn btn-outline-info  mr-1" @click="export_worksheet()">
        {{ mstrings.exportworksheet }}
    </button>
</template>

<script setup>
    import {ref, defineProps, onMounted, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import { saveAs } from 'file-saver';

    const hascapability = ref(false);
    const mstrings = inject('mstrings');

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

        csv += mstrings.recordid + ',' + mstrings.gradenoun + ',' + mstrings.lastmodifiedgrade + ',' + mstrings.idnumber + '\n';
        props.users.forEach((user) => {
            line = [
                mstrings.hiddenuser + ' ' + user.uniqueid,
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
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });

    });

</script>
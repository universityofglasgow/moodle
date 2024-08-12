<template>
    <div>
        <EasyDataTable :headers="headers" :items="items"></EasyDataTable>
    </div>
</template>

<script setup>
    import {ref, onMounted, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const mstrings = inject('mstrings');
    const items = ref([]);
    const headers = ref([]);

    const toast = useToast();

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
            toast.error('Error communicating with server (see console)');
        })
    });
</script>
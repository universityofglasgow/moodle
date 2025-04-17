<template>
    <a class="dropdown-item" href="#" @click="import_grade()">{{ mstrings.importusergrade }}</a>
</template>

<script setup>
    import {inject, defineEmits, defineProps} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const mstrings = inject('mstrings');
    const toast = useToast();

    const props = defineProps({
        itemid: Number,
        userid: Number,
    });

    const emit = defineEmits(['imported']);

    /**
     * Import grade for single user
     */
     function import_grade() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_import_grade',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
            }
        }])[0]
        .then((result) => {
            const success = result['success'];
            if (success) {
                toast.success(mstrings.gradeimporteduser);
            } else {
                toast.warning(mstrings.nothingtoimport);
            }
            emit('imported');
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }
</script>
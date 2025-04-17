<template>
    <a href="#" class="ml-1"><i class="fa fa-cogs fa-lg" aria-hidden="true" @click="cog_clicked"></i></a>
</template>

<script setup>
    import {defineProps, defineEmits, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const props = defineProps({
        itemid: Number,
        header: Object,
        active: Boolean,
    });

    const mstrings = inject('mstrings');
    const toast = useToast();

    const emits = defineEmits(['editcolumn']);

    /**
     * Cog wheel has been clicked
     */
    function cog_clicked() {
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
                columnname: props.header.value,
                gradetype: props.header.gradetype,
                other: props.header.other,
                columnid: props.header.columnid,
                usescale: usescale,
                grademax: grademax,
                scalemenu: scalemenu,
                adminmenu: adminmenu,
                notes: '',
            });
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }
</script>
<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="recalculate_clicked()">
        {{ mstrings.recalculate }}
    </button>

    <VueModal v-model="showrecalculatemodal" enableClose="false" modalClass="col-11 col-lg-6 rounded" :title="mstrings.recalculate">
        <div v-if="loading">
            <PleaseWait :staffuserid="props.staffuserid" progresstype="aggregate"></PleaseWait>
        </div>

        <div v-else>
            <div class="alert alert-info">{{ mstrings.recalculatehelp }}</div>

            <button class="btn btn-primary mr-1" type="button" @click="do_recalculate()">{{  mstrings.recalculate }}</button>
            <button class="btn btn-warning" type="button" @click="showrecalculatemodal = false">{{  mstrings.cancel }}</button>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, inject, defineProps, defineEmits} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import PleaseWait from '@/components/PleaseWait.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const mstrings = inject('mstrings');
    const showrecalculatemodal = ref(false);
    const loading = ref(false);
    const debug = ref({});

    const props = defineProps({
        categoryid: Number,
        staffuserid: Number,
    });

    const emits = defineEmits([
        'recalculated'
    ]);

    const toast = useToast();

    /**
     * Recalculate button has been clicked
     */
    function recalculate_clicked() {
        showrecalculatemodal.value = true;
    }

    /**
     * Perform recalculation
     */
    function do_recalculate() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        loading.value = true;

        fetchMany([{
            methodname: 'local_gugrades_recalculate',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
            }
        }])[0]
        .then((result) => {
            toast.success('Grades recalculated');
            loading.value = false;
            showrecalculatemodal.value = false;
            emits('recalculated');
        })
        .catch((error) => {
            window.console.error(error);
            showrecalculatemodal.value = false;
            debug.value = error;
        });
    }
</script>
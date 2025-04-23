<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button v-if="hascapability" type="button" class="btn btn-outline-primary  mr-1" @click="toggle_view()">
        <span v-if="!togglereveal">{{ mstrings.viewfullnames }}</span>
        <span v-if="togglereveal">{{ mstrings.hidefullnames }}</span>
    </button>
</template>

<script setup>
    import {ref, onMounted, inject, defineEmits} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const hascapability = ref(false);
    const togglereveal = ref(false);
    const mstrings = inject('mstrings');
    const debug = ref({});

    const emit = defineEmits(['viewfullnames']);

    const toast = useToast();

    /**
     * Export data to file
     */
    function toggle_view() {
        togglereveal.value = !togglereveal.value;
        emit('viewfullnames', togglereveal.value);
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
                capability: 'local/gugrades:viewhiddennames'
            }
        }])[0]
        .then((result) => {
            hascapability.value = result['hascapability'];
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });

    });

</script>
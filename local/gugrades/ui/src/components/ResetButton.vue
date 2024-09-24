<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button v-if="hascapability" type="button" class="btn btn-danger  mr-1" @click="showconfirm = true">
        {{ mstrings.resetcourse }}
    </button>

    <ConfirmModal :show="showconfirm" :message="mstrings.resetcourseconfirm" @confirm="confirmdelete"></ConfirmModal>
</template>

<script setup>
    import {ref, onMounted, inject} from '@vue/runtime-core';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import { useToast } from "vue-toastification";

    const hascapability = ref(false);
    const showconfirm = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');

    const toast = useToast();

    /**
     * Process confirmation
     */
    function confirmdelete(confirmyes) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        if (confirmyes) {
            fetchMany([{
                methodname: 'local_gugrades_reset',
                args: {
                    courseid: courseid,
                }
            }])[0]
            .then(() => {
                toast.success(mstrings.resetsuccess)
            })
            .catch((error) => {
                window.console.error(error);
                debug.value = error;
            });
        }

        showconfirm.value = false;
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
                capability: 'local/gugrades:resetcourse'
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
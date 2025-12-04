<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary mr-1" :disabled="props.disabled" @click="showreleasemodal=true">
        <span>
            <span v-if="grouprelease"><span v-if="props.released">(Un-)</span>{{ mstrings.releaseaggregatedgroup }}</span>
            <span v-else><span v-if="props.released">(Un-)</span>{{ mstrings.releaseaggregatedgrade }}</span>
        </span>
    </button>

    <VueModal v-model="showreleasemodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="mstrings.releaseaggregatedgrade">

        <div v-if="loading">
            <PleaseWait></PleaseWait>
        </div>

        <div class="p-2 border rounded">
            <h4>{{ mstrings.releaseaggregatedgrade }}</h4>
            <div v-if="!props.released" class="alert alert-warning">
                {{ mstrings.releaseaggregatedconfirm }}
                <p v-if="grouprelease" class="mt-1"><b>{{ mstrings.releaseconfirmgroup }}</b></p>
            </div>
            <div v-if="props.released" class="alert alert-danger">
                {{ mstrings.releaseaggregatedconfirmstern }}
                <p v-if="grouprelease" class="mt-1"><b>{{ mstrings.releaseconfirmgroup }}</b></p>
            </div>
            <button
                class="btn btn-primary mr-1"
                @click="release_grades()"
                >{{ mstrings.yesrelease }}
            </button>
            <button
                class="btn btn-warning"
                @click="showreleasemodal = false"
                >{{ mstrings.cancel }}
            </button>
        </div>

        <!-- display if already released -->
        <div v-if="props.released" class="border rounded mt-4 p-2">
            <h4>Revert release of grades</h4>
            <div class="alert alert-danger">
                {{ mstrings.removerelease }}
                <p v-if="grouprelease" class="mt-1"><b>{{ mstrings.removereleasegroup }}</b></p>
            </div>
            <button
                class="btn btn-danger mr-1"
                @click="revert_release()"
                >{{ mstrings.yesunrelease }}
            </button>
            <button
                class="btn btn-warning"
                @click="showreleasemodal = false"
                >{{ mstrings.cancel }}
            </button>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, inject, defineProps, defineEmits, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import PleaseWait from '@/components/PleaseWait.vue';
    import { useLogo } from '@/js/monochromelogo.js';

    const showreleasemodal = ref(false);
    const loading = ref(false);
    const mstrings = inject('mstrings');
    const debug = ref({});

    const emit = defineEmits(['released']);

    const toast = useToast();

    const {monochrome, updateLogo} = useLogo();

    const props = defineProps({
        gradeitemid: Number,
        groupid: Number,
        released: Boolean,
        disabled: Boolean,
    });

    const grouprelease = computed(() => {
        return props.groupid > 0;
    });

    /**
     * Release grades on button click
     */
    function release_grades() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        loading.value = true;

        fetchMany([{
            methodname: 'local_gugrades_release_grades',
            args: {
                courseid: courseid,
                gradeitemid: props.gradeitemid,
                groupid: props.groupid,
                revert: false,
            }
        }])[0]
        .then(() => {
            emit('released');
            showreleasemodal.value = false;
            updateLogo();
            loading.value = false;
            toast.success(mstrings.gradesreleased);
        })
        .catch((error) => {
            window.console.error(error);
            showreleasemodal.value = false;
            debug.value = error;
        });

        showreleasemodal.value = true;
    }

    /**
     * Revert release grades on button click
     */
     function revert_release() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        loading.value = true;

        fetchMany([{
            methodname: 'local_gugrades_release_grades',
            args: {
                courseid: courseid,
                gradeitemid: props.gradeitemid,
                groupid: props.groupid,
                revert: true,
            }
        }])[0]
        .then(() => {
            emit('released');
            showreleasemodal.value = false;
            updateLogo();
            loading.value = false;
            toast.success(mstrings.gradesunreleased);
        })
        .catch((error) => {
            showreleasemodal.value = false;
            debug.value = error;
        });

        showreleasemodal.value = true;
    }
</script>
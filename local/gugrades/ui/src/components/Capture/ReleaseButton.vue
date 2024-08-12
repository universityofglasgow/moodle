<template>
    <button type="button" class="btn btn-outline-primary mr-1" @click="showreleasemodal=true">
        <span v-if="props.released">
            <span v-if="grouprelease">{{ mstrings.unreleasegradesgroup }}</span>
            <span v-else>{{ mstrings.unreleasegrades }}</span>
        </span>
        <span v-if="!props.released">
            <span v-if="grouprelease">{{ mstrings.releasegradesgroup }}</span>
            <span v-else>{{ mstrings.releasegrades }}</span>
        </span>
    </button>

    <VueModal v-model="showreleasemodal" modalClass="col-11 col-lg-5 rounded" :title="mstrings.releasegrades">

        <div class="p-2 border rounded">
            <h4>{{ mstrings.releasegrades }}</h4>
            <div v-if="!props.released" class="alert alert-warning">
                {{ mstrings.releaseconfirm }}
                <p v-if="grouprelease" class="mt-1"><b>{{ mstrings.releaseconfirmgroup }}</b></p>
            </div>
            <div v-if="props.released" class="alert alert-danger">
                {{ mstrings.releaseconfirmstern }}
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

    const showreleasemodal = ref(false);
    const mstrings = inject('mstrings');

    const emit = defineEmits(['released']);

    const toast = useToast();

    const props = defineProps({
        gradeitemid: Number,
        groupid: Number,
        released: Boolean,
    });

    const grouprelease = computed(() => {
        return props.groupid > 0;
    });

    /**
     * Get current state of dashboard enabled/disabled
     */
     function get_dashboard_enabled() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_dashboard_enabled',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            const enabled = result.enabled;

            // Bodge to get jQuery needed for Bootstrap JS.
            const $ = window.jQuery;

            if (enabled) {
                $('#mygradeslogo').css('filter', 'grayscale(0)');
            } else {
                $('#mygradeslogo').css('filter', 'grayscale(1)');
            }
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }

    /**
     * Release grades on button click
     */
    function release_grades() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

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
            get_dashboard_enabled();
            toast.success(mstrings.gradesreleased);
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
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
            get_dashboard_enabled();
            toast.success(mstrings.gradesunreleased);
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });

        showreleasemodal.value = true;
    }
</script>
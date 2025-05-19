<template>
    <VueModal v-model="showmodal" :enableClose="false" modalClass="col-3 col-lg-2 rounded vm_container" :title="mstrings.pleasewait">
        <div class="d-flex justify-content-center" >
            <div class="border rounded m-1 p-2 text-center" style="min-width: 300px">
                <p>{{ props.message }}</p>
                <VueSpinnerOrbit v-if="!showprogress" size="50" color="#005c8a"></VueSpinnerOrbit>
                <div v-if="showprogress" class="progress" style="min-width: 250px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" :class="progressclass" role="progressbar" :style="'width: ' + progress + '%'" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                        {{ progress }}%
                    </div>
                </div>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, inject, onMounted, onUnmounted, defineProps, computed} from '@vue/runtime-core';
    import { VueSpinnerOrbit } from 'vue3-spinners';
    import { useIntervalFn } from '@vueuse/core';

    const mstrings = inject('mstrings');
    const showmodal = ref(false);
    const progress = ref(0);

    // Props are only defined for progress bar.
    // If you don't want a progress bar then props are not required
    const props = defineProps({
        uniqueid: {
            type: Number,
            default: 0
        },
        progresstype: {
            type: String,
            default: '',
        },
        staffuserid: {
            type: Number,
            default: 0,
        },
        message: {
            type: String,
            default: '',
        },
    });

    const showprogress = computed(() => {
        return (props.progresstype != '') && (progress.value >= 0);
    });

    const progressclass = computed(() => {
        if (progress.value < 33.3) {
            return 'bg-danger';
        }
        if (progress.value < 66.6) {
            return 'bg-info';
        }
        return 'bg-success';
    })

    const { pause, resume, isActive } = useIntervalFn(() => {
        if (props.progresstype != '') {
            const GU = window.GU;
            const courseid = GU.courseid;
            const fetchMany = GU.fetchMany;

            // Note the two additional parameters. They are
            // async = true
            // loginrequired = false
            //
            // Without loginrequired we'd hit moodle sessions which would stop this returning.
            // We also have to pass around the staff userid as that would not be available
            // outside a session.
            fetchMany([{
                methodname: 'local_gugrades_get_progress',
                args: {
                    courseid: courseid,
                    uniqueid: props.uniqueid,
                    progresstype: props.progresstype,
                    staffuserid: props.staffuserid,
                }
            }], true, false)[0]
            .then((result) => {
                progress.value = result.progress;
            })
            .catch((error) => {
                window.console.error(error);
            })
        }
    }, 1000)

    onMounted(() => {
        showmodal.value = true;
    });


    onUnmounted(() => {
        showmodal.value = false;
    });
</script>

<style>
    .vm {
        min-width: 300px !important;
    }
</style>
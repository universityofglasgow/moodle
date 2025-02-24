<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="import_button_click()">
        <span v-if="groupimport">{{ mstrings.importgradesgroup }}</span>
        <span v-else>{{ mstrings.importgrades }}</span>
    </button>

    <VueModal v-model="showimportmodal" enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="mstrings.importgrades">
        <div v-if="loading">
            <PleaseWait progresstype="import" :staffuserid="props.staffuserid"></PleaseWait>
        </div>

        <div v-else>
            <div v-if="is_importgrades" class="alert alert-warning">
                {{ mstrings.gradesimported }}
                <p v-if="groupimport" class="mt-1"><b>{{ mstrings.importinfogroup }}</b></p>
            </div>
            <div v-else class="alert alert-info">
                {{ mstrings.importinfo }}
                <p v-if="groupimport" class="mt-1"><b>{{ mstrings.importinfogroup }}</b></p>
            </div>

            <div v-if="is_importgrades" class="alert alert-info">
                <FormKit
                    type="checkbox"
                    :label="mstrings.importadditional"
                    :help="mstrings.importadditionalhelp"
                    name="importadditional"
                    v-model="importadditional"
                    >
                    <template #help>
                        <p><i class="fa fa-info-circle" aria-hidden="true"></i> {{ mstrings.importadditionalhelp }}</p>
                    </template>
                </FormKit>
            </div>

            <div v-if="recursiveavailable" class="alert alert-secondary">
                <div v-if="!allgradesvalid" class="alert alert-danger">
                    {{ mstrings.invalidgradetype }}
                </div>
                <div v-else>
                    <FormKit
                        type="checkbox"
                        :label="mstrings.recursiveimport"
                        :help="mstrings.recursiveimporthelp"
                        name="recursiveimport"
                        v-model="recursiveselect"
                        >
                        <template #help>
                            <p><i class="fa fa-info-circle" aria-hidden="true"></i> {{ mstrings.recursiveimporthelp }}</p>
                        </template>
                    </FormKit>
                </div>
            </div>

            <div class="alert alert-success">
                <FormKit
                    type="select"
                    :label="mstrings.importfillns + ':'"
                    :help="mstrings.importfillnshelp"
                    :options="options"
                    name="importfillns"
                    v-model="importfillns"
                    >
                    <template #help>
                        <p><i class="fa fa-info-circle mt-2" aria-hidden="true"></i> {{ mstrings.importfillnshelp }}</p>
                    </template>
                </FormKit>
            </div>

            <div v-if="recursiveavailable && recursiveselect && !recursivematch" class="alert alert-warning">
                {{ mstrings.importnomatch }}
            </div>

            <div class="mt-2 pt-2 border-top">
                <button
                        class="btn btn-primary mr-1"
                        @click="importgrades()"
                        >{{ mstrings.yesimport }}
                </button>
                <button
                    class="btn btn-warning"
                    @click="showimportmodal = false"
                    >{{ mstrings.cancel }}
                </button>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import PleaseWait from '@/components/PleaseWait.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const props = defineProps({
        userids: Array,
        itemid: Number,
        groupid: Number,
        show: Boolean,
        staffuserid: Number,
    });

    const toast = useToast();
    const groupimport = computed(() => {
        return props.groupid > 0;
    });

    const emit = defineEmits(['imported']);

    const showimportmodal = ref(false);
    const is_importgrades = ref(false);
    const recursiveavailable = ref(false);
    const recursivematch = ref(false);
    const recursiveselect = ref(false);
    const importadditional = ref(false);
    const importfillns = ref('');
    const allgradesvalid = ref(false);
    const level = ref(0);
    const loading = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');

    /**
     * Options for NS/NS0 dropdown
     */
    const options = computed(() => {
        const options = {
            none: mstrings.donotfill,
            fillns: mstrings.fillns,
        };

        // NS0 only available level >=2
        if (level.value > 1) {
            options.fillns0 = mstrings.fillns0;
        }

        return options;
    })

    /**
     * Import confirmed. Select appropriate importfunction
     */
    function importgrades() {

        loading.value = true;

        if (recursiveselect.value) {
            importrecursive();
        } else {
            importsingle();
        }
    }

    /**
     * Import single grade item
     */
     function importsingle() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_import_grades_users',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                additional: importadditional.value,
                fillns: importfillns.value,
                userlist: props.userids,
            }
        }])[0]
        .then((result) => {
            const importcount = result['importcount'];
            emit('imported');
            if (importcount) {
                toast.success(mstrings.gradesimportedsuccess);
            } else {
                toast.warning(mstrings.nogradestoimport);
            }

            showimportmodal.value = false;
        })
        .catch((error) => {
            showimportmodal.value = false;
            debug.value = error;
            window.console.error(error);
        });
    }

    /**
     * Import recursive grades
     */
    function importrecursive() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_import_grades_recursive',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
                additional: importadditional.value,
                fillns: importfillns.value,
            }
        }])[0]
        .then((result) => {
            const itemcount = result.itemcount;
            const gradecount = result.gradecount;
            emit('imported');
            if (gradecount) {
                toast.success(mstrings.gradesimportedsuccess);
            } else {
                toast.warning(mstrings.nogradestoimport);
            }

            showimportmodal.value = false;
        })
        .catch((error) => {
            showimportmodal.value = false;
            debug.value = error;
            window.console.error(error);
        });
    }

    /**
     * When button clicked
     * Check for existing grades
     */
    function import_button_click() {
        showimportmodal.value = true;
        importadditional.value = false;
        recursiveselect.value = false;
        importfillns.value = false;
        loading.value = false;

        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_is_grades_imported',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                groupid: props.groupid,
            }
        }])[0]
        .then((result) => {
            is_importgrades.value = result.imported;
            recursiveavailable.value = result.recursiveavailable;
            recursivematch.value = result.recursivematch;
            allgradesvalid.value = result.allgradesvalid;
            level.value = result.level;
        })
        .catch((error) => {
            window.console.error(error);
            showimportmodal.value = false;
            debug.value = error;
        });
    }
</script>
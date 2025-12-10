<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <button type="button" class="btn btn-outline-primary  mr-1" @click="import_button_click()">
        <span v-if="groupimport">{{ mstrings.importgradesgroup }}</span>
        <span v-else>{{ mstrings.importgrades }}</span>
    </button>

    <VueModal v-model="showimportmodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="mstrings.importgrades">

        <div v-if="loading">
            <PleaseWait progresstype="import" :staffuserid="props.staffuserid"></PleaseWait>
        </div>

        <div v-if="showdryrun" class="text-center">

            <p v-if="dryruncount > 0" v-html="mstrings.importdryrun"></p>
            <p v-else v-html="mstrings.importdryrunzero"></p>
            <p v-if="dryruncount > 0" class="display-4">{{ dryruncount }}</p>

            <div class="mt-2 pt-2 border-top">
                <button
                        v-if="dryruncount > 0"
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

        <div v-if="!loading && !showdryrun">

            <!-- already imported warning-->
            <div class="alert" :class="importclass">
                <div class="row">
                    <div v-if="is_importgrades" class="col-md-10 col">
                        {{ mstrings.gradesimported }}
                        <p v-if="groupimport" class="mt-1"><b>{{ mstrings.importinfogroup }}</b></p>
                    </div>
                    <div v-else class="col-md-10 col">
                        {{ mstrings.importinfo }}
                        <p v-if="groupimport" class="mt-1"><b>{{ mstrings.importinfogroup }}</b></p>
                    </div>
                    <div class="col-md-2 col">
                        <button
                            class="btn btn-warning"
                            @click="showimportmodal = false"
                            >{{ mstrings.cancel }}
                        </button>
                    </div>
                </div>
            </div>

            <FormKit type="form" class="bg-light" :actions="false">

                <!-- Recursive options -->
                <div v-if="recursiveavailable">
                    <div v-if="!allgradesvalid" class="alert alert-danger">
                        {{ mstrings.invalidgradetype }}
                    </div>
                    <div v-else>
                        <FormKit
                            type="radio"
                            :label="mstrings.recursiveimport"
                            :options="{
                                single: mstrings.recursive_single,
                                recursive: mstrings.recursive_recursive
                            }",
                            name="recursiveimport"
                            v-model="recursiveselect"
                            >
                        </FormKit>
                    </div>
                    <hr></hr>
                </div>

                <!-- NS fill options -->
                <FormKit
                    type="radio"
                    :label="mstrings.importfillns"
                    :options="nsoptions"
                    name="importfillns"
                    v-model="importfillns"
                    >
                </FormKit>
                <hr></hr>

                <!-- If there are existing grades then show all the options for importing extra grades -->
                <div v-if="is_importgrades">
                    <FormKit
                        type="radio"
                        :label="mstrings.importadditional"
                        name="importadditional"
                        :options="{
                            admin: mstrings.importadditional_admin,
                            missing: mstrings.importadditional_missing,
                            update: mstrings.importadditional_update
                        }"
                        v-model="importadditional"
                        >
                    </FormKit>
                    <hr></hr>
                    <FormKit
                        type="select"
                        :label="mstrings.reasonforadditionalimport"
                        name="reason"
                        v-model="reason"
                        :options="gradetypes"
                        :placeholder="mstrings.selectareason"
                        validation="required"
                    />
                    <FormKit
                        v-if = 'reason == "OTHER"'
                        :label="mstrings.pleasespecify"
                        type="text"
                        :placeholder="mstrings.pleasespecify"
                        name="other"
                        v-model="other"
                    />
                </div>
            </FormKit>

            <div v-if="recursiveavailable && (recursiveselect=='recursive') && !recursivematch" class="alert alert-warning">
                {{ mstrings.importnomatch }}
            </div>

            <div class="mt-2 pt-2 border-top">
                <button
                        class="btn btn-primary mr-1"
                        @click="dryrungrades()"
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
    const recursiveselect = ref('single');
    const reason = ref('SECOND');
    const importadditional = ref('admin');
    const importfillns = ref('none');
    const allgradesvalid = ref(false);
    const gradetypes = ref([]);
    const other = ref('');
    const level = ref(0);
    const dryruncount = ref(0);
    const showdryrun = ref(false);
    const loading = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');

    /**
     * What kind of alert do you get?
     */
    const importclass = computed(() => ({
        'alert-warning' : is_importgrades.value,
        'alert-info' : !is_importgrades.value,
    }));

    /**
     * Options for NS/NS0 dropdown
     */
    const nsoptions = computed(() => {
        const options = {
            none: mstrings.donotfill,
            fillns: mstrings.fillns,
        };

        // NS0 only available level >=2
        if (level.value > 1) {
            options.fillns0 = mstrings.fillns0;
        }

        return options;
    });

    /**
     * Do dry run. Select appropriate import function
     */
    function dryrungrades() {

        loading.value = true;
        dryruncount.value = 0;

        if (recursiveselect.value == 'recursive') {
            importrecursive();
        } else {
            importsingle();
        }
    }

    /**
     * Do proper import. Select appropriate import function
     */
    function importgrades() {

        loading.value = true;

        if (recursiveselect.value == 'recursive') {
            importrecursive();
        } else {
            importsingle();
        }
    }

    /**
     * Get the add grade form stuff
     */
    function get_gradetypes() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_gradetypes',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
            }
        }])[0]
        .then((result) => {
            gradetypes.value = result.gradetypes;
        })
        .catch((error) => {
            window.console.error(error);
            showimportmodal.value = false;
            debug.value = error;
        });
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
                reason: is_importgrades.value ? reason.value : 'FIRST',
                other: is_importgrades.value ? other.value : '',
                dryrun: dryruncount.value == 0,
            }
        }])[0]
        .then((result) => {
            const importcount = result['importcount'];
            dryruncount.value = importcount;
            loading.value = false;

            // Only close the modal after we've shown the dry run count.
            if (showdryrun.value) {
                emit('imported');
                if (dryruncount) {
                    toast.success(mstrings.gradesimportedsuccess);
                } else {
                    toast.warning(mstrings.nogradestoimport);
                }

                showimportmodal.value = false;
            } else {
                showdryrun.value = true;
            }
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
                reason: is_importgrades.value ? reason.value : 'FIRST',
                other: is_importgrades.value ? other.value : '',
                dryrun: dryruncount.value == 0,
            }
        }])[0]
        .then((result) => {
            const itemcount = result.itemcount;
            const gradecount = result.gradecount;
            dryruncount.value = gradecount;
            loading.value = false;

            // Only close the modal after we've shown the dry run count.
            if (showdryrun.value) {
                emit('imported');
                if (dryruncount) {
                    toast.success(mstrings.gradesimportedsuccess);
                } else {
                    toast.warning(mstrings.nogradestoimport);
                }

                showimportmodal.value = false;
            }
            else {
                showdryrun.value = true;
            }
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
        importadditional.value = 'admin';
        recursiveselect.value = 'single';
        importfillns.value = 'none';
        reason.value='SECOND';
        other.value='';
        dryruncount.value = 0;
        showdryrun.value = false;
        loading.value = false;

        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        get_gradetypes();

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

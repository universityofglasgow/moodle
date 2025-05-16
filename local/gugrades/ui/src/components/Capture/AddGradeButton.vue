<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <a class="dropdown-item" href="#" @click="add_grade()">
        {{ buttontitle }}
    </a>

    <VueModal v-model="showaddgrademodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="buttontitle">

        <!-- Can either show add grade form or re-release dialogue -->
        <div v-if="!showreleaseddialogue">
            <ul class="list-unstyled">
                <li v-if="props.categoryid"><b>{{ mstrings.category }}:</b> {{ itemname }}</li>
                <li v-else><b>{{ mstrings.itemname }}:</b> {{ itemname }}</li>
                <li><b>{{ mstrings.username }}:</b> {{ name }}</li>
                <li><b>{{ mstrings.idnumber }}:</b> {{ idnumber }}</li>
                <li>{{ reason }}</li>
                <li v-if="overridden"><b>{{ mstrings.categoryoverridden }}</b></li>
                <li v-if="props.released"><b>{{ mstrings.releasedgrade }}</b></li>
            </ul>

            <!-- message if not available -->
            <div v-if="!available" class="alert alert-danger">
            {{ mstrings.overridenotavailable }}
            </div>

            <!-- message if error -->
            <div v-if="error" class="alert alert-danger">
                {{ mstrings.overrideerror}}
            </div>

            <FormKit v-if="!overridden && available && !error" class="border rounded" type="form"  @submit="submit_form">
                <FormKit
                    v-if="!iscategory"
                    type="select"
                    outer-class="mb-3"
                    :label="mstrings.reasonforadditionalgrade"
                    name="reason"
                    v-model="reason"
                    :options="gradetypes"
                    :placeholder="mstrings.selectareason"
                    :validation-messages="{
                        required: 'This field is required.',
                    }"
                    validation="required"
                    validation-visibility="live"
                />
                <FormKit
                    v-if = 'reason == "OTHER"'
                    :label="mstrings.pleasespecify"
                    type="text"
                    outer-class="mb-3"
                    :placeholder="mstrings.pleasespecify"
                    name="other"
                    v-model="other"
                    validation="required"
                    :validation-messages="{
                        required: 'This field is required.',
                    }"
                />
                <FormKit
                    type="select"
                    :label="mstrings.admingrades"
                    name="admingrades"
                    outer-class="mb-3"
                    v-model="admingrade"
                    :options="adminmenu"
                ></FormKit>
                <FormKit
                    v-if="usescale"
                    type="select"
                    outer-class="mb-3"
                    :label="mstrings.grade"
                    :placeholder="mstrings.specifyscale"
                    :disabled="admingrade != 'GRADE'"
                    name="scale"
                    v-model="scale"
                    :options="scalemenu"
                ></FormKit>
                <FormKit
                    v-if="!usescale"
                    type="text"
                    outer-class="mb-3"
                    :label="mstrings.grade"
                    :placeholder="mstrings.specifygrade"
                    :validation="gradevalidation"
                    :disabled="admingrade != 'GRADE'"
                    number="float"
                    validation-visibility="live"
                    name="grade"
                    v-model="grade"
                ></FormKit>
                <FormKit
                    type="textarea"
                    outer-class="mb-3"
                    label="Notes"
                    :placeholder="mstrings.reasonforammendment"
                    name="notes"
                    v-model="notes"
                />
            </FormKit>

            <div v-if="overridden" class="border rounded mt-3 p-4">
                <div class="alert alert-primary">
                    {{ mstrings.categoryremoveoverride }}
                </div>
                <a href="#" class="btn btn-primary" @click="removeoverride">{{ mstrings.remove }}</a>
            </div>

            <div class="row mt-2">
                <div class="col-sm-12">
                    <div class="float-right">
                        <button class="btn btn-warning" type="button" @click="showaddgrademodal = false">{{  mstrings.cancel }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- re-release after add dialogue -->
        <div v-if="showreleaseddialogue">
            <div class="alert alert-warning">
                {{ mstrings.releasefromadd }}
            </div>
            <div class="mt-2">
                <button class="btn btn-success" type="button" @click="release_grade">{{  mstrings.yes }}</button>
                <button class="btn btn-warning ml-1" type="button" @click="showaddgrademodal = false">{{  mstrings.no }}</button>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showaddgrademodal = ref(false);
    const showreleaseddialogue = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');
    const gradetypes = ref({});
    const idnumber = ref('');
    const reason = ref('');
    const admingrade = ref('GRADE'); // GRADE == not an admin grade (a real grade)
    const scale = ref('');
    const grade = ref(0);
    const notes = ref('');
    const other = ref('');
    const usescale = ref(false);
    const iscategory = ref(false);
    const overridden = ref(false);
    const available = ref(true);
    const error = ref(false);
    const grademax = ref(0);
    const scalemenu = ref([]);
    const adminmenu = ref([]);
    const gradevalidation = ref([]);

    const emit = defineEmits([
        'gradeadded'
    ]);

    const toast = useToast();

    const props = defineProps({
        userid: Number,
        itemid: Number,
        categoryid: Number,
        itemname: String,
        name: String,
        released: Boolean,
    });

    /**
     * The title can be for grade or category
     */
    const buttontitle = computed(() => {
        if (props.categoryid) {
            return mstrings.overridecategory;
        } else {
            return mstrings.addgrade;
        }
    });

    /**
     * Get data for form
     */
    function add_grade() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_add_grade_form',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
            }
        }])[0]
        .then((result) => {
            gradetypes.value = result.gradetypes;
            idnumber.value = result.idnumber;
            usescale.value = result.usescale;
            iscategory.value = result.iscategory;
            overridden.value = result.overridden;
            available.value = result.available;
            error.value = result.error;
            grademax.value = result.grademax;
            scalemenu.value = result.scalemenu;
            adminmenu.value = result.adminmenu;

            // Add 'use grade' option onto front of adminmenu
            adminmenu.value.unshift({
                value: 'GRADE',
                label: mstrings.selectnormalgrade,
            });

            gradevalidation.value = [
                ['required'],
                ['number'],
                ['between', 0, result['grademax']],
            ];
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
            showaddgrademodal.value = false;
        });

        showaddgrademodal.value = true;
    }

    /**
     * Process form submission
     */
    function submit_form() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // We don't ask for the reason if a category. So...
        if (iscategory.value) {
            reason.value = 'CATEGORY';
        }

        fetchMany([{
            methodname: 'local_gugrades_write_additional_grade',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
                admingrade: admingrade.value == 'GRADE' ? '' : admingrade.value,
                reason: reason.value,
                other: other.value,
                scale: scale.value ? scale.value : 0, // WS expecting int
                grade: grade.value,
                notes: notes.value,
            }
        }])[0]
        .then(() => {
            emit('gradeadded');
            toast.success(mstrings.gradeadded);

            // If the grade was released then we have more stuff to do
            if (props.released) {
                showreleaseddialogue.value = true;
            } else {
                showaddgrademodal.value = false;
            }
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
            showaddgrademodal.value = false;
        });
    }


    /**
     * Grade gets re-released after add
     */
    function release_grade() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_release_grade',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
            }
        }])[0]
        .then(() => {

            // This will be sufficient to re-aggregate and so on.
            emit('gradeadded');
            showaddgrademodal.value = false;
            toast.success(mstrings.gradesreleased);
        })
        .catch((error) => {
            window.console.error(error);
            showaddgrademodal.value = false;
            debug.value = error;
        });


    }

    /**
     * Remove override button has been clicked
     *
     */
    function removeoverride() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Scale and grade are both 0 = remove override
        fetchMany([{
            methodname: 'local_gugrades_write_additional_grade',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
                admingrade: '',
                reason: 'CATEGORY',
                other: other.value,
                scale: 0,
                grade: 0,
                notes: '',
                delete: true,
            }
        }])[0]
        .then(() => {
            emit('gradeadded');
            toast.success(mstrings.gradeadded);
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
            showaddgrademodal.value = false;
        });

        // close the modal
        showaddgrademodal.value = false;
    }
</script>
<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div class="row" style="min-width: 250px;">
        <FormKit
            type="select"
            name="admingrades"
            outer-class="col pr-1"
            v-model="admingrade"
            :options="adminmenu"
            @input="input_updated"
        ></FormKit>
        <FormKit
            v-if="!props.usescale"
            outer-class="col pl-0"
            type="text"
            number="float"
            :validation="gradevalidation"
            validation-visibility="live"
            maxlength="8"
            name="grade"
            v-model="grade"
            :disabled="admingrade != 'GRADE'"
            @input="input_updated"
        ></FormKit>
        <FormKit
            v-if="props.usescale"
            type="select"
            :placeholder="mstrings.scale"
            outer-class="col pl-0"
            :disabled="admingrade != 'GRADE'"
            name="scale"
            v-model="grade"
            :options="scalemenu"
            @input="input_updated"
        ></FormKit>
    </div>
</template>

<script setup>
    import {ref, defineProps, onMounted, onBeforeUnmount, defineEmits, inject, watch, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    // (item.id is current userid)
    // (item.reason is the reason/gradetype)
    // (item.other is the other text)
    // (item.gradeitemid)
    const props = defineProps({
        item: Object,
        gradeitemid: Number,
        column: String,
        columnid: Number,
        other: String,
        notes: String,
        gradetype: String,
        usescale: Boolean,
        scalemenu: Array,
        adminmenu: Array,
        grademax: Number,
        cancelled: Boolean,
    });

    const grade = ref('');
    let   originalgrade = '';
    let   originaladmingrade = '';
    //const scale = ref(0);
    const admingrade = ref('GRADE');
    const edited = ref(false);
    const toast = useToast();
    const mstrings = inject('mstrings');

    const emits = defineEmits(['gradewritten', 'gradecancel']);

    // validation depends on grademax
    const gradevalidation = computed(() => {
        return [
            ['number'],
            ['between', 0, props.grademax],
        ];
    })

    /**
     * Watch out for cancel being clicked.
     * This carry on because the prop doesn't get updated when
     * unMount in progress.
     * Each cell will emit so CaptureTable debounces it to avoid multiple
     * page refreshes.
     */
    watch(
        () => props.cancelled,
        () => {
            emits('gradecancel');
        }
    );

    /**
     * Mostly, set up initial values for the form.
     */
    onMounted(() => {

        // Extract the correct current grade from the item
        const value = props.item[props.column];

        // If it's a scale - find the value
        if (props.usescale) {
            props.scalemenu.forEach((scaleitem) => {
                if (scaleitem.label == value) {
                    grade.value = scaleitem.value;
                }
            });
        } else {
            grade.value = value;
        }

        // Could is be an admingrade?
        props.adminmenu.forEach((adminitem) => {
            if (adminitem.value == value) {
                admingrade.value = value;
                grade.value = '';
            }
        });

        originalgrade = grade.value;
        originaladmingrade = admingrade.value;
    });

    /**
     * Change made to edit box
     *
     */
    function input_updated() {

        // If anything has changed, flag that we will need
        // to save it at some point.
        edited.value = true;
    }

    /**
     * When this component closes, save the data
     */
    onBeforeUnmount(() => {

        // if this cell hasn't been edited then nothing to do!
        if (props.cancelled || ((originalgrade == grade.value) && (originaladmingrade == admingrade.value))) {
            return;
        }

        const userid = props.item.id;
        const reason = props.gradetype;
        const other = props.other;
        const gradeitemid = props.gradeitemid;
        const saveadmingrade = admingrade.value == 'GRADE' ? '' : admingrade.value;
        const savescale = (admingrade.value == 'GRADE') && props.usescale ? grade.value : 0;
        const savegrade = (admingrade.value == 'GRADE') && !props.usescale ? grade.value : 0;
        const notes = props.notes;

        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_write_additional_grade',
            args: {
                courseid: courseid,
                gradeitemid: gradeitemid,
                userid: userid,
                admingrade: saveadmingrade,
                reason: reason,
                other: other,
                scale: savescale,
                grade: parseFloat(savegrade),
                notes: notes,
            }
        }])[0]
        .then(() => {
            //emits('gradewritten');
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });

        emits('gradewritten');
    });

</script>
<template>
    <FormKit v-if="loaded" type="form" submit-label="Save" @submit="submit_form">
        <FormKit
            type="text"
            outer-class="mb-3"
            :label="mstrings.conversionmapname"
            validation-visibility="live"
            validation="required"
            name="mapname"
            v-model="mapname"
        ></FormKit>
        <FormKit
            type="text"
            outer-class="mb-3"
            :label="mstrings.maxgrade"
            number="float"
            validation="required|between:0,100"
            validation-visibility="live"
            name="maxgrade"
            v-model="maxgrade"
        ></FormKit>
        <FormKit
            type="select"
            :label="mstrings.scaletype"
            :disabled="props.mapid != 0"
            name="scaletype"
            v-model="scaletype"
            value="schedulea"
            :options="scaletypeoptions"
        ></FormKit>
        <p class="mb-1 mt-3">{{ mstrings.entrytype }}</p>
        <FormKit
            v-model="entrytype"
            type="radio"
            :options="entrytypeoptions"
        ></FormKit>
        <div class="row mt-3">
            <div class="col-2"><h3>{{  mstrings.band }}</h3></div>
            <div class="col-5"><h3>{{ mstrings.percentage}}</h3></div>
            <div class="col-5"><h3>{{ mstrings.points }}</h3></div>
        </div>

        <div  class="row" v-for="item in items" :key="item.band">
            <div class="col-2 pt-2">
                <h3>{{  item.band  }}</h3>
            </div>
            <div class="col-5">
                <FormKit
                    type="text"
                    outer-class="mb-3"
                    :disabled="entrytype != 'percentage'"
                    :validation-rules="{ validate_order }"
                    validation="required|validate_order|between:0,100"
                    validation-visibility="live"
                    :validation-messages="{
                        between: 'Percentage must be between 0 and 100',
                        validate_order: 'Values must be in ascending sequence',
                    }"
                    v-model="item.boundpc"
                ></FormKit>
            </div>
            <div class="col-5">
                <FormKit
                    type="text"
                    number="float"
                    outer-class="mb-3"
                    :disabled="entrytype != 'points'"
                    :validation-rules="{ validate_points, validate_order }"
                    validation="required|validate_points|validate_order"
                    validation-visibility="live"
                    :validation-messages="{
                        validate_points: 'Number must be between 0 and ' + maxgrade,
                        validate_order: 'Values must be in ascending sequence',
                    }"
                    v-model="item.boundpoints"
                ></FormKit>
            </div>
        </div>
        <button class="btn btn-warning float-right" @click="cancel_button">{{ mstrings.cancel }}</button>
    </FormKit>

</template>

<script setup>
    import {ref, inject, defineProps, defineEmits, onMounted, watch} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import { watchDebounced } from '@vueuse/core';

    const mstrings = inject('mstrings');
    const loaded = ref(false);
    const mapname = ref('');
    const maxgrade = ref(100);
    const rawmap = ref([]);
    const items = ref([]);
    const scaletype = ref('schedulea');
    const entrytype = ref('percentage');
    const scaletypeoptions = [
        {value: 'schedulea', label: 'Schedule A'},
        {value: 'scheduleb', label: 'Schedule B'},
    ];
    const entrytypeoptions = [
        {value: 'percentage', label: 'Percentage'},
        {value: 'points', label: 'Points'},
    ];

    const toast = useToast();

    const props = defineProps({
        mapid: Number,
    });

    const emits = defineEmits(['close']);

    /**
     * Round values to 2 decimal place
     * TODO: This might change
     */
    function precision(n) {
        return Math.floor(n * 100) / 100;
    }

    /**
     * Build items array
     * (depending on scale type)
     */
    function build_items() {
        items.value = [];
        rawmap.value.forEach((item) => {
            items.value.push({
                band: item.band,
                grade: item.grade,
                boundpc: item.bound,
                boundpoints: precision(item.bound * maxgrade.value / 100),
            });
        });
    }

    /**
     * Recalculate items.
     * When settings change match percent to point according to
     * entrytypeoptions setting
     */
    function recalculate() {
        items.value.forEach((item) => {

            // If percent selected then recalc points
            if (entrytype.value == 'percentage') {
                item.boundpoints = precision(item.boundpc * maxgrade.value / 100);
            }

            // If points selected then recalc percent
            if (entrytype.value == 'points') {
                item.boundpc = precision(item.boundpoints * 100 / maxgrade.value);
            }
        })
    }

    /**
     * If maxgrade changes then we need to recalculate the map
     */
     watchDebounced(
        maxgrade,
        () => {
            build_items();
        },
        { debounce: 500, maxWait: 1000 },
    );

    /**
     * If the schedule changes then the map can be reloaded
     * only if mapid==0. If it's an existing map, then it would
     * need to be deleted and recreated
     */
    watch(
        scaletype,
        () => {
            if (props.mapid == 0) {
                update_map();
            }
        }
    );

    /**
     * Watch the map array for changes to
     */
    watch(
        items,
        () => {
            recalculate();
        },
        {deep: true},
    );

    /**
     * Custom rule for points values
     */
    function validate_points(node) {
        return (node.value >= 0) && (node.value <= maxgrade.value);
    }

    /**
     * Custom rule to check that points/percentages are in order
     */
    function validate_order() {
        let currentpercent = 0;
        let currentpoints = 0;
        let inorder = true;
        items.value.forEach((item) => {
            if (currentpercent > item.boundpc) {
                inorder = false;
            } else {
                currentpercent = item.boundpc;
            }
            if (currentpoints > item.boundpoints) {
                inorder = false;
            } else {
                currentpoints = item.boundpoints;
            }
        });

        return inorder;
    }

    /**
     * Form submitted
     */
    function submit_form() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        const map = [];
        items.value.forEach((item) => {
            map.push({
                band: item.band,
                bound: item.boundpc,
                grade: item.grade,
            });
        });

        fetchMany([{
            methodname: 'local_gugrades_write_conversion_map',
            args: {
                courseid: courseid,
                mapid: props.mapid,
                name: mapname.value,
                schedule: scaletype.value,
                maxgrade: maxgrade.value,
                map: map,
            }
        }])[0]
        .then(() => {
            toast.success(mstrings.conversionmapsaved);
            emits('close')
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }

    /**
     * Cancel button pressed
     */
    function cancel_button() {
        emits('close');
    }

    /**
     * Update the conversion map
     */
    function update_map() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_conversion_map',
            args: {
                courseid: courseid,
                mapid: props.mapid,
                schedule: scaletype.value,
            }
        }])[0]
        .then((result) => {
            mapname.value = result.name;
            scaletype.value = result.schedule;
            maxgrade.value = result.maxgrade;
            rawmap.value = result.map;

            build_items();

            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }

    /**
     * Is this a new map (id=0) or an existing one
     */
    onMounted(() => {
        update_map();
    })
</script>
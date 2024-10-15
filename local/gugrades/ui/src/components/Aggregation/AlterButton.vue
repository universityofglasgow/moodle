<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <a class="dropdown-item" href="#" @click.prevent="alter_weights()">
        {{ mstrings.altertitle }}
    </a>

    <VueModal v-model="showaltermodal" modalClass="col-11 col-lg-5 rounded" :title="mstrings.altertitle">

        <!-- basic details of category -->
        <ul class="list-unstyled">
            <li><b>{{ mstrings.category }}:</b> {{ categoryname }}</li>
            <li><b>{{ mstrings.username }}:</b> {{ userfullname }}</li>
            <li><b>{{ mstrings.idnumber }}:</b> {{ idnumber }}</li>
        </ul>

        <!-- grade items therein -->
        <div class="border mt-3 p-2">
            <div class="row mt-1 mb-2 font-weight-bolder">
                <div class="col">{{ mstrings.gradeitem }}</div>
                <div class="col">{{ mstrings.gradetype }}</div>
                <div class="col">{{ mstrings.grade }}</div>
                <div class="col">{{ mstrings.defaultweights }}</div>
                <div class="col">{{ mstrings.alteredweights }}</div>
            </div>
            <div v-for="item in items" class="row mt-1">
                <div class="col"><b>{{ item.fullname }}</b></div>
                <div class="col">{{ item.gradetype }}</div>
                <div class="col">{{ item.display }}</div>
                <div class="col">
                    {{ item.originalweight }}
                </div>
                <div class="col">
                    <FormKit
                        type="number"
                        number="float"
                        outer-class="mb-3"
                        placeholder="new weight"
                        name="weight"
                        step="0.05"
                        validation="between:0,1"
                        validation-visibility="live"
                        v-model="item.alteredweight"
                    />
                </div>
            </div>
            <div class="row mt-1">
                <div class="col font-weight-bold">{{ mstrings.sumofweights }}</div>
                <div class="col">&nbsp;</div>
                <div class="col">&nbsp;</div>
                <div class="col">{{ defaulttotal.toFixed(5) }}</div>
                <div class="col">{{ alteredtotal.toFixed(5) }}</div>
            </div>
            <div v-if="!closeenough" class="mt-2 text-danger">{{ mstrings.donotaddto1 }}</div>
        </div>

        <div class="mt-2">
            <button class="btn btn-primary mr-1" type="button" @click="save_altered">{{  mstrings.save }}</button>
            <button class="btn btn-info mr-1" type="button" @click="revert_altered">{{  mstrings.revert }}</button>
            <button class="btn btn-warning" type="button" @click="showaltermodal = false">{{  mstrings.cancel }}</button>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showaltermodal = ref(false);
    const mstrings = inject('mstrings');
    const debug = ref({});
    const toast = useToast();
    const categoryname = ref('');
    const userfullname = ref('');
    const idnumber = ref('');
    const items = ref([]);

    const props = defineProps({
        userid: Number,
        itemid: Number,
        categoryid: Number,
    });

    /**
     * Calculate altered weight total
     */
    const alteredtotal = computed(() => {
        var total = 0.0;
        items.value.forEach((item) => {
            total = total + parseFloat(item.alteredweight);
        });

        return total;
    });

    /**
     * Calculate default weight total
     */
     const defaulttotal = computed(() => {
        var total = 0.0;
        items.value.forEach((item) => {
            total = total + parseFloat(item.originalweight);
        });

        return total;
    });

    /**
     * Is total "close enough" to 1.0
     */
    const closeenough = computed(() => {
        var total = 0.0;
        items.value.forEach((item) => {
            total = total + parseFloat(item.alteredweight);
        });
        const error = Math.abs(total - 1);

        return error < 0.01;
    })

    /**
     * Alter weights button has been clicked
     */
    function alter_weights() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        showaltermodal.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_alter_weight_form',
            args: {
                courseid: courseid,
                categoryid: props.categoryid,
                userid: props.userid,
            }
        }])[0]
        .then((result) => {
            categoryname.value = result.categoryname;
            userfullname.value = result.userfullname;
            idnumber.value = result.idnumber;
            items.value = result.items;

            window.console.log(result);
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }
</script>
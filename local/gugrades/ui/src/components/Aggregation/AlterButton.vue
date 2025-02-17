<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <a class="dropdown-item" href="#" @click.prevent="alter_weights()">
        {{ mstrings.altertitle }}
    </a>

    <VueModal v-model="showaltermodal" enableClose="false" modalClass="col-11 col-lg-5 rounded scrollable-modal" :title="mstrings.altertitle">

        <div v-if="loading" class="alert alert-info">
            {{ mstrings.pleasewait }}
        </div>

        <div v-if="!loading" class="scrollable-content">

            <!-- basic details of category -->
            <ul class="list-unstyled">
                <li><b>{{ mstrings.category }}:</b> {{ categoryname }}</li>
                <li><b>{{ mstrings.username }}:</b> {{ userfullname }}</li>
                <li><b>{{ mstrings.idnumber }}:</b> {{ idnumber }}</li>
            </ul>

            <!-- grade items therein -->
            <div class="border rounded mt-3 p-2">
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

            <!-- reason -->
            <div class="border rounded mt-2 px-3">
                <FormKit
                    type="textarea"
                    outer-class="mb-3"
                    :label="mstrings.reasonforammendment"
                    name="reason"
                    v-model="reason"
                />
            </div>

            <div class="mt-2">
                <button class="btn btn-primary mr-1" type="button" @click="save_altered_weights">{{  mstrings.save }}</button>
                <button class="btn btn-info mr-1" type="button" @click="revert_altered_weights">{{  mstrings.revert }}</button>
                <button class="btn btn-warning" type="button" @click="showaltermodal = false">{{  mstrings.cancel }}</button>
            </div>

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
    const reason = ref('');
    const loading = ref(true);

    const props = defineProps({
        userid: Number,
        itemid: Number,
        categoryid: Number,
    });

    const emit = defineEmits([
        'weightsaltered'
    ]);

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

        reason.value = '';

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
            loading.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Save altered weights
     */
    function save_altered_weights() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        const saveitems = [];
        items.value.forEach((item) => {
            saveitems.push({
                gradeitemid: item.gradeitemid,
                weight: item.alteredweight,
            });
        });

        fetchMany([{
            methodname: 'local_gugrades_save_altered_weights',
            args: {
                courseid: courseid,
                categoryid: props.categoryid,
                userid: props.userid,
                revert: false,
                reason: reason.value,
                items: saveitems,
            }
        }])[0]
        .then((result) => {
            emit('weightsaltered');
            toast.success(mstrings.weightsaltered);

            showaltermodal.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Revert altered weights
     */
     function revert_altered_weights() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_save_altered_weights',
            args: {
                courseid: courseid,
                categoryid: props.categoryid,
                userid: props.userid,
                revert: true,
                reason: '',
                items: [],
            }
        }])[0]
        .then((result) => {
            emit('weightsaltered');
            toast.success(mstrings.weightsreverted);

            showaltermodal.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }
</script>

<style>
    .scrollable-modal {
    display: flex;
    flex-direction: column;
    height: calc(100% - 150px);
    }
    .scrollable-modal .vm-titlebar {
    flex-shrink: 0;
    }
    .scrollable-modal .vm-content {
    padding: 0;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    }
    .scrollable-modal .vm-content .scrollable-content {
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 15px 10px 15px;
    flex-grow: 1;
    }
    .scrollable-modal .scrollable-modal-footer {
    padding: 15px 0px 15px 0px;
    border-top: 1px solid #e5e5e5;
    margin-left: 0;
    margin-right: 0;
    }
</style>
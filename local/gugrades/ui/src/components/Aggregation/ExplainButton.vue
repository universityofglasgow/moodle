<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <a class="dropdown-item" href="#" @click.prevent="explain()">
        {{ mstrings.explain }}
    </a>

    <VueModal v-model="showexplainmodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded scrollable-modal" :title="mstrings.explain">

        <div v-if="loading" class="alert alert-info">
            {{ mstrings.pleasewait }}
        </div>

        <div v-if="!loading" class="scrollable-content">

            <!-- user stuffs -->
            <div class="border rounded my-2 p-2 text-center">
                <img :src="user.pictureurl" :alt="displayname" class="userpicture defaultuserpic" width="35" height="35"/> <b>{{ user.displayname }}</b>
            </div>

            <!-- details -->
            <div class="border rounded my-2 p-2">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th>{{ mstrings.idnumber }}</th>
                            <td>{{ user.idnumber }}</td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.completed }}</th>
                            <td>{{ user.completed }}&percnt;</td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.gradecategory }}</th>
                            <td>{{ user.itemname }}</td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.aggregatedgrade }}</th>
                            <td>{{ user.displaygrade }}</td>
                        </tr>
                        <tr v-if="user.rawgrade">
                            <th>{{ mstrings.rawgrade }}</th>
                            <td>{{ user.rawgrade }}</td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.overridden }}</th>
                            <td><YesNo :yes="user.overridden"></YesNo></td>
                        </tr>
                        <tr v-if="user.showweights">
                            <th>{{ mstrings.alteredweights }}</th>
                            <td><YesNo :yes="user.alteredweight"></YesNo></td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.strategy }}</th>
                            <td>{{ user.strategy }}</td>
                        </tr>
                        <tr>
                            <th>{{ mstrings.gradetype }}</th>
                            <td>{{ user.formattedatype }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- component grades -->
            <div class="border rounded my-2 p-2">
                <h5>{{ mstrings.grades }}</h5>
                <table class="table table-striped">
                    <tbody>
                        <tr v-for="field in user.fields">
                            <th>{{ field.itemname }}</th>
                            <td>
                                <ul class="list-unstyled">
                                    <li><b>{{ field.display }}</b></li>
                                    <li v-if="!field.available">{{ mstrings.notavailable }}</li>
                                    <li v-if="field.dropped">{{ mstrings.dropped }}</li>
                                    <li v-if="field.hidden">{{ mstrings.hidden }}</li>
                                    <li v-if="field.overridden">{{ mstrings.overridden }}</li>
                                    <li v-if="user.showweights">{{ mstrings.weight }}: {{ field.weight }}%</li>
                                    <li v-if="user.showweights && field.normalisedweight">{{ mstrings.normalisedweight }}: {{ field.normalisedweight }}&percnt;</li>
                                    <li v-if="user.showweights && user.alteredweight">{{ mstrings.alteredweight }}: {{ field.alteredweight }}&percnt;</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- explanation -->
            <div class="border rounded my-2 p-2">
                <h5>{{ mstrings.explanation }}</h5>
                <div class="alert alert-info">{{ user.explain }}</div>
            </div>

            <div class="mt-2">
                <button class="btn btn-warning" type="button" @click="showexplainmodal = false">{{  mstrings.close }}</button>
            </div>

        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, inject} from '@vue/runtime-core';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import YesNo from '@/components/YesNo.vue';

    const showexplainmodal = ref(false);
    const mstrings = inject('mstrings');
    const debug = ref({});
    const loading = ref(true);
    const user = ref([]);

    const props = defineProps({
        userid: Number,
        itemid: Number,
        categoryid: Number,
    });


    /**
     * Alter weights button has been clicked
     */
    function explain() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        showexplainmodal.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_explain_aggregation',
            args: {
                courseid: courseid,
                gradecategoryid: props.categoryid,
                userid: props.userid,
            }
        }])[0]
        .then((result) => {
            user.value = result;

            loading.value = false;
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
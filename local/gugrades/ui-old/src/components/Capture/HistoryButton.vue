<template>
    <a class="dropdown-item" href="#" @click="read_history()">{{ mstrings.history }}</a>

    <VueModal v-model="showhistorymodal" modalClass="col-11 col-lg-5 rounded" :title="mstrings.gradehistory">
        <div>
            <ul class="list-unstyled">
                <li><b>{{ mstrings.name }}:</b> {{ name }}</li>
                <li><b>{{ mstrings.itemname }}:</b> {{ itemname }}</li>
            </ul>
        </div>
        <div v-if="grades.length == 0" class="alert alert-warning">{{ mstrings.nohistory }}</div>

        <EasyDataTable v-else :headers="headers" :items="grades">
        </EasyDataTable>

        <div class="row mt-2">
            <div class="col-sm-12">
                <div class="float-right">
                    <button class="btn btn-warning" type="button" @click="showhistorymodal = false">{{  mstrings.close }}</button>
                </div>
            </div>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, onMounted, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const showhistorymodal = ref(false);
    const grades = ref([]);
    const mstrings = inject('mstrings');
    const headers = ref([]);

    const toast = useToast();

    const props = defineProps({
        userid: Number,
        itemid: Number,
        name: String,
        itemname: String,
    });

    /**
     * Read history on button click
     */
    function read_history() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_history',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
            }
        }])[0]
        .then((result) => {
            grades.value = result;
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });

        showhistorymodal.value = true;
    }

    /**
     * Setup the table.
     */
    onMounted(() => {
        headers.value = [
               {text: mstrings.time, value: 'time'},
               {text: mstrings.by, value: 'auditbyname'},
               {text: mstrings.grade, value: 'displaygrade'},
               {text: mstrings.gradetype, value: 'description'},
               {text: mstrings.current, value: 'current'},
               {text: mstrings.comment, value: 'auditcomment'},
            ];
    });
</script>

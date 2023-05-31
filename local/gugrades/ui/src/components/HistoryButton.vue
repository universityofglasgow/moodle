<template>
    <button type="button" class="btn btn-outline-primary btn-sm" @click="read_history()">{{ strings.history }}</button>

    <Teleport to="body">
        <ModalForm :show="showhistorymodal" @close="showhistorymodal = false">
            <template #header>
                <h4>History</h4>
                
            </template>
            <template #body>
                <div v-if="grades.length == 0" class="alert alert-warning">{{ strings.nohistory }}</div>
                <div v-for="grade in grades" :key="grade.id" class="mb-3 border-bottom">
                    <ul>
                        <li>Grade {{ grade.grade }}</li>
                        <li>Reason {{ grade.reasonname }}</li>
                        <li>Time {{  grade.time }}</li>
                        <li>Current {{ grade.current }}</li>
                    </ul>
                </div>
            </template>
        </ModalForm>
    </Teleport>
</template>

<script setup>
    import {ref, defineProps, onMounted} from '@vue/runtime-core';
    import ModalForm from '@/components/ModalForm.vue';
    import { getstrings } from '@/js/getstrings.js';
    import { useToast } from "vue-toastification";

    const showhistorymodal = ref(false);
    const grades = ref([]);
    const strings = ref({});

    const toast = useToast();

    const props = defineProps({
        userid: Number,
        itemid: Number
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
            window.console.log(result);
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });

        showhistorymodal.value = true;
    }

    /**
     * Load strings (mostly for table) and get initial data for table.
     */
    onMounted(() => {

        // Get the moodle strings for this page
        const stringslist = [
            'history',
            'nohistory'
        ];
        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });
    })
</script>
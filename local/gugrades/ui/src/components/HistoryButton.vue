<template>
    <button type="button" class="btn btn-outline-primary btn-sm" @click="read_history()">History</button>

    <Teleport to="body">
        <ModalForm :show="showhistorymodal" @close="showhistorymodal = false">
            <template #header>
                <h4>History</h4>
                
            </template>
            <template #body>
                <div v-if="grades.length == 0" class="alert alert-warning">No history</div>
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
    import {ref, defineProps} from '@vue/runtime-core';
    import ModalForm from '@/components/ModalForm.vue';

    const showhistorymodal = ref(false);
    const grades = ref([]);

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
        });

        showhistorymodal.value = true;
    }
</script>
<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div class="">
        <select class="form-control border-dark" @change="selectMenuChange($event)">
            <option value="0">{{ mstrings.selectuser }}</option>
            <option v-for="student in students" :key="student.id" :value="student.id" :selected="selected == student.id">{{ student.fullname }}</option>
        </select>
    </div>

    <div v-if="!collapsed">
        <!-- Please wait spinner -->
        <PleaseWait v-if="loading"></PleaseWait>
    </div>
</template>

<script setup>
    import {ref, onMounted, defineEmits, inject} from '@vue/runtime-core';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const students = ref([]);
    const selected = ref(0);
    const debug = ref({});
    const mstrings = inject('mstrings');

    const emit = defineEmits(['selectmenuchange']);

    // Get the top level categories
    function getStudents() {
        const SMGSV = window.SMGSV;
        const courseid = SMGSV.courseid;
        const fetchMany = SMGSV.fetchMany;

        fetchMany([{
            methodname: 'local_studentmygradesstaffview_get_students',
            args: {
                courseid
            }
        }])[0]
        .then((result) => {
            students.value = result;
            if (result.length == 0) {
                notsetup.value = true;
            }
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        })
    }

    // Handle change of selection in dropdown.
    function selectMenuChange(event) {
        const studentid = event.target.value;
        emit('selectmenuchange', studentid);
    }

    onMounted(() => {
        getStudents();
        if (selected.value != 0) {
            emit('selectmenuchange', selected.value);
        }
    });
</script>

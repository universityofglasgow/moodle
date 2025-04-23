<template>
    <div v-if="showgroupselect" class="mt-2">
        <select class="form-control border-dark" @change="group_change($event)">
            <option value="0">{{ mstrings.allparticipants }}</option>
            <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
        </select>
    </div>

</template>

<script setup>
    import {ref, onMounted, defineEmits, inject} from '@vue/runtime-core';

    const groups = ref([]);
    const mstrings = inject('mstrings');
    const showgroupselect = ref(false);

    const emit = defineEmits(['groupselected']);

    /**
     * Get groups for this course.
     */
    function get_groups() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_groups',
            args: {
                courseid
            }
        }])[0]
        .then((result) => {
            groups.value = result;
            showgroupselect.value = groups.value.length > 0;
        })
        .catch((error) => {
            window.console.error(error);
        })
    }

    // Handle change of selection in dropdown.
    function group_change(event) {
        const groupid = event.target.value;
        emit('groupselected', groupid);
    }

    onMounted(() => {
        get_groups();
    });
</script>
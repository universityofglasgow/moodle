<template>
    <div>
        <NameFilter @selected="filter_selected"></NameFilter>
        <table class="table table-striped mt-4 border rounded">
            <thead class="thead-light">
                <th><MString name="firstnamelastname"></MString></th>
                <th><MString name="idnumber"></MString></th>
                <th>Grade</th>
                <th> </th>
            </thead>
            <tbody>
                <tr v-for="user in props.users" :key="user.id">
                    <td>{{ user.firstname }} {{ user.lastname }}</td>
                    <td>{{ user.idnumber }}</td>
                    <td><MString name="awaitingcapture"></MString></td>
                    <td><button type="button" class="btn btn-outline-primary">Add grade</button></td>
                </tr>
            </tbody>
        </table>
    </div>   
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import NameFilter from '@/components/NameFilter.vue';

    const props = defineProps({
        users: Array,
    });

    const emit = defineEmits('filterchanged');

    function filter_selected(first, last) {
        if (first == 'all') {
            first = '';
        }
        if (last == 'all') {
            last = '';
        }
        emit('filterchanged', first, last)
    }
</script>
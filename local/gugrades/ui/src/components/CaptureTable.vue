<template>
    <div>
        <NameFilter></NameFilter>
        <table class="table table-striped mt-4 border rounded">
            <thead class="thead-light">
                <th>Firstname/lastname</th>
                <th>ID number</th>
                <th>Grade</th>
                <th> </th>
            </thead>
            <tbody>
                <tr v-for="user in props.users" :key="user.id">
                    <td>{{ user.firstname }} {{ user.lastname }}</td>
                    <td>{{ user.idnumber }}</td>
                    <td>Awaiting import</td>
                    <td><button type="button" class="btn btn-outline-primary">Add grade</button></td>
                </tr>
            </tbody>
        </table>
    </div>   
</template>

<script setup>
    import {defineProps} from '@vue/runtime-core';
    //import MString from '@/components/MString.vue';
    import NameFilter from '@/components/NameFilter.vue';

    const props = defineProps({
        users: Array,
    });
</script>
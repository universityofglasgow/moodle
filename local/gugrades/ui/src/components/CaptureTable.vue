<template>
    <table class="table table-striped mt-4 border rounded">
        <tbody>
            <tr v-for="user in props.users" :key="user.id">
                <td>{{ user.firstname }} {{ user.lastname }}</td>
                <td>{{ user.idnumber }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
</template>

<script setup>
    import {defineProps} from '@vue/runtime-core';
    //import MString from '@/components/MString.vue';

    const props = defineProps({
        users: Array,
    });
</script>
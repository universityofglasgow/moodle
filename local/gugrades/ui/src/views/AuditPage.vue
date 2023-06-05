<template>
    <div>
        <table class="table table-striped">
            <tbody>
                <tr v-for="item in items" :key="item.id" :class="item.bgcolor">
                    <td>{{ item.time }}</td>
                    <td>{{ item.type }}</td>
                    <td>{{ item.message }}</td>
                </tr>
            </tbody>
        </table>

    </div>
</template>

<script setup>
    import {ref, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const items = ref([]);

    const toast = useToast();

    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_audit',
            args: {
                courseid: courseid,
                userid: 0
            }
        }])[0]
        .then((result) => {
            window.console.log(result);
            items.value = result;
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        })
    });
</script>
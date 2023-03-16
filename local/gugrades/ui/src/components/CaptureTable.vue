<template>
    <div>
        <NameFilter @selected="filter_selected"></NameFilter>
        <div class="table-responsive">
            <table v-if="showtable" class="table table-striped table-sm mt-4 border rounded">
                <thead class="thead-light">
                    <th><MString name="firstnamelastname"></MString></th>
                    <th><MString name="idnumber"></MString></th>
                    <th>Grade</th>
                    <th> </th>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.firstname }} {{ user.lastname }}</td>
                        <td>{{ user.idnumber }}</td>
                        <td><MString name="awaitingcapture"></MString></td>
                        <td><button type="button" class="btn btn-outline-primary btn-sm">Add grade</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2 v-if="!showtable"><MString name="nothingtodisplay"></MString></h2>
    </div>   
</template>

<script setup>
    import {ref, defineProps, computed, watch, onMounted} from '@vue/runtime-core';
    import MString from '@/components/MString.vue';
    import NameFilter from '@/components/NameFilter.vue';

    const props = defineProps({
        itemid: Number,
    });

    const users = ref([]);

    let firstname = '';
    let lastname = '';

    /**
     * Get filtered/paged data
     */
     function get_page_data(itemid, first, last) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;        

        fetchMany([{
            methodname: 'local_gugrades_get_capture_page',
            args: {
                courseid: courseid,
                gradeitemid: itemid,
                pageno: 0,
                pagelength: 0,
                firstname: first,
                lastname: last,
            }
        }])[0]
        .then((result) => {
            users.value = JSON.parse(result['users']);
            window.console.log(users.value);
        })
        .catch((error) => {
            window.console.log(error);
        });        
    }

    /**
     * Firstname/lastname filter selected
     * @param {*} first 
     * @param {*} last 
     */
    function filter_selected(first, last) {
        window.console.log('FILTER ', first, last);
        if (first == 'all') {
            first = '';
        }
        if (last == 'all') {
            last = '';
        }
        firstname = first;
        lastname = last;
        window.console.log('FILTER 2', firstname, lastname);
        get_page_data(props.itemid, first, last);
    }

    const showtable = computed(() => {
        return users.value.length != 0;
    });

    watch(() => props.itemid, (itemid) => {
        get_page_data(itemid, firstname, lastname);
    })

    onMounted(() => {
        get_page_data(props.itemid, firstname, lastname);
    })
</script>
<template>
    <div>
        <NameFilter @selected="filter_selected"></NameFilter>
        <div class="table-responsive">
            <table v-if="showtable" class="table table-striped table-sm mt-4 border rounded">
                <thead class="thead-light">
                    <th>{{ strings.firstnamelastname }}</th>
                    <th>{{ strings.idnumber }}</th>
                    <th>{{ strings.grade }}</th>
                    <th> </th>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.firstname }} {{ user.lastname }}</td>
                        <td>{{ user.idnumber }}</td>
                        <td>{{ strings.awaitingcapture }}</td>
                        <td><button type="button" class="btn btn-outline-primary btn-sm">{{ strings.addgrade }}</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2 v-if="!showtable">{{ strings.nothingtodisplay }}</h2>
    </div>   
</template>

<script setup>
    import {ref, defineProps, computed, watch, onMounted} from '@vue/runtime-core';
    import NameFilter from '@/components/NameFilter.vue';
    import { getstrings } from '@/js/getstrings.js';

    const props = defineProps({
        itemid: Number,
    });

    const users = ref([]);
    const strings = ref({});

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

        // Get the moodle strings for this page
        const stringslist = [
            'addgrade',
            'awaitingcapture',
            'firstnamelastname',
            'idnumber',
            'nothingtodisplay',
            'grade'
        ];
        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        });

        // Get the data for the table
        get_page_data(props.itemid, firstname, lastname);
    })
</script>
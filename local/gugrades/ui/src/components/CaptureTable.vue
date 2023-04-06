<template>
    <div>
        <NameFilter v-if="!usershidden" @selected="filter_selected" ref="namefilterref"></NameFilter>
        <PagingBar :totalrows="totalrows" :perpage="perpage" @pagechange="pagechanged"></PagingBar>
        <div class="table-responsive">
            <table v-if="showtable" class="table table-striped table-sm mt-4 border rounded">
                <thead class="thead-light">
                    <th>{{ strings.firstnamelastname }}</th>
                    <th>{{ strings.idnumber }}</th>
                    <th>{{ strings.grade }}</th>
                    <th> </th>
                </thead>
                <tbody>
                    <tr v-for="user in pagedusers" :key="user.id">
                        <td>{{ user.displayname }}</td>
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
    import PagingBar from '@/components/PagingBar.vue';
    import { getstrings } from '@/js/getstrings.js';

    const PAGESIZE = 20;

    const props = defineProps({
        itemid: Number,
    });

    const users = ref([]);
    const pagedusers = ref([]);
    const strings = ref({});
    const totalrows = ref(0);
    const perpage = ref(PAGESIZE);
    const currentpage = ref(1);
    const usershidden = ref(false);
    const namefilterref = ref(null);

    let firstname = '';
    let lastname = '';

    /**
     * filter out paged users
     */
    function get_pagedusers() {
        const first = (currentpage.value - 1) * PAGESIZE;
        const last = first + PAGESIZE - 1;
        pagedusers.value = [];
        for (let i=first; i<=last; i++) {
            if (users.value[i] != undefined) {
                pagedusers.value.push(users.value[i]);
            }
        }
    }

    /**
     * Get filtered/paged data
     */
     function get_page_data(itemid, first, last) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;      
        
        window.console.log('GET PAGE DATA');

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
            usershidden.value = result['hidden'];
            users.value = JSON.parse(result['users']);
            totalrows.value = users.value.length;
            window.console.log(users.value);
            get_pagedusers();
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
        if (first == 'all') {
            first = '';
        }
        if (last == 'all') {
            last = '';
        }
        firstname = first;
        lastname = last;

        // Reset page
        currentpage.value = 1;
        get_page_data(props.itemid, first, last);
    }

    /**
     * Page selected on paging bar
     */
    function pagechanged(page) {
        currentpage.value = page;
        get_pagedusers();
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
        })
        .catch((error) => {
            window.console.log(error);
        });

        // Get the data for the table
        get_page_data(props.itemid, firstname, lastname);
    })
</script>
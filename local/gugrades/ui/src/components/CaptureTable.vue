<template>
    <div>
        <div class="border rounded p-2 py-4 mt-2">
            <ImportButton :itemid="itemid" :userids="userids" @imported="gradesimported"></ImportButton>
        </div>

        <NameFilter v-if="!usershidden" @selected="filter_selected" ref="namefilterref"></NameFilter>
        <PagingBar :totalrows="totalrows" :perpage="perpage" @pagechange="pagechanged"></PagingBar>

        <div class="table-responsive">
            <table v-if="showtable" class="table table-striped table-sm mt-4 border rounded">
                <thead class="thead-light">
                    <th v-if="!usershidden">{{ strings.userpicture }}</th>
                    <th>{{ strings.firstnamelastname }}</th>
                    <th>{{ strings.idnumber }}</th>
                    <th>{{ strings.grade }}</th>
                    <th> </th>
                </thead>
                <tbody>
                    <tr v-for="user in pagedusers" :key="user.id">
                        <td v-if="!usershidden">
                            <UserPicture :userid="parseInt(user.id)" :fullname="user.displayname"></UserPicture>
                        </td>
                        <td>{{ user.displayname }}</td>
                        <td>{{ user.idnumber }}</td>
                        <CaptureGrades :grades="user.grades"></CaptureGrades>
                        <td>
                            <AddGradeButton :itemid="itemid" :userid="parseInt(user.id)"></AddGradeButton>&nbsp;
                            <HistoryButton :userid="parseInt(user.id)" :itemid="itemid"></HistoryButton>
                        </td>
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
    import UserPicture from '@/components/UserPicture.vue';
    import CaptureGrades from '@/components/CaptureGrades.vue';
    import HistoryButton from '@/components/HistoryButton.vue';
    import ImportButton from '@/components/ImportButton.vue';
    import AddGradeButton from '@/components/AddGradeButton.vue';
    import { getstrings } from '@/js/getstrings.js';
    import { useToast } from "vue-toastification";

    const PAGESIZE = 20;

    const props = defineProps({
        itemid: Number,
    });

    const users = ref([]);
    const userids = ref([]);
    const pagedusers = ref([]);
    const strings = ref({});
    const totalrows = ref(0);
    const perpage = ref(PAGESIZE);
    const currentpage = ref(1);
    const usershidden = ref(false);
    const namefilterref = ref(null);

    const toast = useToast();

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
     * @param int itemid
     * @param char first
     * @param char last
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
            usershidden.value = result['hidden'];
            users.value = JSON.parse(result['users']);
            userids.value = users.value.map(u => u.id);
            totalrows.value = users.value.length;
            //window.console.log(userids);
            get_pagedusers();
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
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
     * @param int page
     */
    function pagechanged(page) {
        //window.console.log(namefilterref);
        if ('reset_filter' in namefilterref) {
            window.console.log('GOT TO PAGE CHANGE RESET');
            namefilterref.reset_filter();
        }
        currentpage.value = page;
        get_pagedusers();
    }

    /**
     * Import grades function is complete
     */
    function gradesimported() {

        // Get the data for the table
        get_page_data(props.itemid, firstname, lastname);

        // Done it
        toast.success("Import complete", {
        });
    }

    /**
     * Show table if there's anything to show
     */
    const showtable = computed(() => {
        return users.value.length != 0;
    });

    /**
     * Watch for displayed grade-item changing
     */
    watch(() => props.itemid, (itemid) => {
        get_page_data(itemid, firstname, lastname);
    })

    /**
     * Load strings (mostly for table) and get initial data for table.
     */
    onMounted(() => {

        // Get the moodle strings for this page
        const stringslist = [
            'addgrade',
            'awaitingcapture',
            'firstnamelastname',
            'idnumber',
            'nothingtodisplay',
            'grade',
            'importgrades',
            'userpicture',
        ];
        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });

        // Get the data for the table
        get_page_data(props.itemid, firstname, lastname);
    })
</script>
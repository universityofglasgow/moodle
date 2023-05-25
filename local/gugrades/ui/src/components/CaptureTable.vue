<template>
    <div>
        <div class="border rounded p-2 py-4 mt-2">
            <button class="btn btn-primary" @click="showimportmodal = true">Import grades</button>
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
                            <button type="button" class="btn btn-outline-primary btn-sm mr-1">{{ strings.addgrade }}</button>
                            <HistoryButton :userid="parseInt(user.id)" :itemid="itemid"></HistoryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2 v-if="!showtable">{{ strings.nothingtodisplay }}</h2>

        <Teleport to="body">
            <ModalForm :show="showimportmodal" @close="showimportmodal = false">
                <template #header>
                    <h4>{{ strings.importgrades }}</h4>
                </template>
                <template #body>
                    Form goes here
                    <p><button class="btn btn-primary" @click="importgrades">Import</button></p>
                </template>
            </ModalForm>
        </Teleport>
    </div>   
</template>

<script setup>
    import {ref, defineProps, computed, watch, onMounted} from '@vue/runtime-core';
    import NameFilter from '@/components/NameFilter.vue';
    import PagingBar from '@/components/PagingBar.vue';
    import ModalForm from '@/components/ModalForm.vue';
    import UserPicture from '@/components/UserPicture.vue';
    import CaptureGrades from '@/components/CaptureGrades.vue';
    import HistoryButton from '@/components/HistoryButton.vue';
    import { getstrings } from '@/js/getstrings.js';
    import { useNotification } from '@kyvg/vue3-notification';

    const PAGESIZE = 20;

    const props = defineProps({
        itemid: Number,
    });

    const { notify } = useNotification();

    const users = ref([]);
    const pagedusers = ref([]);
    const strings = ref({});
    const totalrows = ref(0);
    const perpage = ref(PAGESIZE);
    const currentpage = ref(1);
    const usershidden = ref(false);
    const namefilterref = ref(null);
    const showimportmodal = ref(false);

    let firstname = '';
    let lastname = '';
    let userids = [];

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
            userids = users.value.map(u => u.id);
            totalrows.value = users.value.length;
            //window.console.log(result['users']);
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
     * Import grades button clicked
     */
    function importgrades() {
        window.console.log('IMPORT GRADES');

        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;      
        
        fetchMany([{
            methodname: 'local_gugrades_import_grades_users',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userlist: userids.toString(),
            }
        }])[0]
        .then(() => {

            // Get the data for the table
            get_page_data(props.itemid, firstname, lastname);

            // Done it
            notify({
                title: 'Import complete',
                text: 'Data has been imported',
                position: 'bottom left',
            })
        })
        .catch((error) => {
            window.console.log(error);
        });

        showimportmodal.value = false;
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
        });

        // Get the data for the table
        get_page_data(props.itemid, firstname, lastname);
    })
</script>
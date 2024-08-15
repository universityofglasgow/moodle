<template>
    <div class="border rounded p-2 mt-2">
        <div class="col-12 col-lg-6">
            <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
            <GroupSelect v-if="level1category" @groupselected="groupselected"></GroupSelect>
        </div>
    </div>

    <div v-if="level1category" class="mt-2">

        <!-- display warnings -->
        <div>
            <small>
                <div v-for="warning in warnings" class="alert alert-warning alert-dismissible fade show mb-1" role="alert">
                    {{ warning.message }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </small>
        </div>

        <!-- Filter on initials -->
        <NameFilter @selected="filter_selected" ref="namefilterref"></NameFilter>

        <!-- Breadcrumb trail -->
        <div v-if="breadcrumb.length > 1" class="gug_breadcrumb border rounded my-3 p-2 text-white">
            <ul class="list-inline mb-0">
                <li v-for="(item, index) in breadcrumb" :key="item.id" class="list-inline-item">
                    <span v-if="index != 0"> > </span>
                    <a class="text-white" href="#" @click="expand_clicked(item.id)">{{ item.shortname }}</a>
                </li>
            </ul>
        </div>

        <div v-if="loading" class="d-flex justify-content-center">
            <VueSpinner size="50" color="#005c8a"></VueSpinner>
        </div>

        <EasyDataTable
            v-if="!loading"
            buttons-pagination
            alternating
            sort-by="displayname"
            sort-type="asc"
            table-class-name="aggregation-table"
            header-text-direction="center"
            :body-item-class-name="table_item_class"
            :items="users"
            :headers="headers"
        >

            <!-- additional information in header cells -->
            <template #header="header">
                <div v-if="header.value == 'back'">
                    <a class="text-white" href="#" @click="expand_clicked(backid)" data-toggle="tooltip" data-placement="bottom" :title="mstrings.goback">
                        <i class="fa fa-arrow-circle-left fa-xl" aria-hidden="true"></i>
                    </a>
                </div>
                <div v-else class="aggregation-header">
                    <div data-toggle="tooltip" :title="header.fullname" :data-original-title="header.fullname">

                        <div>
                            <!-- column title -->
                            <InfoButton v-if="header.gradeitemid" :itemid="header.gradeitemid" :text="header.text" size="lg" color="text-warning"></InfoButton>
                            <span v-else>{{ header.text }}</span>
                        </div>
                        <div v-if="!header.infocol">{{ header.weight }}%</div>
                        <div v-if="header.gradetype">{{ header.gradetype }} <span v-if="!header.isscale">({{ header.grademax }})</span></div>
                    </div>
                    <div class="py-1" v-if="header.strategy">
                        <i>{{ header.strategy }}</i>
                    </div>
                    <div v-if="header.categoryid">
                        <a href="#" @click="expand_clicked(header.categoryid)">
                            <span class="badge badge-light mt-2" >
                                <i class="fa fa-caret-left" aria-hidden="true"></i>
                                {{ mstrings.expand }}
                                <i class="fa fa-caret-right" aria-hidden="true"></i>
                            </span>
                        </a>
                    </div>
                    <div v-if="header.atype">
                        ({{ formattedatype }})
                    </div>
                </div>
            </template>

            <!-- User picture column -->
            <template #item-slotuserpicture="item">
                <a :href="item.profileurl">
                    <img :src="item.pictureurl" :alt="item.displayname" class="userpicture defaultuserpic" width="35" height="35"/>
                </a>
            </template>

            <!-- Resit required -->
            <template #item-resitrequired="item">
                <a href="#" @click="resit_clicked(item.id, !item.resitrequired)">
                    <span v-if="item.resitrequired" class="gug_pill badge badge-pill badge-success">{{ mstrings.yes }}</span>
                    <span v-else class="gug_pill badge badge-pill badge-secondary">{{ mstrings.no }}</span>
                </a>
            </template>

            <!-- Completion -->
            <template #item-completed="item">
                {{ item.completed }}%
            </template>

            <!-- Total -->
            <template #item-total="item">
                <span v-if="item.error">{{ item.error }}</span>
                <span v-else>{{ item.displaygrade }}</span>
            </template>

        </EasyDataTable>

        <!-- display debugging/timing information -->
        <div v-if="debug.length > 0" class="my-3 pt-2 rounded border text-monospace bg-secondary text-dark">
            <ul>
                <li v-for="line in debug">
                    {{ line.line }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
    import {ref, computed, inject} from '@vue/runtime-core';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import NameFilter from '@/components/NameFilter.vue';
    import GroupSelect from '@/components/GroupSelect.vue';
    import { useToast } from "vue-toastification";
    import { VueSpinner } from 'vue3-spinners';
    import InfoButton from '@/components/InfoButton.vue';

    const toast = useToast();

    const mstrings = inject('mstrings');

    const level1category = ref(0);
    const loading = ref(true);
    const categoryid = ref(0);
    const groupid = ref(0);
    const items = ref([]);
    const users = ref([]);
    const columns = ref([]);
    const categories = ref([]);
    const breadcrumb = ref([]);
    const backid = ref(0);
    const toplevel = ref(false);
    const completed = ref(0);
    const atype = ref('');
    const formattedatype = ref('');
    const warnings = ref([]);
    const strategy = ref('');
    const debug = ref([]);

    let firstname = '';
    let lastname = '';

    /**
     * Capture change to top level category dropdown
     * @param {*} level
     */
    function levelOneChange(level) {
        level1category.value = parseInt(level);
        categoryid.value = level1category.value;
        table_update();
    }

    /**
     * Capture change to group
     */
     function groupselected(gid) {
        groupid.value = Number(gid);
        table_update();
    }

    /**
     * Get class name for table items
     */
     function table_item_class(column) {
        if (column != 'displayname') {
            return 'text-center';
        }
    }

    /**
     * Resit required 'pill' clicked
     */
    function resit_clicked(userid, required) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_resit_required',
            args: {
                courseid: courseid,
                userid: userid,
                required: required,
            }
        }])[0]
        .then(() => {
            table_update();
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }

    /**
     * Add columns to user array
     */
    function process_users(users) {
        users.forEach(user => {
            user.fields.forEach(field => {
                user[field.fieldname] = field.display;
            })
        });

        return users;
    }

    /**
     * Show the correct string for the aggregation type (atype)
     */
    function get_formattedatype() {
        if (atype.value == 'A') {
            return 'Schedule A';
        } else if (atype.value == 'B') {
            return 'Schedule B';
        } else if (atype.value == 'P') {
            return mstrings.points;
        } else if (atype.value == 'C') {
            return mstrings.converted;
        } else if (atype.value == 'E') {
            return 'Error';
        } else {
            return '[[' + atype.value + ']]';
        }
    };

    /**
     * Create list of headers for EasyDataTable
     * (infocol = true, means that the column has no grade data)
     */
    const headers = computed(() => {
        let heads = [];

        // User identification.
        heads.push({text: mstrings.userpicture, value: "slotuserpicture", infocol: true});
        heads.push({text: mstrings.firstnamelastname, value: "displayname", sortable: true, infocol: true})
        heads.push({text: mstrings.idnumber, value: "idnumber", sortable: true, infocol: true});

        // 'Back' button column on everything but "top level"
        if (!toplevel.value) {
            heads.push({
                text: '??', // Dealt with by template
                value: "back", // Fake
            });
        }

        // Grade categories and items.
        columns.value.forEach(column => {
            heads.push({
                gradeitemid: column.gradeitemid,
                text: column.shortname,
                value: column.fieldname,
                weight: column.weight,
                fullname: column.fullname,
                categoryid: column.categoryid,
                gradetype: column.gradetype,
                grademax: column.grademax,
                isscale: column.isscale,
                strategy: column.strategy,
            });
        });

        // Items that only display on "top level" page.
        if (toplevel.value) {

            // Resit required?
            heads.push({
                text: mstrings.resitrequired,
                value: "resitrequired",
                infocol: true,
            });

            // Completion %age
            heads.push({
                text: mstrings.completed,
                value: "completed",
                infocol: true,
            });

            // Total.
            heads.push({
                text: mstrings.coursetotal,
                value: "total",
                infocol: true,
                strategy: strategy.value,
            });
        } else {

            // Sub-category total
            heads.push({
                text: mstrings.subcattotal,
                atype: atype.value,
                value: "total",
                infocol: true,
                strategy: strategy.value,
            });
        }
        return heads;
    });

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
        //currentpage.value = 1;
        table_update();
    }

    /**
     * Update table (when something changes)
     */
    function table_update() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // If we happen to end up here with no categoryid then just bail out.
        if (!Number.isInteger(categoryid.value)) {
            return;
        }

        loading.value = true;

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_page',
            args: {
                courseid: courseid,
                gradecategoryid: categoryid.value,
                firstname: firstname,
                lastname: lastname,
                groupid: groupid.value,
                aggregate: false,
            }
        }])[0]
        .then((result) => {
            //items.value = result.items;
            //categories.value = result.categories;
            users.value = result.users;
            warnings.value = result.warnings;
            columns.value = result.columns;
            breadcrumb.value = result.breadcrumb;
            toplevel.value = result.toplevel;
            atype.value = result.atype;
            strategy.value = result.strategy;
            debug.value = result.debug;

            // Get id of one back from breadcrumb
            backid.value = breadcrumb.value.slice(-2)[0].id;

            users.value = process_users(users.value);
            formattedatype.value = get_formattedatype();
            loading.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        });
    }

    /**
     * Expand button was clicked in header
     */
    function expand_clicked(id) {
        categoryid.value = id;
        table_update();
    }
</script>

<style>
    .aggregation-table {
        --easy-table-header-font-size: 14px;
        --easy-table-header-height: 50px;
        --easy-table-header-font-color: white;
        --easy-table-header-background-color: #4F5961;

        --easy-table-header-item-padding: 10px 15px;
    }

    .aggregation-header {
        display: flex;
        flex-direction: column;
        text-align: center;
    }

    .gug_breadcrumb {
        background-color: #005c8a;
    }

    .gug_pill {
        font-size: 125%;
    }

    .vue3-easy-data-table__main table {
        border-radius: 25px;
    }
</style>
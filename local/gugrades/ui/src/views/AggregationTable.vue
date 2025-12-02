<template>
    <DebugDisplay :debug="serverdebug"></DebugDisplay>

    <div class="border rounded p-2 mt-2">
        <div class="col-12 col-lg-6">
            <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
            <GroupSelect v-if="level1category" @groupselected="groupselected"></GroupSelect>
        </div>

        <!-- display warnings -->
        <div class="mt-2" v-if="level1category">
            <small>
                <div v-for="warning in warnings" class="alert alert-warning alert-dismissible fade show mb-1" role="alert">
                    {{ warning.message }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </small>
        </div>

        <!-- Buttons line -->
        <AggregationButtons
            v-if="level1category && aggregationsupported"
            :categoryid="categoryid"
            :gradeitemid="gradeitemid"
            :groupid="groupid"
            :toplevel="toplevel"
            :atype="atype"
            :allowconversion="allowconversion"
            :allowrelease="allowrelease"
            :released="released"
            :staffuserid="staffuserid"
            :caneditgrades="caneditgrades"
            @refreshtable="table_update"
            ></AggregationButtons>
    </div>

    <!-- Aggregation is not possible -->
    <div v-if="!aggregationsupported" class="alert alert-danger my-5">
        {{  mstrings.aggregationnotsupported }}
    </div>

    <div v-if="level1category && aggregationsupported" class="mt-2">

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

        <!-- Please wait spinner -->
        <PleaseWait v-if="loading"></PleaseWait>

        <EasyDataTable
            v-if="!loading"
            alternating
            sort-by="displayname"
            sort-type="asc"
            table-class-name="aggregation-table"
            header-text-direction="center"
            :body-item-class-name="table_item_class"
            :header-item-class-name="header_item_class"
            :items="users"
            :headers="headers"
            :filter-options="table_filter"
            ref="dataTable"
            hide-footer
        >

            <!-- additional information in header cells -->
            <template #header="header">
                <div v-if="header.value == 'back'">
                    <a class="text-white" href="#" @click="expand_clicked(backid)">
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
                        <div v-if="!header.infocol && showweights">{{ header.weight }}%</div>
                        <div v-if="header.gradetype">{{ header.gradetype }} <span v-if="!header.isscale">({{ header.grademax }})</span></div>
                        <div v-if="header.resititem" class="badge badge-pill badge-info">{{ mstrings.resitselected }}</div>
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

            <!-- all items (yes this is complicated) -->
            <!-- point is to iterate over field names to maniuplate data in individual field items -->
            <template v-for="header in headers" v-slot:[header.slot]="item">

                <!-- strikethrough if data is dropped -->
                <!-- bold if admin -->
                <!-- there HAS to be an easier way -->
                <span :class="itemclasses(item[header.value])">
                    <s v-if="item[header.value].dropped">
                        <b v-if="item[header.value].isadmin">{{ item[header.value].data }}</b>
                        <span v-else>{{ item[header.value].data }}</span>
                    </s>
                    <span v-else>
                        <b v-if="item[header.value].isadmin">{{ item[header.value].data }}</b>
                        <span v-else>{{ item[header.value].data }}</span>
                    </span>
                </span>

                <!-- add/override grade -->
                <OverrideGrade
                    v-if="item[header.value].available"
                    :itemid = "header.gradeitemid"
                    :categoryid = "header.categoryid"
                    :userid = "item.id"
                    :gradehidden = "item[header.value].hidden"
                    :overridden = "item[header.value].overridden"
                    :itemname = "header.fullname"
                    :name = "item.displayname"
                    :showweights = "header.showweights"
                    :released = "header.released"
                    :caneditgrades = "caneditgrades"
                    @gradeadded = "grade_changed(item.id)"
                ></OverrideGrade>
            </template>

            <!-- User picture column -->
            <template #item-slotuserpicture="item">
                <a :href="item.profileurl">
                    <img :src="item.pictureurl" :alt="item.displayname" class="userpicture defaultuserpic" width="35" height="35"/>
                </a>
            </template>

            <!-- Resit required -->
            <template #item-resitrequired="item">
                <a href="#" v-if="caneditgrades" @click="resit_clicked(item.id, !item.resitrequired)">
                    <span v-if="item.resitrequired" class="gug_pill badge badge-pill badge-success">{{ mstrings.yes }}</span>
                    <span v-else class="gug_pill badge badge-pill badge-secondary">{{ mstrings.no }}</span>
                </a>
                <span v-if="!caneditgrades">
                    <span v-if="item.resitrequired" class="gug_pill badge badge-pill badge-success">{{ mstrings.yes }}</span>
                    <span v-else class="gug_pill badge badge-pill badge-secondary">{{ mstrings.no }}</span>    
                </span>
            </template>

            <!-- Completion -->
            <template #item-completed="item">
                {{ item.completed }}%
            </template>

            <!-- Releasegrade -->
            <template #item-releasegrade="item">
                <div v-if="!toplevel">
                    {{ item.releasegrade }}
                    <span v-if="item.mismatch">
                        <br />
                        <span class="badge badge-danger mt-1">MISMATCH</span>
                    </span>
                </div>
            </template>

            <!-- Total -->
            <template #item-total="item">
                <div class="d-flex justify-content-center align-items-center">
                    <div>
                        <span v-if="item.error">{{ item.error }}</span>
                        <span :class="itemclasses(item)" v-else>{{ item.displaygrade }}</span>
                        <span v-if="item.alteredweight">
                            <br />
                            <span class="badge badge-warning mt-1">ALTERED</span>
                         </span>
                    </div>
                    <div>
                        <!-- add/override for total grade -->
                        <OverrideGrade
                            :toplevel="toplevel"
                            :itemid = "gradeitemid"
                            :categoryid = "categoryid"
                            :userid = "item.id"
                            :gradehidden = "false"
                            :overridden = "item.overridden"
                            :itemname = "item.itemname"
                            :name = "item.displayname"
                            :showweights = "showweights"
                            :released = "false"
                            @gradeadded = "grade_changed(item.id)"
                        ></OverrideGrade>
                    </div>
                </div>
            </template>
        </EasyDataTable>

        <!-- Implementation of our own accessible footer. -->
        <CustomPagination 
            v-if="!loading"
            v-bind="props"
        />

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
    import {ref, computed, inject, onMounted} from '@vue/runtime-core';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import NameFilter from '@/components/NameFilter.vue';
    import GroupSelect from '@/components/GroupSelect.vue';
    import { useToast } from "vue-toastification";
    import InfoButton from '@/components/InfoButton.vue';
    import PleaseWait from '@/components/PleaseWait.vue';
    import AggregationButtons from '@/components/Aggregation/AggregationButtons.vue';
    import OverrideGrade from '@/components/Aggregation/OverrideGrade.vue';
    import DismissableAlert from '@/components/DismissableAlert.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import CustomPagination from '@/components/CustomPagination.vue';

    const toast = useToast();
    const mstrings = inject('mstrings');
    const level1category = ref(0);
    const loading = ref(true);
    const aggregationsupported = ref(true);
    const categoryid = ref(0);
    const gradeitemid = ref(0);
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
    const conversion = ref('');
    const allowconversion = ref(false);
    const serverdebug = ref({});
    const allowrelease = ref(false);
    const released = ref(false);
    const showweights = ref(false);
    const excludeempty = ref(false);
    const firstname = ref('');
    const lastname = ref('');
    const staffuserid = ref(0);
    const caneditgrades = ref(false);
    // pagination related.
    const dataTable = ref();
    const props = {
        dataTable: dataTable,
        loading: loading
    }

    /**
     * onMounted, get write grades capability
     */
    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_has_capability',
            args: {
                courseid: courseid,
                capability: 'local/gugrades:editgrades'
            }
        }])[0]
        .then((result) => {
            caneditgrades.value = result.hascapability;
        })
        .catch((error) => {
            window.console.log(error);
            debug.value = error;
        });
    })

    /**
     * Table name filter
     */
     const table_filter = computed(() => {
        const options = [];

        if (firstname.value != '') {
            options.push({
                field: 'firstinitial',
                comparison: '=',
                criteria: firstname.value,
            });
        }

        if (lastname.value != '') {
            options.push({
                field: 'lastinitial',
                comparison: '=',
                criteria: lastname.value,
            });
        }

        return options;
    });

    /**
     * Work out border classes for item
     */
    function itemclasses(item) {
        if (item.overridden) {
            return ['border', 'border-danger', 'rounded', 'p-1']
        }
        if (item.hidden) {
            return ['border', 'border-warning', 'rounded', 'p-1']
        }
        return [];
    }

    /**
     * Capture change to top level category dropdown
     * @param {*} level
     */
    function levelOneChange(level) {
        level1category.value = parseInt(level);
        categoryid.value = level1category.value;
        if (categoryid.value) {
            table_update();
        }
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

        // Hide name initial columns
        if ((column == 'firstinitial') || (column == 'lastinitial')) {
            return 'd-none';
        }
        if (column != 'displayname') {
            return 'text-center';
        }
    }

    /**
     * Get class name for header items
     */
     function header_item_class(header) {
        if ((header.value == 'firstinitial') || (header.value == 'lastinitial')) {
            return 'd-none';
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
            user_update(userid);

        })
        .catch((error) => {
            window.console.error(error);
            serverdebug.value = error;
        });
    }

    /**
     * Grade has been modified for user
     */
    function grade_changed(userid) {
        user_update(userid);
    }

    /**
     * Add columns to user array
     */
    function process_users(users) {
        users.forEach(user => {
            user.fields.forEach(field => {
                user[field.fieldname] = {
                    userid: user.id,
                    data: field.display,
                    dropped: field.dropped,
                    isadmin: field.isadmin,
                    hidden: field.hidden,
                    overridden: field.overridden,
                    available: field.available,
                };
            })
        });

        return users;
    }

    /**
     * Process columns for single user
     */
    function process_user(user) {
        user.fields.forEach(field => {
                user[field.fieldname] = {
                    userid: user.id,
                    data: field.display,
                    dropped: field.dropped,
                    isadmin: field.isadmin,
                    hidden: field.hidden,
                    overridden: field.overridden,
                    available: field.available,
                };
        });

        return user;
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
        heads.push({text: 'firstinitial', value: 'firstinitial'});
        heads.push({text: 'lastinitial', value: 'firstinitial'});
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
                slot: 'item-' + column.fieldname,
                weight: column.weight,
                fullname: column.fullname,
                categoryid: column.categoryid,
                gradetype: column.gradetype,
                grademax: column.grademax,
                isscale: column.isscale,
                strategy: column.strategy,
                showweights: column.showweights,
                released: column.released,
                resititem: column.isresitgradeitem,
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
                excludeempty: excludeempty.value,
            });
        } else {

            // Build up strategy text including conversion applied
            let headerstrategy = strategy.value;
            if (conversion.value) {
                headerstrategy = headerstrategy + ' by ' + conversion.value;
            }

            // Sub-category total
            heads.push({
                text: mstrings.subcattotal,
                atype: atype.value,
                value: "total",
                infocol: true,
                strategy: headerstrategy,
            });
        }

        // Released grade (not shown for grand total)
        if (released.value && !toplevel.value) {
            heads.push({
                text: mstrings.released,
                value: 'releasegrade',
                infocol: true,
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
        firstname.value = first;
        lastname.value = last;

        // Reset page
        //currentpage.value = 1;
        //table_update();
    }

    /**
     * Update single user (when something changes)
     */
    function user_update(userid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_aggregation_user',
            args: {
                courseid: courseid,
                gradecategoryid: categoryid.value,
                userid: userid,
            }
        }])[0]
        .then((result) => {
            const found = users.value.findIndex((user) => {
                return user.id == userid;
            });
            if (found > -1) {
                users.value[found] = process_user(result);
            }
        })
        .catch((error) => {
            window.console.error(error);
            serverdebug.value = error;
        });
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
                firstname: '',
                lastname: '',
                groupid: groupid.value,
                aggregate: false,
            }
        }])[0]
        .then((result) => {
            //items.value = result.items;
            //categories.value = result.categories;
            aggregationsupported.value = result.aggregationsupported;
            users.value = result.users;
            warnings.value = result.warnings;
            columns.value = result.columns;
            breadcrumb.value = result.breadcrumb;
            toplevel.value = result.toplevel;
            atype.value = result.atype;
            gradeitemid.value = result.gradeitemid;
            strategy.value = result.strategy;
            debug.value = result.debug;
            conversion.value = result.conversion;
            allowconversion.value = result.allowconversion;
            allowrelease.value = result.allowrelease;
            released.value = result.released;
            showweights.value = result.showweights;
            excludeempty.value = result.excludeempty;
            staffuserid.value = result.staffuserid;

            if (aggregationsupported.value) {

                // Get id of one back from breadcrumb
                backid.value = breadcrumb.value.slice(-2)[0].id;

                users.value = process_users(users.value);
                formattedatype.value = get_formattedatype();
            }

            loading.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            serverdebug.value = error;
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

    .border-lg {
        border-width: thick !important;
    }

    .buttons-pagination .item.button.active {
        color: black !important;
    }
</style>
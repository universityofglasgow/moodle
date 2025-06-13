<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div>
        <div class="border rounded p-2 mt-2">
            <div class="col-12 mb-2">
                <button class="badge badge-primary" @click="selectcollapse">
                    <span v-if="collapsed"><i class="fa fa-arrow-right"></i> {{ mstrings.showcategories }}</span>
                    <span v-else><i class="fa fa-arrow-down"></i> {{ mstrings.hidecategories }}</span>
                </button>
            </div>

            <div id="captureselect" :class="collapseclasses">
                <CaptureSelect @selecteditemid="selecteditemid"></CaptureSelect>

                <div v-if="itemid">
                    <CaptureAlerts
                        :gradesupported="gradesupported"
                        :aggregationsupported="aggregationsupported"
                        :gradehidden="gradehidden"
                        :gradelocked="gradelocked"
                        :noids="!showcsvimport"
                        >
                    </CaptureAlerts>

                    <CaptureButtons
                        v-if="gradesupported"
                        :itemid="itemid"
                        :groupid="groupid"
                        :userids="userids"
                        :users="users"
                        :itemtype="itemtype"
                        :itemname="itemname"
                        :usershidden="usershidden"
                        :gradesimported="gradesimported"
                        :showconversion="showconversion"
                        :converted="converted"
                        :released="released"
                        :revealnames="revealnames"
                        :showcsvimport="showcsvimport"
                        :staffuserid="staffuserid"
                        @refreshtable="refresh"
                        @viewfullnames="viewfullnames"
                        @editcolumn="editcog_clicked"
                        >
                    </CaptureButtons>
                </div>
            </div>
        </div>

        <div v-if="itemid && gradesupported" class="mt-2">
            <NameFilter v-if="!usershidden" @selected="filter_selected" ref="namefilterref"></NameFilter>

            <!-- Please wait spinner -->
            <PleaseWait v-if="!loaded"></PleaseWait>

            <div v-if="showtable && loaded">

                <!-- button for saving cell edits -->
                <div class="pb-1 clearfix" v-if="ineditcellmode">
                    <button class="btn btn-warning float-right mr-1" @click="edit_cell_cancelled">{{ mstrings.cancel }}</button>
                    <button class="btn btn-primary float-right mr-1" @click="edit_cell_saved">{{ mstrings.save }}</button>
                </div>

                <!-- Note. The array 'users' contains the lines of data. One record for each user -->
                <EasyDataTable
                    alternating
                    sort-by="displayname"
                    sort-type="asc"
                    table-class-name="capture-table"
                    :items="users"
                    :headers="headers"
                    header-text-direction="center"
                    :body-row-class-name="table_row_class"
                    :body-item-class-name="table_item_class"
                    :header-item-class-name="header_item_class"
                    :filter-options="table_filter"
                    @xxupdate-page-items="pagination_clicked()"
                    ref="dataTable"
                    hide-footer
                    >

                    <!-- add header text and edit cog next to cell if required -->
                    <!-- component needs to return info about which column (which reason/gradetype has been selected)-->
                    <template #header="header">
                        {{ header.text }}
                        <CaptureColumnEditCog v-if="header.editable  && !ineditcellmode" :header="header" :itemid="itemid" @editcolumn="editcog_clicked"></CaptureColumnEditCog>
                    </template>

                    <!-- User picture column -->
                    <template #item-slotuserpicture="item">
                        <a :href="item.profileurl">
                            <img :src="item.pictureurl" :alt="item.displayname" class="userpicture defaultuserpic" width="35" height="35"/>
                        </a>
                    </template>

                    <!-- Provisional column -->
                    <template v-slot:[provisionalslot]="item">
                        <div v-if="item[provisionalid]">
                            <span v-if="item.gradehidden && !item.gradebookhidden" class="border border-lg border-warning rounded p-1">{{ item[provisionalid] }}</span>
                            <span v-if="item.gradebookhidden" class="border border-lg border-success rounded p-1">{{ item[provisionalid] }}</span>
                            <span v-if="!item.gradebookhidden && !item.gradehidden">{{ item[provisionalid] }}</span>
                        </div>
                    </template>


                    <!-- switch to input for bulk editing (if selected) -->
                    <template v-slot:[editcolumnslot]="item">
                        <EditCaptureCell
                            :item="item"
                            :column="editcolumn"
                            :columnid="editcolumnid"
                            :other="editother"
                            :notes="editnotes"
                            :gradeitemid="itemid"
                            :gradetype="editgradetype"
                            :usescale="editusescale"
                            :scalemenu="editscalemenu"
                            :adminmenu="editadminmenu"
                            :grademax="editgrademax"
                            :cancelled="editcancelled"
                            @gradewritten = "edit_grade_written()"
                            @gradecancel = "edit_grade_written()"
                             >
                        </EditCaptureCell>
                    </template>

                    <!-- dropdown in the final column -->
                    <template #item-actions="item">
                        <CaptureMenu
                            v-if="!ineditcellmode"
                            :item="item"
                            :itemid="itemid"
                            :userid="parseInt(item.id)"
                            :name="item.displayname"
                            :itemname="itemname"
                            :gradesimported="gradesimported"
                            :awaitingcapture="item.awaitingcapture"
                            :gradehidden="item.gradehidden"
                            :converted="converted"
                            @gradeadded = "get_user_data(item.id)"
                            >
                        </CaptureMenu>
                    </template>

                    <!-- show warning if grades do not agree -->
                    <template #item-alert="item">
                        <div class="capture-warning">
                            <div v-if="item.alert" class="badge badge-pill badge-danger mb-1 mr-1">{{ mstrings.discrepancy }}</div>
                            <div v-if="item.gradebookhidden" class="badge badge-pill badge-success mb-1 mr-1">{{ mstrings.hiddengradebook }}</div>
                            <div v-if="item.gradehidden" class="badge badge-pill badge-warning mb-1">{{ mstrings.hiddenmygrades }}</div>
                        </div>
                    </template>

                    <!-- Override pagination if bulk editing -->
                    <template #pagination v-if="ineditcellmode">
                        {{ mstrings.pleasesavefirst }}
                    </template>
                </EasyDataTable>

                <!-- Implementation of our own accessible footer. -->
                <CustomPagination 
                    v-if="loaded"
                    v-bind="props"
                />

                <!-- button for saving cell edits -->
                <div class="pb-1 clearfix mt-2" v-if="ineditcellmode">
                    <button class="btn btn-warning float-right mr-1" @click="edit_cell_cancelled">{{ mstrings.cancel }}</button>
                    <button class="btn btn-primary float-right mr-1" @click="edit_cell_saved">{{ mstrings.save }}</button>
                </div>
            </div>

            <h2 v-if="!showtable">{{ mstrings.nothingtodisplay }}</h2>
        </div>
    </div>
</template>

<script setup>
    import {ref, computed, inject, watch, defineEmits} from '@vue/runtime-core';
    import NameFilter from '@/components/NameFilter.vue';
    import CaptureSelect from '@/components/CaptureSelect.vue';
    import CaptureMenu from '@/components/CaptureMenu.vue';
    import { useToast } from "vue-toastification";
    import CaptureButtons from '@/components/Capture/CaptureButtons.vue';
    import CaptureAlerts from '@/components/CaptureAlerts.vue';
    import CaptureColumnEditCog from '@/components/CaptureColumnEditCog.vue';
    import EditCaptureCell from '@/components/Capture/EditCaptureCell.vue';
    import { useWindowScroll, watchDebounced } from '@vueuse/core';
    import PleaseWait from '@/components/PleaseWait.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import CustomPagination from '@/components/CustomPagination.vue';

    const users = ref([]);
    const userids = ref([]);
    const itemid = ref(0);
    const groupid = ref(0);
    const mstrings = inject('mstrings');
    const totalrows = ref(0);
    const currentpage = ref(1);
    const usershidden = ref(false);
    const namefilterref = ref(null);
    const itemtype = ref('');
    const itemname = ref('');
    const gradesupported = ref(true);
    const aggregationsupported = ref(true);
    const gradesimported = ref(false);
    const gradehidden = ref(false);
    const gradelocked = ref(false);
    const converted = ref(false);
    const released = ref(false);
    const columns = ref([]);
    const loaded = ref(false);
    const showalert = ref(false);
    const revealnames = ref(false);
    const collapsed = ref(false);
    const editcolumn = ref('');
    const editcolumnslot = ref('');
    const editusescale = ref(false);
    const editscalemenu = ref([]);
    const editadminmenu = ref([]);
    const editgradetype = ref('');
    const editgrademax = ref(0);
    const editgradecount = ref(0);
    const editcolumnid = ref(0);
    const editother = ref('');
    const editnotes = ref('');
    const editcancelled = ref(false);
    const showconversion = ref(false);
    const provisionalslot = ref('');
    const provisionalid = ref('');
    const showcsvimport = ref(true);
    const debug = ref({});
    const firstname = ref('');
    const lastname = ref('');
    const staffuserid = ref(0);
    const collapseclasses = ref(['collapse', 'show']);
    const toast = useToast();
    const emit = defineEmits(['refreshlogo']);
    // pagination related.
    const dataTable = ref();
    const props = {
        dataTable: dataTable,
    }

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
     * A watch for the itemid changing
     * Lots of stuff gets reset if the itemid is changed.
     */
    watch(itemid, () => {
        currentpage.value = 1;
        revealnames.value = false;
        editcolumn.value = '';
        editcolumnslot.value = '';
    });

    /**
     * Reset the page
     */
    function reset_page() {
        usershidden.value = false;
        users.value = [];
        itemtype.value = '';
        itemname.value = '';
        gradesupported.value = true;
        gradesimported.value = false;
        gradehidden.value = false;
        gradelocked.value = false;
        columns.value = [];
        userids.value = [];
        totalrows.value = 0;
        showconversion.value = false;
        converted.value = false;
        released.value = false;
        loaded.value = false;
    }

    /**
     * Get class name for table row depending on criteria
     * Used to show hidden rows
     */
    function table_row_class(item) {
        return 'non-hidden-row'
        if (item.gradehidden) {
            return 'hidden-row';
        } else if (item.gradebookhidden) {
            return 'gradebookhidden-row';
        } else {
            return 'non-hidden-row';
        }
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
     * Collapse selection area
     */
    function selectcollapse() {

        if (collapsed.value) {
            collapseclasses.value = ['collapse', 'show'];
        } else {
            collapseclasses.value = ['collapse'];
        }
        collapsed.value = !collapsed.value;
    }

    /**
     * New itemid and/or groupid has been selected
     * If itemid = 0, then reset the table
     */
    function selecteditemid(itemgroup) {
        itemid.value = itemgroup.itemid;
        groupid.value = itemgroup.groupid;

        if (itemid.value == 0) {
            reset_page();
        } else {
            reload_page();
        }
    }

    /**
     * Column editcog has been clicked
     */
     function editcog_clicked(cellform) {

        // Unpack data
        const columnname = cellform.columnname;

        // Note: this is the EasyDataTable slot name for the column.
        editcolumnslot.value = 'item-' + columnname;
        editcolumn.value = columnname;
        editusescale.value = cellform.usescale;
        editscalemenu.value = cellform.scalemenu;
        editadminmenu.value = cellform.adminmenu;
        editgradetype.value = cellform.gradetype;
        editgrademax.value = cellform.grademax;
        editcolumnid.value = cellform.columnid;
        editother.value = cellform.other;
        editnotes.value = cellform.notes;
        editcancelled.value = false;
        reload_page();
    }

    /**
     * In edit mode, the save button is clicked
     * The cells save themselves using a hook on before close.
     */
    function edit_cell_saved() {
        editcolumn.value = '';
        editcolumnslot.value = '';
    }

    /**
     * In edit mode, the cancel button is clicked
     * Set editcancelled to true and pass as prop to edit cells
     * so it knows not to save.
     */
    function edit_cell_cancelled() {
        editcancelled.value = true;
    }

    /**
     * A cell has declared that it has been written (or cancelled)
     * (We're probably getting lots of these)
     * Just count them and we'll watch/debounce the count to update the table
     */
    function edit_grade_written() {
        editgradecount.value++;
    }

    /**
     * See above - watching edit cell written count in order to
     * upgrade the main table
     */
     watchDebounced(
        editgradecount,
        () => {

            // Duplicated for cancel
            editcolumn.value = '';
            editcolumnslot.value = '';

            reload_page();
        },
        { debounce: 500, maxWait: 1000 },
    );

    /**
     * Change pagination
     */
    function pagination_clicked() {

        // If the multiple update is open, we'll close it
        edit_cell_saved();
    }

    /**
     * Are we in "edit a cell" mode?
     * Stuff doesn't appear, if so, and 'Save' button appears.
     */
    const ineditcellmode = computed(() => {
        return editcolumn.value != '';
    });

    /**
     * Get headers for table
     * These also define what data is displayed.
     */
    const headers = computed(() => {
        let heads = [];
        if (!usershidden.value) {
            heads.push({text: 'firstinitial', value: 'firstinitial'}),
            heads.push({text: 'lastinitial', value: 'firstinitial'}),
            heads.push({text: mstrings.userpicture, value: "slotuserpicture"});
            heads.push({text: mstrings.firstnamelastname, value: "displayname", sortable: true})
        } else {
            heads.push({text: mstrings.participant, value: "displayname", sortable: true});
        }
        heads.push({text: mstrings.idnumber, value: "idnumber", sortable: true});
        if (showalert.value) {
            heads.push({text: mstrings.warnings, value: "alert"});
        }

        // Add the grades columns
        columns.value.forEach(column => {

            // grab the value of the provisional column
            // We'll use it to style the column in the table.
            if (column.gradetype == 'PROVISIONAL') {
                provisionalslot.value = 'item-GRADE' + column.id;
                provisionalid.value = 'GRADE' + column.id;
            }

            // Make sure that the value is a string
            heads.push({
                text: column.description,
                value: 'GRADE' + column.id,
                gradetype: column.gradetype,
                editable: column.editable,
                columnid: column.id,
                other: column.other,
            });
        });

        // Space for the buttons column
        heads.push({text: mstrings.actions, value: "actions"});

        return heads;
    });

    /**
     * Handle viewfullnames
     * @param bool toggleview
     */
    function viewfullnames(toggleview) {
        revealnames.value = toggleview;
        reload_page();
    }

    /**
     * Add the column/grade data for individual user
     *
     */
    function add_user_grades(user, columns) {
        let grade = {};

        // Only show alert/discrepancy column if there are any
        if (user.alert || user.gradebookhidden || user.gradehidden) {
            showalert.value = true;
        }

        // Allow import if there are no grades for this user.
        user.awaitingcapture = true;
        columns.forEach(column => {
            const columnname = 'GRADE' + column.id;
            grade = user.grades.find((element) => {
                return (element.columnid == column.id);
            });
            if (grade) {
                user.awaitingcapture = false;
                user[columnname] = grade.displaygrade;
            } else if (column.gradetype == 'FIRST') {
                user[columnname] = mstrings.awaitingcapture;
            } else {
                user[columnname] = '';
            }

            // Is this column in 'editing mode'?
            // If so, we add the 'editcolumn' ta (true) to each cell in that column
            // The table slot can then pick it up and display an edit box
            // Similarly the reason/gradetype stuff
            user.editcolumn = (columnname == editcolumn.value);
            user.reason = column.gradetype;
            user.other = column.other;
            user.gradeitemid = column.gradeitemid;
        });

        return user;
    }

    /**
     * Add grade columns into 'users' data so the table component can display them
     * @param users
     * @param columns
     * @return array
     */
    function add_grades(users, columns) {

        showalert.value = false;
        users.forEach(user => {

            add_user_grades(user, columns);
        });

        return users;
    }

    /**
     * Helper function to reload the page
     * (We have to do this in lots of places)
     */
    function reload_page() {
        get_page_data(itemid.value, groupid.value);
    }

    /**
     * Get filtered/paged data
     * @param int itemid
     * @param char first
     * @param char last
     * @param int gid (group id)
     */
     function get_page_data(itemid, gid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        loaded.value = false;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_page',
            args: {
                courseid: courseid,
                gradeitemid: itemid,
                firstname: '',
                lastname: '',
                groupid: gid,
                viewfullnames: revealnames.value,
            }
        }])[0]
        .then((result) => {
            usershidden.value = result.hidden;
            users.value = result.users;
            itemtype.value = result.itemtype;
            itemname.value = result.itemname;
            gradesupported.value = result.gradesupported;
            aggregationsupported.value = result.aggregationsupported;
            gradesimported.value = result.gradesimported;
            gradehidden.value = result.gradehidden;
            gradelocked.value = result.gradelocked;
            columns.value = result.columns;
            userids.value = users.value.map(u => u.id);
            totalrows.value = users.value.length;
            showconversion.value = result.showconversion;
            converted.value = result.converted;
            released.value = result.released;
            showcsvimport.value = result.showcsvimport;
            staffuserid.value = result.staffuserid;

            users.value = add_grades(users.value, columns.value);

            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Work out if user data contains any additional columns
     * If a new column has been added then
     * Return true if missing columns
     */
    function missing_columns(usergrades) {

        // Flag a missing gradetype inside the callback
        let missing = false;

        usergrades.forEach((grade) => {
            const gradetype = grade.gradetype;
            const found = columns.value.find((column) => {
                return column.gradetype == gradetype;
            });

            // found returns undefined if not found. Only need one to be not found
            if (found === undefined) {
                missing = true;
            }
        });

        return missing;
    }

    /**
     * Get the data for an individual user
     * (If grade added and so on)
     */
    function get_user_data(userid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_user',
            args: {
                courseid: courseid,
                gradeitemid: itemid.value,
                userid: userid,
                viewfullnames: revealnames.value,
            }
        }])[0]
        .then((result) => {
            const updateduser = add_user_grades(result, columns.value);

            // If this seems to have added more columns then do a page reload.
            if (missing_columns(updateduser.grades)) {
                reload_page();
            } else {
                const found = users.value.findIndex((user) => {
                    return user.id == updateduser.id;
                });
                if (found > -1) {
                    users.value[found] = result;
                }
            }
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
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
        firstname.value = first;
        lastname.value = last;

        // Reset page
        //currentpage.value = 1;
        //get_page_data(itemid.value, groupid.value);
    }

    /**
     * Refresh the data table
     */
    function refresh() {
        get_page_data(itemid.value, groupid.value);
        emit('refreshlogo');
    }

    /**
     * Show table if there's anything to show
     */
    const showtable = computed(() => {
        return users.value.length != 0;
    });

</script>

<style>
    .hidden-row td {
        background-color: #ffff66  !important;
        overflow: visible;
    }

    .gradebookhidden-row td {
        background-color: #fabbec!important;
        overflow: visible;
    }

    .non-hidden-row td {
        overflow: visible;
    }

    .capture-table {
        --easy-table-header-font-size: 14px;
        --easy-table-header-height: 50px;
        --easy-table-header-font-color: #ffffff;
        --easy-table-header-background-color: #005c8a;

        --easy-table-header-item-padding: 10px 15px;
    }

    .border-lg {
        border-width: thick !important;
    }

    .capture-warning {
        font-size: 125%;
    }

    .buttons-pagination .item.button.active {
        color: black !important;
    }
</style>
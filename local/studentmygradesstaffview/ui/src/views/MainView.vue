<template>
    <DebugDisplay :debug="debug"></DebugDisplay>
    <div class="border rounded p-2 mt-2">
        <div class="collapse show">
            <StudentSelect @selectmenuchange="selectmenuchange"></StudentSelect>
        </div>
    </div>

    <div>
        <AssessmentsOverview></AssessmentsOverview>
    </div>

</template>

<script setup>
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import StudentSelect from '@/components/StudentSelect.vue';

    const toast = useToast();

    /**
     * New studentid has been selected.
     * If studentid = 0, then reset the page
     */
     function selectmenuchange(studentid) {
        studentid.value = studentid;

        if (studentid.value == 0) {
            reset_page();
        } else {
            reload_page();
        }
    }

    /**
     * Reset the page
     */
     function reset_page() {
        loaded.value = false;
    }

    /**
     * Helper function to reload the page
     * (We have to do this in lots of places)
     */
     function reload_page() {
        get_page_data(studentid.value);
    }

    /**
     * Get filtered/paged data
     * @param int studentid
     */
     function get_page_data(studentid) {
        const SMGSV = window.SMGSV;
        const courseid = SMGSV.courseid;
        const fetchMany = SMGSV.fetchMany;

        loaded.value = false;

        fetchMany([{
            methodname: 'local_gugrades_get_capture_page',
            args: {
                courseid: courseid,
                studentid: studentid,
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
</script>

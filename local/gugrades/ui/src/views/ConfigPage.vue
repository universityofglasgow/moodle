<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div class="border rounded p-2 mt-2">
        <div class="col-12 col-lg-6">
            <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
        </div>
    </div>

    <div v-if="categoryid">
    </div>

    <div v-if="loaded" class="mt-3 border rounded p-2">
        <h3>{{ categoryname }}</h3>
        <ConfigTree :nodes="activitytree" depth="1"></ConfigTree>
    </div>
</template>

<script setup>
    import {ref, computed, inject, watch, defineEmits, onMounted} from '@vue/runtime-core';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import ConfigTree from '@/components/ConfigTree.vue';

    const categoryid = ref(0);
    const activitytree = ref();
    const categoryname = ref('');
    const loaded = ref(false);
    const debug = ref({});

    /**
     * Capture change to top level category dropdown
     * @param {*} level
     */
    function levelOneChange(level) {
        categoryid.value = parseInt(level);
        if (categoryid.value) {
            getActivities(categoryid.value);
        }
    }

    /**
     * Get tree structure of activities and grade categories
     */
    function getActivities(catid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_activities',
            args: {
                courseid: courseid,
                categoryid: catid
            }
        }])[0]
        .then((result) => {
            const tree = JSON.parse(result['activities']);

            window.console.log(tree);

            activitytree.value = tree;
            categoryname.value = tree.category.fullname;
            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        })
    }
</script>
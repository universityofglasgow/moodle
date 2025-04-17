<template>
    <div class="col-12 col-lg-6">
        <LevelOneSelect  @levelchange="levelOneChange"></LevelOneSelect>
        <div>
            <ActivitySelect v-if="showactivityselect" :categoryid="level1category" :currentitem="itemid" @activityselected="activity_selected"></ActivitySelect>
        </div>
        <GroupSelect v-if="itemid" @groupselected="groupselected"></GroupSelect>
    </div>
</template>

<script setup>
    import {ref, defineEmits} from '@vue/runtime-core';
    import LevelOneSelect from '@/components/LevelOneSelect.vue';
    import ActivitySelect from '@/components/ActivitySelect.vue';
    import GroupSelect from '@/components/GroupSelect.vue'

    const level1category = ref(0);
    const showactivityselect = ref(false);
    const itemid = ref(0);
    const groupid = ref(0);

    const emits = defineEmits(['selecteditemid'])

    /**
     * Capture change to top level category dropdown
     * @param {*} level
     */
     function levelOneChange(level) {
        itemid.value = 0;
        level1category.value = parseInt(level);
        if (level1category.value) {
            showactivityselect.value = true;
        } else {
            showactivityselect.value = false;
        }
    }

    /**
     * Capture change to activity selection
     */
     function activity_selected(newitemid) {
        itemid.value = Number(newitemid);
        emits('selecteditemid', {
            itemid: itemid.value,
            groupid: groupid.value,
        });
    }

    /**
     * Capture change to group
     */
    function groupselected(gid) {
        groupid.value = Number(gid);
        emits('selecteditemid', {
            itemid: itemid.value,
            groupid: groupid.value,
        });
    }
</script>
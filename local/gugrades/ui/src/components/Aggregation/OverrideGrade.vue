<template>
    <span class="dropright">
        <a href="#" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Actions">
            <i class="fa fa-ellipsis-v fa-lg ml-1" aria-hidden="true" ></i>
        </a>
        <div v-if="props.categoryid == 0" class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <AddGradeButton :itemid="props.itemid" :userid="props.userid" :name="props.name" :itemname="props.itemname" @gradeadded = "grade_added()"></AddGradeButton>
            <HistoryButton :userid="props.userid" :itemid="props.itemid" :name="props.name" :itemname="props.itemname"></HistoryButton>
            <HideShowButton :gradehidden="props.gradehidden" :itemid="props.itemid" :userid="props.userid" @changed="grade_added()"></HideShowButton>
        </div>
        <div v-else class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <AddGradeButton
                :itemid="props.itemid"
                :categoryid="props.categoryid"
                :userid="props.userid"
                :name="props.name"
                :itemname="props.itemname"
                @gradeadded = "grade_added()">
            </AddGradeButton>
            <HistoryButton :userid="props.userid" :itemid="props.itemid" :name="props.name" :itemname="props.itemname"></HistoryButton>
        </div>
    </span>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import HistoryButton from '@/components/Capture/HistoryButton.vue';
    import AddGradeButton from '@/components/Capture/AddGradeButton.vue';
    import HideShowButton from '@/components/Capture/HideShowButton.vue';

    const props = defineProps({
        userid: Number,
        item: Object,
        itemid: Number,
        categoryid: Number,
        itemname: String,
        name: String,
        awaitingcapture: Boolean,
        gradehidden: Boolean,
        converted: Boolean,
    });

    const emit = defineEmits([
        'gradeadded'
    ]);

    function grade_added() {
        emit('gradeadded');
    }

</script>

<style>
    .dropdown-menu.show {
        overflow: visible;
        z-index: 9999;
    }
</style>
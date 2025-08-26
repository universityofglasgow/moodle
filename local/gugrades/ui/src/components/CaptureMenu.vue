<template>
    <div class="tn-group dropright">
        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Actions">
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <ImportUserGradeButton v-if="props.awaitingcapture && !props.converted && caneditgrade" :itemid="props.itemid" :userid="props.userid" @imported="grade_added()"></ImportUserGradeButton>
            <AddGradeButton v-if="caneditgrade" :itemid="props.itemid" :userid="props.userid" :name="props.name" :itemname="props.itemname" @gradeadded = "grade_added()"></AddGradeButton>
            <HistoryButton :userid="props.userid" :itemid="props.itemid" :name="props.name" :itemname="props.itemname"></HistoryButton>
            <HideShowButton v-if="caneditgrade" :gradehidden="props.gradehidden" :itemid="props.itemid" :userid="props.userid" @changed="grade_added()"></HideShowButton>
        </div>
    </div>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import HistoryButton from '@/components/Capture/HistoryButton.vue';
    import ImportUserGradeButton from '@/components/Capture/ImportUserGradeButton.vue';
    import AddGradeButton from '@/components/Capture/AddGradeButton.vue';
    import HideShowButton from '@/components/Capture/HideShowButton.vue';

    const props = defineProps({
        userid: Number,
        item: Object,
        itemid: Number,
        itemname: String,
        name: String,
        awaitingcapture: Boolean,
        gradehidden: Boolean,
        converted: Boolean,
        caneditgrades: Boolean,
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
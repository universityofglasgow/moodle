<template>
    <div class="dropdown">
        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <ImportUserGradeButton v-if="props.awaitingcapture" :itemid="props.itemid" :userid="props.userid" @imported="grade_added()"></ImportUserGradeButton>
            <AddGradeButton :itemid="props.itemid" :userid="props.userid" :name="props.name" :itemname="props.itemname" @gradeadded = "grade_added()"></AddGradeButton>
            <HistoryButton :userid="props.userid" :itemid="props.itemid" :name="props.name" :itemname="props.itemname"></HistoryButton>
        </div>
    </div>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import HistoryButton from '@/components/Capture/HistoryButton.vue';
    import ImportUserGradeButton from '@/components/Capture/ImportUserGradeButton.vue';
    import AddGradeButton from '@/components/Capture/AddGradeButton.vue';

    //const mstrings = inject('mstrings');

    const props = defineProps({
            userid: Number,
            itemid: Number,
            itemname: String,
            name: String,
            awaitingcapture: Boolean,
        });

    const emit = defineEmits([
        'gradeadded'
    ]);

    function grade_added() {
        emit('gradeadded');
    }
</script>
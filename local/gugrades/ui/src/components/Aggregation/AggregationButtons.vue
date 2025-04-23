<template>
    <div class="col-12 mt-2">
        <RecalculateButton :categoryid="props.categoryid" :staffuserid="props.staffuserid" @recalculated="refreshtable"></RecalculateButton>
        <ConversionButton v-if="allowconversion" :categoryid="props.categoryid" @converted="refreshtable"></ConversionButton>
        <ReleaseCategoryButton v-if="!props.toplevel"
            :disabled="!props.allowrelease"
            :gradeitemid="props.gradeitemid"
            :groupid="props.groupid"
            :released="props.released"
            @released="refreshtable"
        ></ReleaseCategoryButton>
        <ExportAggregationButton v-if="props.toplevel" :categoryid="props.categoryid" :groupid="props.groupid"></ExportAggregationButton>
    </div>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import RecalculateButton from '@/components/Aggregation/RecalculateButton.vue';
    import ConversionButton from '@/components/Aggregation/ConversionButton.vue';
    import ReleaseCategoryButton from '@/components/Aggregation/ReleaseCategoryButton.vue';
    import ExportAggregationButton from '@/components/Aggregation/ExportAggregationButton.vue';

    const props = defineProps({
        categoryid: Number,
        gradeitemid: Number,
        groupid: Number,
        toplevel: Boolean,
        atype: String,
        allowconversion: Boolean,
        allowrelease: Boolean,
        released: Boolean,
        staffuserid: Number,
    });

    const emits = defineEmits([
        'refreshtable'
    ]);

    /**
     * Redraw the main table
     */
    function refreshtable() {
        emits('refreshtable');
    }
</script>
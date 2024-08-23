<template>
    <div class="col-12 mt-2">
        <RecalculateButton :categoryid="props.categoryid" @recalculated="refreshtable"></RecalculateButton>
        <ConversionButton v-if="allowconversion" :categoryid="props.categoryid"></ConversionButton>
    </div>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import RecalculateButton from '@/components/Aggregation/RecalculateButton.vue';
    import ConversionButton from '@/components/Aggregation/ConversionButton.vue';

    const props = defineProps({
        categoryid: Number,
        toplevel: Boolean,
        atype: String,
        allowconversion: Boolean,
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
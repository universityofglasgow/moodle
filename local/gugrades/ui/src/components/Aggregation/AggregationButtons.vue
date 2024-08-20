<template>
    <div class="col-12 mt-2">
        <RecalculateButton :categoryid="props.categoryid" @recalculated="refreshtable"></RecalculateButton>
    </div>
</template>

<script setup>
    import {defineProps, defineEmits} from '@vue/runtime-core';
    import RecalculateButton from '@/components/Aggregation/RecalculateButton.vue';

    const props = defineProps({
        categoryid: Number,
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
<template>
    <div class="col-12 mt-2">
        <small>
            <DismissableAlert v-if="!props.gradesupported" alertstyle="danger" :message="mstrings.gradenotsupported"></DismissableAlert>

            <DismissableAlert v-if="!props.aggregationsupported" alertstyle="danger" :message="mstrings.aggregationnotsupported"></DismissableAlert>

            <DismissableAlert v-if="props.gradehidden" alertstyle="warning" :message="mstrings.gradehidden"></DismissableAlert>

            <DismissableAlert v-if="props.gradelocked" alertstyle="warning" :message="mstrings.gradelocked"></DismissableAlert>

            <DismissableAlert v-if="props.noids" alertstyle="warning" :message="mstrings.noids"></DismissableAlert>
        </small>
    </div>
</template>

<script setup>
    import {inject, defineProps} from '@vue/runtime-core';
    import DismissableAlert from '@/components/DismissableAlert.vue';

    const mstrings = inject('mstrings');

    const props = defineProps({
        gradesupported: Boolean,
        aggregationsupported: Boolean,
        gradehidden: Boolean,
        gradelocked: Boolean,
        noids: Boolean,
    });
</script>
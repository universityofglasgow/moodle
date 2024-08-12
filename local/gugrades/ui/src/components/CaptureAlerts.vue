<template>
    <div class="col-12 mt-2">
        <small>
            <DismissableAlert v-if="!props.gradesupported" alertstyle="danger" :message="mstrings.gradenotsupported"></DismissableAlert>

            <DismissableAlert v-if="props.gradehidden" alertstyle="warning" :message="mstrings.gradehidden"></DismissableAlert>

            <DismissableAlert v-if="props.gradelocked" alertstyle="warning" :message="mstrings.gradelocked"></DismissableAlert>
        </small>
    </div>
</template>

<script setup>
    import {inject, defineProps} from '@vue/runtime-core';
    import DismissableAlert from '@/components/DismissableAlert.vue';

    const mstrings = inject('mstrings');

    const props = defineProps({
        gradesupported: Boolean,
        gradehidden: Boolean,
        gradelocked: Boolean,
    });
</script>
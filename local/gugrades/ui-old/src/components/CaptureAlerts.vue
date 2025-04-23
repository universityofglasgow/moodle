<template>
    <div class="col-12 mt-2">
        <div v-if="!props.gradesupported" class="alert alert-danger">
            <span v-html="mstrings.gradenotsupported"></span>
        </div>

        <div v-if="props.gradehidden" class="alert alert-warning">
            <span v-html="mstrings.gradehidden"></span>
        </div>

        <div v-if="props.gradelocked" class="alert alert-warning">
            <span v-html="mstrings.gradelocked"></span>
        </div>
    </div>
</template>

<script setup>
    import {inject, defineProps} from '@vue/runtime-core';

    const mstrings = inject('mstrings');

    const props = defineProps({
        gradesupported: Boolean,
        gradehidden: Boolean,
        gradelocked: Boolean,
    });
</script>
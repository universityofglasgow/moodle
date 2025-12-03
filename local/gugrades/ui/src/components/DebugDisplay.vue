<template>
    <VueModal v-model="showdebugmodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded" title="A serious error has occurred">
        <div class="alert alert-danger">A serious error has occurred and MyGrades cannot continue.</div>
        <ul>
            <li>
                CourseID: <pre>{{ moodlecourseid }}</pre>
            </li>
            <li v-for="(item, index) in errors">
                {{ index }}: <pre>{{ item }}</pre>
            </li>
        </ul>
        <div class="alert alert-primary">
            <b>Please copy all of this data and send to IT Services / Help Desk for attention</b><br />
            You can then continue.
        </div>
        <div class="mt-2 text-center">
            <a class="btn btn-primary" href="javascript:window.location.reload(true)">Close and continue</a>
        </div>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from '@vue/runtime-core';
    import  * as _ from 'underscore';

    const props = defineProps({
        debug: Object,
    });

    const moodlecourseid = computed(() => {
        const GU = window.GU;
        const courseid = GU.courseid;

        return courseid;
    });

    /**
     * Debug should be an array but it's sometimes a string
     */
    const errors = computed(() => {
        if (typeof props.debug === "string") {
            return {
                message: props.debug,
            } 
        } else {
            return props.debug;
        }
    });

    const showdebugmodal = computed(() => {
        const simpledebug = {...props.debug};
        return !_.isEmpty(simpledebug);
    });
</script>

<style>
    pre {
        white-space: pre-wrap !important;
    }
</style>
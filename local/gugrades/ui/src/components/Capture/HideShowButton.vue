<template>
    <DebugDisplay :debug="debug"></DebugDisplay>
    <a v-if="props.gradehidden" class="dropdown-item" href="#" @click="showhide('show')">{{ mstrings.show }}</a>
    <a v-if="!props.gradehidden" class="dropdown-item" href="#" @click="showhide('hide')">{{ mstrings.hide }}</a>
</template>

<script setup>
    import {defineProps, defineEmits, inject, ref} from '@vue/runtime-core';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const mstrings = inject('mstrings');
    const debug = ref({});

    const props = defineProps({
        courseid: Number,
        itemid: Number,
        userid: Number,
        gradehidden: Boolean,
    });

    const emit = defineEmits(['changed']);

    /**
     * Hide/show button clicked
     */
    function showhide(action) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_show_hide_grade',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userid: props.userid,
                hide: action == 'hide',
            }
        }])[0]
        .then(() => {
            emit('changed');
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }
</script>
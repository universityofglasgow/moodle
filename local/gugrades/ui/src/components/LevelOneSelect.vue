/**
 * Display drop-drown for top-level
 */

<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div>
        <div v-if="notsetup" class="alert alert-warning">
            {{ mstrings.notoplevel }}
        </div>
        <select v-else class="form-control border-dark" @change="levelOneChange($event)">
            <option value="0">{{ mstrings.selectgradecategory }}</option>
            <option v-for="category in level1categories" :key="category.id" :value="category.id" :selected="selected == category.id">{{ category.fullname }}</option>
        </select>
    </div>
</template>

<script setup>
    import {ref, onMounted, defineEmits, inject} from '@vue/runtime-core';
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const level1categories = ref([]);
    const selected = ref(0);
    const notsetup = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');

    const emit = defineEmits(['levelchange']);

    // Get the top level categories
    function getLevelOne() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_levelonecategories',
            args: {
                courseid
            }
        }])[0]
        .then((result) => {
            level1categories.value = result;
            if (result.length == 0) {
                notsetup.value = true;
            }

            // if there's only one then might as well select it. 
            if (result.length == 1) {
                selected.value = result[0].id;
                emit('levelchange', selected.value);
            }
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        })
    }

    // Handle change of selection in dropdown.
    function levelOneChange(event) {
        const categoryid = event.target.value;
        //localStorage.setItem('level1category', categoryid);
        emit('levelchange', categoryid);
    }

    onMounted(() => {
        //selected.value = localStorage.getItem('level1category');
        getLevelOne();
        if (selected.value != 0) {
            emit('levelchange', selected.value);
        }
    });
</script>

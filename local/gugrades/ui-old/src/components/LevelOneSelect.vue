/**
 * Display drop-drown for top-level
 */

<template>
    <div>
        <div v-if="notsetup" class="alert alert-warning">
            {{ mstrings.notoplevel }}
        </div>
        <select v-else class="form-control border-dark" @change="levelOneChange($event)">
            <option value="0">{{ mstrings.selectgradecategory }}</option>
            <option v-for="category in level1categories" :key="category.id" :value="category.id">{{ category.fullname }}</option>
        </select>
    </div>
</template>

<script setup>
    import {ref, onMounted, defineEmits, inject} from '@vue/runtime-core';

    const level1categories = ref([]);
    const notsetup = ref(false);
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
        })
        .catch((error) => {
            window.console.error(error);
        })
    }

    // Handle change of selection in dropdown.
    function levelOneChange(event) {
        const categoryid = event.target.value;
        emit('levelchange', categoryid);
    }

    onMounted(() => {
        getLevelOne();
    });
</script>

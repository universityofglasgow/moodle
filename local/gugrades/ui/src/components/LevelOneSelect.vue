/**
 * Display drop-drown for top-level 
 */

<template>
    <div>
        <select class="form-control" @change="levelOneChange($event)">
            <option value="0"><MString name="selectgradecategory"></MString></option>
            <option v-for="category in level1categories" :key="category.id" :value="category.id">{{ category.fullname }}</option>
        </select>
    </div>  
</template>

<script setup>
    import MString from '@/components/MString.vue';
    import {ref, onMounted, defineEmits} from '@vue/runtime-core';

    const level1categories = ref([]);

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
        })
        .catch((error) => {
            window.console.log(error);
        })
    }

    // Handle change of selection in dropdown.
    function levelOneChange(event) {
        const categoryid = event.target.value;
        emit('levelchange', categoryid);
    }

    onMounted(() => {
        getLevelOne();
    })
</script>

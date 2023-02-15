<template>
    <div>
        Activity select   
    </div>
</template>

<script setup>
    import {ref, onMounted, defineProps} from 'vue';

    const props = defineProps({
        categoryid: Number,
    });

    const activitytree = ref({});

    // Get the sub-category / activity
    function getActivity() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;
        const catid = props.categoryid;

        fetchMany([{
            methodname: 'local_gugrades_get_activities',
            args: {
                courseid: courseid,
                categoryid: catid
            }
        }])[0]
        .then((result) => {
            const tree = JSON.parse(result['activities']);
            activitytree.value = tree;
            window.console.log(tree);
        })
        .catch((error) => {
            window.console.log(error);
        })
    }

    onMounted(() => {
        getActivity();
    })
</script>
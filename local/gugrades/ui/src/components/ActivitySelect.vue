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
                courseid,
                catid
            }
        }])[0]
        .then((result) => {
            activitytree.value = JSON.parse(result);
            window.console.log(activitytree.value);
        })
        .catch((error) => {
            window.console.log(error);
        })
    }

    onMounted(() => {
        getActivity();
    })
</script>
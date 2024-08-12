<template>
    <div class="mt-2 border border-dark p-3 rounded" v-if="loaded">
        <div v-if="collapsed" @click="open_selection" class="cursor-pointer row">
            <div class="col-10">
                {{ mstrings.selected }}: {{ selectedactivity.itemname }}
            </div>
            <div class="col-2 text-right">
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </div>
        </div>
        <div v-else>
            <b><i class="fa fa-list-alt" aria-hidden="true"></i> {{ categoryname }}</b>
            <ActivityTree :nodes="activitytree" @activityselected="activity_selected"></ActivityTree>
        </div>
    </div>
</template>

<script setup>
    import {ref, onMounted, defineProps, defineEmits, watch, inject} from 'vue';
    import ActivityTree from '@/components/ActivityTree.vue';

    const props = defineProps({
        categoryid: Number,
        currentitem: Number,
    });

    const emit = defineEmits(['activityselected']);

    const activitytree = ref({});
    const categoryname = ref('');
    const selectedactivity = ref({});
    const loaded = ref(false);
    const collapsed = ref(false);
    const mstrings = inject('mstrings');

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
            categoryname.value = tree.category.fullname;
            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
        })
    }

    // Get the selected avtivity
    function activity_selected(activityid) {
        const GU = window.GU;
        const fetchMany = GU.fetchMany;
        fetchMany([{
            methodname: 'local_gugrades_get_grade_item',
            args: {
                itemid: activityid,
            }
        }])[0]
        .then((result) => {
            selectedactivity.value = result;
            collapsed.value = true;
        })
        .catch((error) => {
            window.console.error(error);
        });

        // Emit id as well
        emit('activityselected', activityid);
    }

    // (Re-)open the selection
    function open_selection() {
        collapsed.value = false;
    }

    onMounted(() => {
        getActivity();

        // Could be mounted with something selected
        if (props.currentitem) {
            selectedactivity.value = props.currentitem;
            collapsed.value = true;
        }
    });

    // If the categoryid prop changes then we read new values
    // and (re-)open the dialogue
    watch(() => props.categoryid, () => {
        collapsed.value = false;
        getActivity();
    })
</script>

<style scoped>
    .cursor-pointer {
        cursor: pointer;
    }
</style>
<template>
    <ul class="list-unstyled pl-3 mt-1">
        <li v-for="item in props.nodes.items" :key="item.id">
            <ConfigTreeIcon :gradeitem="item"></ConfigTreeIcon>
            {{ item.itemname }}
            <i>&nbsp;<small>{{ item.info.scalename }}</small></i>
        </li>
        <li v-for="category in props.nodes.categories" :key="category.id" class="pb-2">
            <b>
                <i v-if="props.depth == 1" class="fa fa-folder icon itemicon" aria-hidden="true"></i>
                <i v-else class="fa fa-folder-o" aria-hidden="true"></i>
                {{ category.category.fullname }}
            </b>
            <i>&nbsp;<small>{{ category.category.strategy }}</small></i>
            <ConfigTree :nodes="category" @activityselected="sub_activity_click" :depth="props.depth + 1"></ConfigTree>
        </li>
    </ul>
</template>

<script setup>
    import {defineProps, defineEmits} from 'vue';
    import ConfigTreeIcon from './ConfigTreeIcon.vue';

    const props = defineProps({
        nodes: Object,
        depth: Number,
    });

    const emit = defineEmits(['activityselected']);

    // Emit activity id when activity selected
    function activity_click(itemid, event) {
        event.preventDefault();
        emit('activityselected', itemid);
    }

    // As emit only works for one level, this re-emits events
    // from lower levels.
    function sub_activity_click(activityid) {
        emit('activityselected', activityid);
    }
</script>
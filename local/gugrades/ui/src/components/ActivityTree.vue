<template>
    <ul class="list-unstyled pl-3">
        <li v-for="item in props.nodes.items" :key="item.id">
            <a href="#" @click="activity_click(item.id, $event)">{{ item.itemname }}</a>
        </li>
        <li v-for="category in props.nodes.categories" :key="category.id">
            <b>
                <i v-if="props.depth == 1" class="fa fa-folder" aria-hidden="true"></i>
                <i v-else class="fa fa-folder-o" aria-hidden="true"></i>
                {{ category.category.fullname }}
            </b>
            <ActivityTree :nodes="category" @activityselected="sub_activity_click" :depth="props.depth + 1"></ActivityTree>
        </li>
    </ul>
</template>

<script setup>
    import {defineProps, defineEmits} from 'vue';

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
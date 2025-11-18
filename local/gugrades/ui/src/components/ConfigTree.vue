<template>
    <ul class="list-unstyled pl-3 mt-1">
        <li v-for="item in props.nodes.items" :key="item.id" :class="resitclass">
            <ResitCheckbox v-if="resitconfig && !resitfade" :itemid="item.id" :checkeditemid="resititemid" @checked="resit_clicked"></ResitCheckbox>
            <ConfigTreeIcon :gradeitem="item"></ConfigTreeIcon>
            {{ item.itemname }}
            <i>&nbsp;<small>
                {{ item.info.scalename }}
                <span v-if="!item.info.isscale">&nbsp;{{ item.grademax }}</span>
            </small></i>
        </li>
        <li v-for="category in props.nodes.categories" :key="category.id" class="pb-2" :class="{ 'bg-light': category.category.even }">
            <ResitCheckbox v-if="resitconfig && !resitfade" :itemid="category.itemid" :checkeditemid="resititemid" @checked="resit_clicked"></ResitCheckbox>
            <b>
                <i v-if="props.depth == 1" class="fa fa-folder icon itemicon" :title="mstrings.gradecategory" aria-hidden="true"></i>
                <i v-else class="fa fa-folder-o" :title="mstrings.gradecategory" aria-hidden="true"></i>
                {{ category.category.fullname }}
            </b>
            <i>&nbsp;<small>{{ category.category.strategy }}</small></i>
            <ConfigTree :nodes="category" @activityselected="sub_activity_click" :depth="props.depth + 1" :resitconfig="resitconfig" :resitfade="!category.category.resitcandidate"></ConfigTree>
        </li>
    </ul>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from 'vue';
    import ConfigTreeIcon from './ConfigTreeIcon.vue';
    import ResitCheckbox from './ResitCheckbox.vue';

    /**
     * resitconfig = enable display of resit radio boxes etc.
     * resitfade = if above is true then non-resit categories are faded out.
     */
    const props = defineProps({
        nodes: Object,
        depth: Number,
        resitconfig: Boolean,
        resitfade: Boolean,
    });

    const mstrings = inject('mstrings');
    const emit = defineEmits(['activityselected']);
    const resititemid = ref();



    /**
     * Get resit CSS class
     */
    const resitclass = computed(() => (
        {
            resit_fade: props.resitconfig && props.resitfade,
            resit_nofade: !props.resitconfig || !props.resitfade
        }
    ));

    /**
     * A resit box was clicked. 
     */
    function resit_clicked(itemid) {
        window.console.log(itemid);
    }
    

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

<style>
    .resit_fade {
        opacity: 0.2;
    }

    .resit_nofade {
        opacity: 1.0;
    }
</style>
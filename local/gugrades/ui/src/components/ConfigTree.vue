<template>
    <tr v-for="item in props.nodes.items" :key="item.id" :class="resitclass">
        <td v-if="resitconfig && !resitfade">
            <ResitCheckbox  :itemid="item.id" :checkeditemid="resititemid" @checked="resit_clicked"></ResitCheckbox>
        </td>
        <td :style="indentstyle">
            <ConfigTreeIcon :gradeitem="item"></ConfigTreeIcon>
            {{ item.itemname }}
        </td>
        <td>&nbsp;</td> <!-- holder for strategy -->
        <td>
            {{ item.info.scalename }}
            <span v-if="!item.info.isscale">&nbsp;({{ item.grademax }})</span>
        </td>
        <td>
            <span v-if="showeights">{{ item.info.weight }}%</span>
        </td>
    </tr>
    <template v-for="category in props.nodes.categories" :key="category.id">
        <tr class="pb-2" :class="{ 'bg-light': category.category.even }">
            <td v-if="resitconfig && !resitfade">
                <ResitCheckbox v-if="resitconfig && !resitfade" :itemid="category.itemid" :checkeditemid="resititemid" @checked="resit_clicked"></ResitCheckbox>
            </td>
            <td :style="indentstyle">
                <b>
                    <i v-if="props.depth == 1" class="fa fa-folder icon itemicon" :title="mstrings.gradecategory" aria-hidden="true"></i>
                    <i v-else class="fa fa-folder-o" :title="mstrings.gradecategory" aria-hidden="true"></i>
                    {{ category.category.fullname }}
                </b>
            </td>
            <td>
                {{ category.category.strategy }}
            </td>
            <td></td> <!-- Holder for scale -->
            <td>
                <span v-if="showeights">{{ category.category.info.weight }}%</span>
            </td>
        </tr>
        <ConfigTree :nodes="category" @activityselected="sub_activity_click" :depth="nextlevel" :resitconfig="resitconfig" :resitfade="!category.category.resitcandidate"></ConfigTree>
    </template>
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
     * Need this to make sure we pass down a number,
     * not a string
     */
    const nextlevel = computed(() => props.depth*1 + 1);

    /**
     * Are we showing weights?
     */
    const showeights = computed(() => props.nodes.category.weighted);

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
     * Get indent/padding class
     */
    const indentstyle = computed(() => {
        const padding = props.depth * 30;

        return {
            'padding-left': padding + 'px',
        }
    });

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
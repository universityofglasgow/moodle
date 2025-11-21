<template>
    <a href="#/" @click="$emit('checked', itemid)" class="plainlink" :style="indentstyle">
        <i v-if="checked" class="fa fa-check-square-o" aria-hidden="true"></i>
        <i v-else class="fa fa-square-o" aria-hidden="true"></i>
    </a>
    <span class="badge badge-pill ml-2"  :class="badgeclass">Resit?</span>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from 'vue';

    const props = defineProps({
        itemid: Number,
        checkeditemid: Number,
        depth: Number,
    });

    const bscolors = [
        'primary',
        'secondary',
        'success',
        'danger',
        'warning',
        'info'
    ];

    /**
     * Is the box checked?
     */
    const checked = computed(() => props.itemid == props.checkeditemid);

    /**
     * badge class (by color)
     */
    const badgeclass = computed(() => {
        const index = (props.depth - 2) % bscolors.length;

        return 'badge-' + bscolors[index];
    })

    /**
     * Get indent/padding class
     */
    const indentstyle = computed(() => {
        const padding = (props.depth - 2) * 30;

        return {
            'padding-left': padding + 'px',
        }
    });
    
</script>

<style>
    .plainlink, .plainlink:hover, .plainlink:visited, .plainlink:link, .plainlink:active {
        text-decoration: none;
    }
</style>
<template>
    <a href="#" @click="$emit('checked', itemid)" class="plainlink">
        <i v-if="checked" class="fa fa-check-square-o" aria-hidden="true"></i>
        <i v-else class="fa fa-square-o" aria-hidden="true"></i>
    </a>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from 'vue';

    const props = defineProps({
        itemid: Number,
        checkeditemid: Number,
    });

    /**
     * Is the box checked?
     */
    const checked = computed(() => props.itemid == props.checkeditemid);
    
</script>

<style>
    .plainlink, .plainlink:hover, .plainlink:visited, .plainlink:link, .plainlink:active {
        text-decoration: none;
    }
</style>
<template>
    <span v-if="props.yes">Yes</span>
    <span v-else>No</span>
</template>

<script setup>
    import {ref, defineProps, inject} from '@vue/runtime-core';

    const props = defineProps({
        yes: Boolean,
    });
</script>
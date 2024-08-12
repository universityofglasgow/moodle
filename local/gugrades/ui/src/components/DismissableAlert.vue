<template>
    <div class="alert alert-dismissible fade show" :class="[alertclass]" role="alert">
        <span v-html="props.message"></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>

<script setup>
    import {defineProps, computed} from '@vue/runtime-core';

    const alertclass = computed(() => {
        return 'alert-' + props.alertstyle;
    })

    const props = defineProps({
        message: String,
        alertstyle: String,
    });
</script>
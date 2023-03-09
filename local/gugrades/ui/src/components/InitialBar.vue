<template>
    <div class="initialbar {{class}} d-flex flex-wrap justify-content-center justify-content-md-start">
        <span class="initialbarlabel mr-2">{{ props.label }}</span>

        <nav class="initialbargroups d-flex flex-wrap justify-content-center justify-content-md-start">
            <ul class="pagination pagination-sm">
                <li class="initialbarall page-item active">
                    <a data-initial="" class="page-link" href="#">All</a>
                </li>
            </ul>
            <ul class="pagination pagination-sm">
                <li v-for="letter in letters" :key="letter" class="page-item">
                    <a class="page-link" href="#">{{letter}}</a>
                </li>
            </ul>
        </nav>
    </div>    
</template>

<script setup>
    import {computed, defineProps} from '@vue/runtime-core';

    const props = defineProps({
        'label': String,
    });

    const letters = computed(() => {
        return Array.from("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
    });
</script>
<template>
    <div ref="dropZoneRef">
      Drop files here
    </div>
</template>

<script setup>
    import {ref, onMounted, defineProps} from 'vue';
    import { useDropZone } from '@vueuse/core';
    import { useFileSelection } from '@/js/fileselect.js';

    const dropZoneRef = ref(null);

    const {isOverDropZone, chooseFiles} = useFileSelection({
        dropzone: dropZoneRef,
        allowMultiple: true,
        allowedFileTypes: 'text/csv',
        onFiles: process,
    });

    function process(files) {
        window.console.log(files);
    }
</script>
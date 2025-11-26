<template>
    <div class="mt-1 alert alert-danger">
        <p><b><span v-html="mstrings.configerror"></span>:</b></p>
        <p v-html="errormessage"></p>
    </div>
</template>

<script setup>
    import {defineProps, inject} from 'vue';

    const props = defineProps({
        errormessage: String,
    });
    const mstrings = inject('mstrings');
</script>
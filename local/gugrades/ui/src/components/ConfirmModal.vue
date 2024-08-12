<template>
    <VueModal v-model="showmodal" modalClass="col-11 col-lg-5 rounded" :title="mstrings.confirm">
        <div class="alert alert-danger">
            <p><strong>{{  props.message }}</strong></p>
            <p>{{ mstrings.areyousure }}</p>
        </div>
        <div class="mt-2 pt-2 border-top">
            <button class="btn btn-primary mr-1" @click="emit('confirm', true)">{{ mstrings.yes }}</button>
            <button class="btn btn-warning" @click="emit('confirm', false)">{{ mstrings.no }}</button>
        </div>
    </VueModal>
</template>

<script setup>
    import {defineProps, defineEmits, inject, toRef} from '@vue/runtime-core';

    const props = defineProps({
        show: Boolean,
        message: String,
    });

    const showmodal = toRef(props, 'show');

    const emit = defineEmits(['confirm']);
    const mstrings = inject('mstrings');
</script>
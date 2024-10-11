<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <a class="dropdown-item" href="#" @click.prevent="alter_weights()">
        {{ mstrings.altertitle }}
    </a>

    <VueModal v-model="showaltermodal" modalClass="col-11 col-lg-5 rounded" :title="mstrings.altertitle">

        <button class="btn btn-warning" type="button" @click="showaltermodal = false">{{  mstrings.cancel }}</button>
    </VueModal>
</template>

<script setup>
    import {ref, defineProps, defineEmits, inject, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showaltermodal = ref(false);
    const mstrings = inject('mstrings');
    const toast = useToast();

    const props = defineProps({
        userid: Number,
        itemid: Number,
    });

    /**
     * Alter weights button has been clicked
     */
    function alter_weights() {
        showaltermodal.value = true;
    }
</script>
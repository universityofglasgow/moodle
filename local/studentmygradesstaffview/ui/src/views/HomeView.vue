<template>
    <DebugDisplay :debug="debug"></DebugDisplay>
    <h1>Here!!</h1>
    <div class="border rounded p-2 mt-2">
        <div class="col-12 col-lg-6">
            <StudentPicker></StudentPicker>
        </div>
    </div>

</template>

<script setup>
    import {ref, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import StudentPicker from '@/components/StudentPicker.vue';

    const toast = useToast();
</script>

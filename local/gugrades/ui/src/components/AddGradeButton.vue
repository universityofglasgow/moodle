<template>
    <button type="button" class="btn btn-outline-primary btn-sm" @click="add_grade()">Add grade</button>

    <Teleport to="body">
        <ModalForm :show="showaddgrademodal" @close="showaddgrademodal = false">
            <template #header>
                <h4>Add grade</h4>
                
            </template>
            <template #body>

            </template>
        </ModalForm>
    </Teleport>
</template>

<script setup>
    import {ref, defineProps} from '@vue/runtime-core';
    import ModalForm from '@/components/ModalForm.vue';

    const showaddgrademodal = ref(false);

    const props = defineProps({
        userid: Number,
        itemid: Number
    });

    /**
     * Read history on button click
     */
    function add_grade() {

        window.console.log(props);
        showaddgrademodal.value = true;
    }
</script>
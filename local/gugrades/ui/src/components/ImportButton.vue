<template>
    <button type="button" class="btn btn-outline-dark  mr-1" @click="showimportmodal = true"><MString name="importgrades"></MString></button>

    <Teleport to="body">
        <ModalForm :show="showimportmodal" @close="showimportmodal = false">
            <template #header>
                <h4><MString name="importgrades"></MString></h4>
            </template>
            <template #body>
                Form goes here
                <p><button class="btn btn-primary" @click="importgrades">Import</button></p>
            </template>
        </ModalForm>
    </Teleport>
</template>

<script setup>
    import {ref, defineProps, defineEmits} from '@vue/runtime-core';
    import ModalForm from '@/components/ModalForm.vue';
    import MString from '@/components/MString.vue';
    import { useToast } from "vue-toastification";

    const props = defineProps({
        userids: Array,
        itemid: Number
    });

    const toast = useToast();

    const emit = defineEmits(['imported']);

    const showimportmodal = ref(false);

    /**
     * Import grades button clicked
     */
     function importgrades() {
        window.console.log('IMPORT GRADES');

        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany; 
        
        window.console.log(props);
        
        fetchMany([{
            methodname: 'local_gugrades_import_grades_users',
            args: {
                courseid: courseid,
                gradeitemid: props.itemid,
                userlist: props.userids.toString(),
            }
        }])[0]
        .then(() => {
            emit('imported');
        })
        .catch((error) => {
            window.console.log(error);
            toast.error('Error communicating with server (see console)');
        });

        showimportmodal.value = false;
    }
</script>
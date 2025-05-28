<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div>
        <EasyDataTable 
            :headers="headers"
            :items="items"
            ref="dataTable"
            hide-footer
        ></EasyDataTable>
        <!-- Implementation of our own accessible footer. -->
        <CustomPagination v-bind="paginationProps" />
    </div>
    <div>
        <button class="mt-2 btn btn-success" @click="download_clicked">{{ mstrings.downloadtocsv }}</button>
    </div>
</template>

<script setup>
    import {ref, computed, onMounted, inject} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import { saveAs } from 'file-saver';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import CustomPagination from '@/components/CustomPagination.vue';

    const mstrings = inject('mstrings');
    const items = ref([]);
    const headers = ref([]);
    const debug = ref({});
    const toast = useToast();
    // pagination related.
    const dataTable = ref();
    const currentPageFirstIndex = computed(() => dataTable.value?.currentPageFirstIndex);
    const currentPageLastIndex = computed(() => dataTable.value?.currentPageLastIndex);
    const clientItemsLength = computed(() => dataTable.value?.clientItemsLength);
    const maxPaginationNumber = computed(() => dataTable.value?.maxPaginationNumber);
    const currentPaginationNumber = computed(() => dataTable.value?.currentPaginationNumber);
    const isFirstPage = computed(() => dataTable.value?.isFirstPage);
    const isLastPage = computed(() => dataTable.value?.isLastPage);
    const paginationProps = {
        dataTable: dataTable,
        currentPageFirstIndex: currentPageFirstIndex,
        currentPageLastIndex: currentPageLastIndex,
        clientItemsLength: clientItemsLength,
        maxPaginationNumber: maxPaginationNumber,
        currentPaginationNumber: currentPaginationNumber,
        isFirstPage: isFirstPage,
        isLastPage: isLastPage
    }

    /**
     * Download button clicked
     */
    function download_clicked() {
        let csv =
            mstrings.time + ', ' +
            mstrings.gradeitem + ', ' +
            mstrings.by + ', ' +
            mstrings.relateduser + ', ' +
            mstrings.message + '\n';
        items.value.forEach((item) => {
            csv +=
                '"' + item.time + '", ' +
                '"' + item.gradeitem + '", ' +
                '"' + item.username + '", ' +
                '"' + item.relatedusername + '", ' +
                '"' + item.message.replaceAll('"', '') + '"\n';
        });
        const d = new Date();
        const filename = 'Audit_' + d.toLocaleString() + '.csv';
        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
        saveAs(blob, filename);
    }

    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        headers.value = [
               {text: mstrings.time, value: 'time'},
               {text: mstrings.gradeitem, value: 'gradeitem'},
               {text: mstrings.by, value: 'username'},
               {text: mstrings.relateduser, value: 'relatedusername'},
               {text: mstrings.message, value: 'message'},
            ];

        fetchMany([{
            methodname: 'local_gugrades_get_audit',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            items.value = result;

        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        })
    });
</script>
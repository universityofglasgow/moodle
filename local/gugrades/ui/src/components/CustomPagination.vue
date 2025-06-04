<template>
    <div class="customize-footer d-flex justify-content-end">
        <div class="customize-rows-per-page">
            rows per page: 
            <select
                class="select-items"
                @change="updateRowsPerPageSelect"
            >
                <option
                v-for="item in rowsPerPageOptions"
                :key="item"
                :selected="item === rowsPerPageActiveOption"
                :value="item"
                >
                {{ item }}
                </option>
            </select>
        </div>

        <div class="customize-index ml-2 mr-2">
            {{currentPageFirstIndex}} - {{currentPageLastIndex}} of {{clientItemsLength}}
        </div>

        <PaginationArrows
            :is-first-page="isFirstPage"
            :is-last-page="isLastPage"
            :max-pagination-number="maxPaginationNumber"
            @click-next-page="clickNextPage"
            @click-prev-page="clickPrevPage"
        >
            <template #buttonsPagination>
                <ButtonsPagination
                    :current-pagination-number="currentPaginationNumber"
                    :max-pagination-number="maxPaginationNumber"
                    @update-page="updatePage"
                />
            </template>
        </PaginationArrows>
    </div>
</template>

<script setup>
    import { computed } from '@vue/runtime-core';
    import ButtonsPagination from '@/components/ButtonsPagination.vue';
    import PaginationArrows from '@/components/PaginationArrows.vue';

    const props = defineProps({
        dataTable: Object,
    });

    const dataTable = props.dataTable;
    const currentPageFirstIndex = computed(() => dataTable.value?.currentPageFirstIndex);
    const currentPageLastIndex = computed(() => dataTable.value?.currentPageLastIndex);
    const clientItemsLength = computed(() => dataTable.value?.clientItemsLength);
    let currentPaginationNumber = computed(() => dataTable.value?.currentPaginationNumber);
    let maxPaginationNumber = computed(() => dataTable.value?.maxPaginationNumber);
    const isFirstPage = computed(() => dataTable.value?.isFirstPage);
    const isLastPage = computed(() => dataTable.value?.isLastPage);

    // // Rows per page related.
    const rowsPerPageOptions = computed(() => dataTable.value?.rowsPerPageOptions);
    const rowsPerPageActiveOption = computed(() => dataTable.value?.rowsPerPageActiveOption);

    const updatePage = (paginationNumber) => {
        props.dataTable.value.updatePage(paginationNumber);
        currentPaginationNumber.value = paginationNumber;
    };

    const updateRowsPerPageSelect = (e) => {
        dataTable.value.updateRowsPerPageActiveOption(Number(e.target.value));
    };

    const clickNextPage = () => {
        props.dataTable.value.nextPage();
        currentPaginationNumber.value++;
    };

    const clickPrevPage = () => {
        props.dataTable.value.prevPage();
        currentPaginationNumber.value--;
    };
</script>
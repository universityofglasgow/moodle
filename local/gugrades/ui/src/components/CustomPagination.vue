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

        <div class="customize-pagination">
            <nav aria-label="Search results pages">
                <ul class="pagination pagination-sm">
                    <li class="page-item" :class="{'disabled': isFirstPage}" @click="prevPage" :disabled="isFirstPage">
                        <span v-if="isFirstPage" class="page-link">prev</span>
                        <a v-else class="page-link" href="#" @keyup.left="handleKeyNavigation('prev', $event)" @keyup.right="handleKeyNavigation(0, $event)" name="prev" role="button" tabindex="0">prev</a>
                    </li>

                    <li
                        v-for="paginationNumber in maxPaginationNumber"
                        class="page-item"
                        :class="{'active': paginationNumber === currentPaginationNumber}"
                        @click="updatePage(paginationNumber)"
                        @keyup.left="handleKeyNavigation(paginationNumber, $event)"
                        @keyup.right="handleKeyNavigation(paginationNumber, $event)"
                    >
                        <a class="page-link" href="#" role="button" tabindex="0" :name="paginationNumber">{{paginationNumber}}</a>
                    </li>

                    <li class="page-item" :class="{'disabled': isLastPage}" @click="nextPage" :disabled="isLastPage">
                        <span v-if="isLastPage" class="page-link">next</span>
                        <a v-else class="page-link" href="#" @keyup.left="handleKeyNavigation(maxPaginationNumber +1, $event)" @keyup.right="handleKeyNavigation('next', $event)" name="next" role="button" tabindex="0">next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script setup>
    import { computed} from '@vue/runtime-core';

    const props = defineProps({
        dataTable: Object,
        currentPageFirstIndex: Number,
        currentPageLastIndex: Number,
        clientItemsLength: Number,
        maxPaginationNumber: Number,
        currentPaginationNumber: Number,
        isFirstPage: Boolean,
        isLastPage: Boolean
    });

    const currentPageFirstIndex = props.currentPageFirstIndex;
    const currentPageLastIndex = props.currentPageLastIndex;
    const clientItemsLength = props.clientItemsLength;
    let maxPaginationNumber = props.maxPaginationNumber;
    let currentPaginationNumber = props.currentPaginationNumber;
    const isFirstPage = props.isFirstPage;
    const isLastPage = props.isLastPage;

    const nextPage = () => {
        props.dataTable.value.nextPage();
        currentPaginationNumber.value++;
    };
    const prevPage = () => {
        props.dataTable.value.prevPage();
        currentPaginationNumber.value--;
    };
    const updatePage = (paginationNumber) => {
        props.dataTable.value.updatePage(paginationNumber);
        currentPaginationNumber.value = paginationNumber;
    };

    // Rows per page related.
    const rowsPerPageOptions = computed(() => props.dataTable.value?.rowsPerPageOptions);
    const rowsPerPageActiveOption = computed(() => props.dataTable.value?.rowsPerPageActiveOption);

    const updateRowsPerPageSelect = (e) => {
        props.dataTable.value.updateRowsPerPageActiveOption(Number(e.target.value));
    };

    /**
     * Give focus to the element to the left of the current one.
     *
     * @param elemname
     */
    function moveLeft(elemname) {
        let el = '';
        switch (elemname) {
            case 'prev':
                el = ((document.getElementsByName('next').length) ? 'next' : maxPaginationNumber.value);
            break;
            default:
                el = ((elemname == 1) ? ((document.getElementsByName('prev').length) ? 'prev' : ((document.getElementsByName('next').length) ? 'next' : maxPaginationNumber.value)) : elemname -1);
            break;
        }
        let tmp = document.getElementsByName(el);
        tmp[0].focus({ focusVisible:true });
    }

    /**
     * Give focus to the element to the right of the current one.
     *
     * @param elemname
     */
    function moveRight(elemname) {
        let el = '';
        switch (elemname) {
            case 'next':
                el = ((document.getElementsByName('prev').length) ? 'prev' : 1);
            break;
            default:
                el = ((elemname +1 > maxPaginationNumber.value) ? ((document.getElementsByName('next').length) ? 'next': ((document.getElementsByName('prev').length) ? 'prev' : 1)) : elemname +1);
            break;
        }
        let tmp = document.getElementsByName(el);
        tmp[0].focus({ focusVisible:true });
    }

    /**
     * Listen for left/right arrow key events.
     *
     * @param elemname
     * @param e
     */
    function handleKeyNavigation (elemname, e) {
        switch (e.keyCode) {
            case 37:
                moveLeft(elemname);
            break;
            case 39: 
                moveRight(elemname);
            break;
      }
    }
</script>
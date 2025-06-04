<template>
    <div class="customize-pagination">
        <nav aria-label="Search results pages">
            <ul class="pagination pagination-sm">
                <li 
                    class="page-item previous-page__click-button"
                    :class="{'first-page disabled': props.isFirstPage}"
                    @click="emits('clickPrevPage')"
                    :disabled="props.isFirstPage">
                        <span v-if="props.isFirstPage" class="arrow arrow-right"></span>
                        <a v-else class="arrow arrow-right" href="#" @keyup.left="handleKeyNavigation('prev', $event)" @keyup.right="handleKeyNavigation(1, $event)" name="prev" role="button" tabindex="0"></a>
                </li>
                <slot name="buttonsPagination"></slot>
                <li 
                    class="page-item next-page__click-button"
                    :class="{'last-page disabled': props.isLastPage}"
                    @click="emits('clickNextPage')"
                    :disabled="props.isLastPage">
                        <span v-if="props.isLastPage" class="arrow arrow-left"></span>
                        <a v-else class="arrow arrow-left" href="#" @keyup.left="handleKeyNavigation(props.maxPaginationNumber, $event)" @keyup.right="handleKeyNavigation('next', $event)" name="next" role="button" tabindex="0"></a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
    const props = defineProps({
        isFirstPage: { type: Boolean, required: false },
        isLastPage: { type: Boolean, required: false },
        maxPaginationNumber: { type: Number, required: false},
    });

    const emits = defineEmits(['clickPrevPage', 'clickNextPage']);

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

    /**
     * Give focus to the element to the left of the current one.
     *
     * @param elemname
     */
    function moveLeft(elemname) {
        let tmp = document.getElementsByName(elemname);
        let elem = '';

        if (elemname == props.maxPaginationNumber) {
            elem = elemname;
        } else {
            if (tmp[0].parentElement.previousElementSibling != null) {
                if (tmp[0].parentElement.previousElementSibling.length) {
                    elem = tmp[0].parentElement.previousElementSibling.firstElementChild;
                }
            } else {
                if (document.getElementsByName('next').length) {
                    elem = 'next';
                } else {
                    elem = props.maxPaginationNumber;
                }
            }
        }

        let el = document.getElementsByName(elem);
        el[0].focus({ focusVisible:true });
    }

    /**
     * Give focus to the element to the right of the current one.
     *
     * @param elemname
     */
    function moveRight(elemname) {
        let tmp = document.getElementsByName(elemname);
        let elem = '';

        if (elemname == 1) {
            elem = elemname;
        } else {
            if (tmp[0].parentElement.nextElementSibling != null) {
                if (tmp[0].parentElement.nextElementSibling.length) {
                    elem = tmp[0].parentElement.nextElementSibling.firstElementChild;
                }
            } else {
                if (document.getElementsByName('prev').length) {
                    elem = 'prev';
                } else {
                    elem = '1';
                }
            }
        }

        let el = document.getElementsByName(elem);
        el[0].focus({ focusVisible:true });
    }
</script>
<style scoped>
    .previous-page__click-button, .next-page__click-button {
        margin: 0px 5px;
        cursor: pointer;
        .arrow {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-top: 2px solid #000;
            border-left: 2px solid #000;
            &.arrow-left {
                transform: rotate(135deg);
            }
            &.arrow-right {
                transform: rotate(-45deg);
            }
        }
    }
    .page-item.first-page, .page-item.last-page {
        cursor: not-allowed;
        .arrow {
            border-color: #e0e0e0;
        }
    }

    .arrow {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-top: 2px solid #000;
    border-left: 2px solid #000;
    }

    .arrow.arrow-left {
        transform: rotate(135deg);
    }

    .arrow.arrow-right {
        transform: rotate(-45deg);
    }
</style>
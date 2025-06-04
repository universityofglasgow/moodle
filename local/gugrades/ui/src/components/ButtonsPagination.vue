<template>
        <li
        v-for="(item, i) in paginationItemsForRender"
        :key="i"
        class="page-item"
        :class="{
            button: item.type === 'button',
            active: item.type === 'button' && item.active,
            'active-prev': item.type === 'button' && item.activePrev,
            omission: item.type === 'omission',
        }"
        @click="(item.type === 'button' ? changePage(item) : '')"
        @keyup.left="handleKeyNavigation((item.type === 'button' ? item.page : 'omission' + i), $event)"
        @keyup.right="handleKeyNavigation((item.type === 'button' ? item.page : 'omission' + i), $event)"
        >
        <a class="page-link" href="#" role="button" tabindex="0" :name="item.type === 'button' ? item.page : 'omission' + i">{{ item.type === 'button' ? item.page : '...' }}</a>
    </li>
</template>

<script setup>
    import { computed } from 'vue';

    const emits = defineEmits(['updatePage']);

    const props = defineProps({
        maxPaginationNumber: { type: Number, required: true },
        currentPaginationNumber: { type: Number, required: true },
    });

    const totalVisible = 7;

    const changePage = (PaginationItem) => {
        if (PaginationItem.type === 'button' && !PaginationItem.active) emits('updatePage', PaginationItem.page);
    };

    const paginationItemsForRender = computed(() => {
        const paginationItems = [];
        if (props.maxPaginationNumber <= totalVisible) {
            // x,x,x,x
            for (let i = 1; i <= props.maxPaginationNumber; i += 1) {
                paginationItems.push({
                    type: 'button',
                    page: i,
                    active: i === props.currentPaginationNumber,
                    activePrev: (i + 1) === props.currentPaginationNumber,
                });
            }
        } else if ([1, 2, props.maxPaginationNumber, props.maxPaginationNumber - 1].includes(props.currentPaginationNumber)) {
            // x,x,x,...,x,x,x
            for (let i = 1; i <= totalVisible; i += 1) {
                if (i <= 3) {
                    paginationItems.push({
                        type: 'button',
                        page: i,
                        active: i === props.currentPaginationNumber,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                } else if (i === 4) {
                    paginationItems.push({
                        type: 'omission',
                    });
                } else {
                    const page = props.maxPaginationNumber - (totalVisible - i);
                    paginationItems.push({
                        type: 'button',
                        page,
                        active: page === props.currentPaginationNumber,
                        activePrev: (page + 1) === props.currentPaginationNumber,
                    });
                }
            }
        } else if ([3, 4].includes(props.currentPaginationNumber)) {
            // x,x,x,x,x,...,x
            for (let i = 1; i <= totalVisible; i += 1) {
                if (i <= 5) {
                    paginationItems.push({
                        type: 'button',
                        page: i,
                        active: i === props.currentPaginationNumber,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                } else if (i === 6) {
                    paginationItems.push({
                        type: 'omission',
                    });
                } else {
                    paginationItems.push({
                        type: 'button',
                        page: props.maxPaginationNumber,
                        active: props.maxPaginationNumber === props.currentPaginationNumber,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                }
            }
        } else if ([props.maxPaginationNumber - 2, props.maxPaginationNumber - 3].includes(props.currentPaginationNumber)) {
            // x,...,x,x,x,x,x
            for (let i = 1; i <= totalVisible; i += 1) {
                if (i === 1) {
                    paginationItems.push({
                        type: 'button',
                        page: 1,
                        active: props.currentPaginationNumber === 1,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                } else if (i === 2) {
                    paginationItems.push({
                        type: 'omission',
                    });
                } else {
                    const page = props.maxPaginationNumber - (totalVisible - i);
                    paginationItems.push({
                        type: 'button',
                        page,
                        active: page === props.currentPaginationNumber,
                        activePrev: (page + 1) === props.currentPaginationNumber,
                    });
                }
            }
        } else {
            // x,...,x,x,x,...,x
            for (let i = 1; i <= totalVisible; i += 1) {
                if (i === 1) {
                    paginationItems.push({
                        type: 'button',
                        page: 1,
                        active: props.currentPaginationNumber === 1,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                } else if (i === 2 || i === 6) {
                    paginationItems.push({
                        type: 'omission',
                    });
                } else if (i === 7) {
                    paginationItems.push({
                        type: 'button',
                        page: props.maxPaginationNumber,
                        active: props.maxPaginationNumber === props.currentPaginationNumber,
                        activePrev: (i + 1) === props.currentPaginationNumber,
                    });
                } else {
                    const diff = 4 - i;
                    const page = props.currentPaginationNumber - diff;
                    paginationItems.push({
                        type: 'button',
                        page,
                        active: page === props.currentPaginationNumber,
                        activePrev: (page + 1) === props.currentPaginationNumber,
                    });
                }
            }
        }

        return paginationItems;
    });

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
        let elem = tmp[0].parentElement.previousElementSibling.firstElementChild;

        // The next element is one we can't tab/arrow key to. Cycle around and check if that works.
        if (elem.nodeName == 'SPAN') {
            if (document.getElementsByName('next').length == 1) {
                elem = 'next';
            } else {
                elem = props.maxPaginationNumber;
            }
        } else {
            elem = elem.name;
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
        let elem = tmp[0].parentElement.nextElementSibling.firstElementChild;

        // The next element is one we can't tab/arrow key to. Cycle around and check if that works.
        if (elem.nodeName == 'SPAN') {
            if (document.getElementsByName('prev').length == 1) {
                elem = 'prev';
            } else {
                elem = '1';
            }
        } else {
            elem = elem.name;
        }

        let el = document.getElementsByName(elem);
        el[0].focus({ focusVisible:true });
    }
</script>
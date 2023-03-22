<template>
    <nav v-if="showbar" class="pagination pagination-centered justify-content-center">
        <ul class="mt-1 pagination ">
            <li v-if="show.previous" class="page-item">
                <a class="page-link" @click="pageclick(show.previouspage)">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous page</span>
                </a>
            </li>
            <li v-if="show.first" class="page-item" >
                <a class="page-link" @click="pageclick(1)">
                    <span aria-hidden="true">1</span>
                    <span class="sr-only">Page 1</span>
                </a>
            </li>
            <li v-if="show.first" class="page-item disabled">
                <span class="page-link">&hellip;</span>
            </li>
            <li v-for="page in pages" :key="page" class="page-item" :class="{ active: is_active(page)}">
                <a href="#" class="page-link" @click="pageclick(page)">
                    <span aria-hidden="true">{{ page }}</span>
                    <span class="sr-only">Page {{ page }}</span>
                </a>
            </li>
            <li v-if="show.last" class="page-item disabled">
                <span class="page-link">&hellip;</span>
            </li>
            <li v-if="show.last" class="page-item">
                <a class="page-link" @click="pageclick(pagecount)">
                    <span aria-hidden="true">{{ pagecount }}</span>
                    <span class="sr-only">Page {{ pagecount }}</span>
                </a>
            </li>
            <li v-if="show.next" class="page-item">
                <a class="page-link" @click="pageclick(show.nextpage)">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next page</span>
                </a>
            </li>
        </ul>
    </nav>
</template>

<script setup>
    import { ref, reactive, defineProps, defineEmits, watch } from 'vue';

    // Number of pages to show either side of current
    const PAGES_EITHERSIDE = 4;

    const props = defineProps({
        totalrows: Number,
        perpage: Number,
    });

    const emit = defineEmits(['pagechange']);

    const show = reactive({
        previous: false,
        previouspage: 0,
        first: false,
        last: false,
        next: false,
        nextpage: 0
    });

    const pages = ref([]);
    const activepage = ref(1);
    const pagecount = ref(0);
    const showbar = ref(false);

    function is_active(page) {
        return page == activepage.value;
    }

    /**
     * Calculate the pages and various show options
     * given current page
     */
    function get_pages() {
        pagecount.value = Math.ceil(props.totalrows / props.perpage);
        showbar.value = pagecount.value > 1;
        let lower = activepage.value - PAGES_EITHERSIDE;
        if (lower < 1) {
            lower = 1;
        }
        let upper = activepage.value + PAGES_EITHERSIDE;
        if (upper > pagecount.value) {
            upper = pagecount.value;
        }
        show.previous = lower > 1;
        show.previouspage = activepage.value - 1;
        show.next = upper < pagecount.value;
        show.nextpage = activepage.value + 1;
        show.first = activepage.value > (PAGES_EITHERSIDE + 1);
        show.last = activepage.value < (pagecount.value - PAGES_EITHERSIDE);
        pages.value = [];
        for (let i = lower; i <= upper; i++) {
            pages.value.push(i);
        }

        emit('pagechange', activepage.value);
    }

    /**
     * Watch for number of rows changing (when data acquired)
     */
    watch(() => props.totalrows, () => {

        // if the total number of pages change, revert to first page
        activepage.value = 1;
        get_pages();
    });

    /**
     * Page number has been clicked
     */
    function pageclick(page) {
        activepage.value = page;
        get_pages();
    }
</script>
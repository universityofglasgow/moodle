<template>
    <div>
        <div v-if="!showdata" class="alert alert-primary">
            <MString name="noaudit"></MString>
        </div>
        <div v-if="showdata">
            <PagingBar :totalrows="totalrows" :perpage="perpage" @pagechange="pagechanged"></PagingBar>
            <table class="table table-striped">
                <thead class="thead-light">
                    <th><MString name="date"></MString></th>
                    <th><MString name="type"></MString></th>
                    <th><MString name="gradeitem"></MString></th>
                    <th><MString name="description"></MString></th>
                </thead>
                <tbody>
                    <tr v-for="item in pageditems" :key="item.id">
                        <td>{{ item.time }}</td>
                        <td><span class="badge" :class="'badge-' + item.bgcolor">{{ item.type }}</span></td>
                        <td>{{ item.gradeitem }}</td>
                        <td>{{ item.message }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
    import {ref, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import PagingBar from '@/components/PagingBar.vue';
    import MString from '@/components/MString.vue';

    const PAGESIZE = 20;

    const items = ref([]);
    const pageditems = ref([]);
    const totalrows = ref(0);
    const perpage = ref(PAGESIZE);
    const currentpage = ref(1);
    const showdata = ref(false);

    const toast = useToast();

    /**
     * filter out paged items
     */
     function get_pageditems() {
        const first = (currentpage.value - 1) * PAGESIZE;
        const last = first + PAGESIZE - 1;
        pageditems.value = [];
        for (let i=first; i<=last; i++) {
            if (items.value[i] != undefined) {
                pageditems.value.push(items.value[i]);
            }
        }
    }

    /**
     * Page selected on paging bar
     * @param int page
     */
     function pagechanged(page) {
        currentpage.value = page;
        get_pageditems();
    }

    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_audit',
            args: {
                courseid: courseid,
                userid: 0
            }
        }])[0]
        .then((result) => {
            items.value = result;
            totalrows.value = items.value.length;
            showdata.value = totalrows.value > 0;
            get_pageditems();
        })
        .catch((error) => {
            window.console.error(error);
            toast.error('Error communicating with server (see console)');
        })
    });
</script>
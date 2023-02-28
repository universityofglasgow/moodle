<template>
    <div class="mt-4 border px-3" v-if="loaded">
        <div v-html="treehtml"></div>
    </div>
</template>

<script setup>
    import {ref, onMounted, defineProps, watch} from 'vue';

    const props = defineProps({
        categoryid: Number,
    });

    const activitytree = ref({});
    const treehtml = ref('');
    const loaded = ref(false);

    /**
     * Build the tree
     * @param object tree 
     * @param int depth depth into tree
     */
    function build_tree(tree, depth=0) {

        let html = '';

        // name of this category
        if (depth) {
            html += '<a href="#"><b><i class="fa fa-list-alt" aria-hidden="true"></i> ' + tree.category.fullname + '</b></a><br />';
        } else {
            html += '<b><i class="fa fa-list-alt" aria-hidden="true"></i> ' + tree.category.fullname + '</b><br />';
        }

        // build activities bit
        html += '<ul>';
        tree.items.forEach((item) => {
            html += '<li>' + item.itemname + '</li>';
        });
        html += '</ul>';

        // build (sub) categories
        html += '<ul>';
        tree.categories.forEach((category) => {
            html += build_tree(category, depth+1);
        });
        html += '</ul>';

        return html;
    }

    // Get the sub-category / activity
    function getActivity() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;
        const catid = props.categoryid;

        fetchMany([{
            methodname: 'local_gugrades_get_activities',
            args: {
                courseid: courseid,
                categoryid: catid
            }
        }])[0]
        .then((result) => {
            const tree = JSON.parse(result['activities']);
            window.console.log(tree);

            activitytree.value = tree;
            treehtml.value = build_tree(tree);
            loaded.value = true;
        })
        .catch((error) => {
            window.console.log(error);
        })
    }

    onMounted(() => {
        getActivity();
    });

    watch(() => props.categoryid, () => {
        getActivity();
    })
</script>
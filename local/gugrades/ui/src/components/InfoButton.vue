<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <!-- info button -->
    <a href="#" class="ml-2"  @click="info_clicked" data-toggle="tooltip" :title="mstrings.gradeiteminfo">
        <span v-if="props.text" class="text-light"><u>{{ props.text }}</u></span>
        <i v-else class="fa fa-info-circle align-middle text-warning" :class="customclasses" aria-hidden="true"></i>
    </a>

    <!-- modal to show info-->
    <VueModal v-model="showinfomodal" :enableClose="false" modalClass="col-11 col-lg-5 rounded" :title="itemname">

        <table class="table">
            <tbody>
                <tr>
                    <th>{{ mstrings.name }}</th>
                    <td v-if="link == ''">{{ itemname }}</td>
                    <td v-else><a :href="link" target="_blank">{{ itemname }}</a></td>
                </tr>
                <tr>
                    <th>{{ mstrings.type }}</th>
                    <td>{{ itemtype }}</td>
                </tr>
                <tr>
                    <th>{{ mstrings.module }}</th>
                    <td>{{ itemmodule }}</td>
                </tr>
                <tr v-if="isscale">
                    <th>{{  mstrings.scale }}</th>
                    <td>{{ scalename }}</td>
                </tr>
                <tr v-if="!isscale && grademax">
                    <th>{{ mstrings.maxgrade }}</th>
                    <td>{{ grademax }}</td>
                </tr>
                <tr>
                    <th>{{ mstrings.weight }}</th>
                    <td>{{  weight }}</td>
                </tr>
                <tr v-if="categoryerror">
                    <div class="alert alert-warning">
                        {{ mstrings.categoryerror }}
                    </div>
                </tr>
            </tbody>
        </table>

        <button
            class="btn btn-warning"
            @click="showinfomodal = false"
            >{{ mstrings.close }}
        </button>
    </VueModal>
</template>

<script setup>
    import {ref, inject, defineProps, computed} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const showinfomodal = ref(false);
    const itemname = ref('');
    const itemtype = ref('');
    const itemmodule = ref('');
    const isscale = ref(false);
    const scalename = ref('');
    const grademax = ref(0);
    const weight = ref(0);
    const categoryerror = ref(false);
    const link = ref('');
    const debug = ref({});
    const mstrings = inject('mstrings');

    const props = defineProps({
        itemid: Number,
        size: String,
        color: String,
        text: String,
    });

    const toast = useToast();

    const customclasses = computed(() => {
        var sizeclass = '';
        var colorclass;
        if (props.size == '') {
            sizeclass = 'fa-sm';
        } else {
            sizeclass = 'fa-' + props.size;
        }

        if (props.color = '') {
            colorclass = 'text-primary';
        } else {
            colorclass = props.color;
        }

        return [sizeclass, colorclass];
    })

    /**
     * Info button clicked
     */
    function info_clicked() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_grade_item',
            args: {
                itemid: props.itemid,
            }
        }])[0]
        .then((result) => {
            itemname.value = result.itemname;
            itemtype.value = result.itemtype;
            itemmodule.value = result.itemmodule;
            isscale.value = result.isscale;
            scalename.value = result.scalename;
            grademax.value = result.grademax;
            weight.value = result.weight;
            categoryerror.value = result.categoryerror;
            link.value = result.link;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });

        showinfomodal.value = true;
    }

</script>
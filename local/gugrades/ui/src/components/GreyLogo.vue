<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div v-if="!loading" class="text-center pb-1">
        <img :src="url" id="mygradeslogo" alt="MyGrades Logo" :class="{monoimage: monochrome}"/>
    </div>
</template>

<script setup>
    import {ref, onMounted} from 'vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import { useLogo } from '@/js/monochromelogo.js';

    const debug = ref({});
    const url = ref('');
    const loading = ref(true);

    const {monochrome, updateLogo} = useLogo();

    function get_url() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        const images = [
            {
                imagename: 'MyGradesLogoSmall',
                component: 'local_gugrades',
            }
        ];

        fetchMany([{
            methodname: 'local_gugrades_get_image_urls',
            args: {
                courseid: courseid,
                images: images,
            }
        }])[0]
        .then((result) => {
            url.value = result[0]['url'];
            loading.value = false;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    onMounted(() => {
        get_url();
        updateLogo();
    });
</script>

<style>
    .monoimage {
        filter: grayscale(100);
    }
</style>
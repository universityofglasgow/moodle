<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <div>
        <h1>{{ mstrings.settings }}</h1>


        <FormKit type="form" @submit="submit_form">

            <div v-if="!gradesreleased" class="alert alert-warning">
                {{ mstrings.gradesnotreleased }}
            </div>

            <FormKit
                type="checkbox"
                :label="mstrings.disabledashboard"
                :disabled="!gradesreleased"
                v-model="disabledashboard"
                >
            </FormKit>

        </FormKit>

        <div class="mt-5">
            <ResetButton></ResetButton>
        </div>
    </div>
</template>

<script setup>
    import {ref, inject, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import ResetButton from '@/components/ResetButton.vue';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import { useLogo } from '@/js/monochromelogo.js';

    const mstrings = inject('mstrings');
    const disabledashboard = ref(false);
    const gradesreleased = ref(true);

    const toast = useToast();
    const {setmonochrome, updateLogo} = useLogo();

    /**
     * Submit button clicked
     */
    function submit_form() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_save_settings',
            args: {
                courseid: courseid,
                gradeitemid: 0,
                settings: [
                    {
                        name: 'disabledashboard',
                        value: disabledashboard.value,
                    },
                ]
            }
        }])[0]
        .then(() => {
            updateLogo();
            toast.success(mstrings.settingssaved);
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Load initial page
     */
    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        updateLogo();

        fetchMany([{
            methodname: 'local_gugrades_get_settings',
            args: {
                courseid: courseid,
                gradeitemid: 0,
            }
        }])[0]
        .then((settings) => {
            settings.forEach((setting) => {

                // TODO: Something a bit cleverer than this
                if (setting.name == 'disabledashboard') {
                    disabledashboard.value = setting.value ? true : false;
                }
            })
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    })

</script>
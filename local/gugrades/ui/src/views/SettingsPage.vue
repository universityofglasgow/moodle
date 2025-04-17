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

    const mstrings = inject('mstrings');
    const disabledashboard = ref(false);
    const gradesreleased = ref(true);

    const toast = useToast();

    /**
     * Get current state of dashboard enabled/disabled
     */
    function get_dashboard_enabled() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_dashboard_enabled',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            gradesreleased.value = result.gradesreleased;
            const enabled = result.enabled;

            // Bodge to get jQuery needed for Bootstrap JS.
            const $ = window.jQuery;

            if (enabled) {
                $('#mygradeslogo').css('filter', 'grayscale(0)');
            } else {
                $('#mygradeslogo').css('filter', 'grayscale(1)');
            }
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

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
            get_dashboard_enabled();
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

        get_dashboard_enabled();

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
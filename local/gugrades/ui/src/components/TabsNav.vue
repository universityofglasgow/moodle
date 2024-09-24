<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded mb-2">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item" :class="{active: activetab == 'capture'}">
                    <a class="nav-link" role="button" @click="clickTab('capture')">
                        <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.assessmentgradecapture }}
                    </a>
                </li>
                <li class="nav-item" :class="{active: activetab == 'conversion'}">
                    <a class="nav-link" role="button" @click="clickTab('conversion')">
                        <i class="fa fa-exchange" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.manageconversion }}
                    </a>
                </li>
                <li class="nav-item" v-if="props.viewaggregation" :class="{active: activetab == 'aggregation'}">
                    <a class="nav-link" role="button" @click="clickTab('aggregation')">
                        <i class="fa fa-compress" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.coursegradeaggregation }}
                    </a>
                </li>
                <li class="nav-item" :class="{active: activetab == 'audit'}">
                    <a class="nav-link" role="button" @click="clickTab('audit')">
                        <i class="fa fa-history" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.auditlog }}
                    </a>
                </li>
                <li class="nav-item" v-if="settingscapability" :class="{active: activetab == 'settings'}">
                    <a class="nav-link" role="button" @click="clickTab('settings')">
                        <i class="fa fa-cog" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.settings }}
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script setup>
    import {ref, defineEmits, defineProps, inject, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import DebugDisplay from '@/components/DebugDisplay.vue';

    const activetab = ref('capture');
    const settingscapability = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');

    const props = defineProps({
        viewaggregation: Boolean,
    });

    const toast = useToast();

    const emit = defineEmits(['tabchange']);

    /**
     * Detect change of tab and emit result to parent
     * @param {} item
     */
    function clickTab(item) {
        activetab.value = item;
        emit('tabchange', item);
    }

    /**
     * Check capability
     */
     onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_has_capability',
            args: {
                courseid: courseid,
                capability: 'local/gugrades:changesettings'
            }
        }])[0]
        .then((result) => {
            settingscapability.value = result['hascapability'];
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });

    });
</script>

<style>
    .navbar-dark .navbar-nav .active > .nav-link {
        font-weight: bold;
    }

    .navbar-dark .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.7);
    }
</style>
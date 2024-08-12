<template>
    <div id="tabsnav">
        <ul class="nav nav-pills mb-4 border-bottom">
            <li class="nav-item">
                <a class="nav-link btn btn-secondary" :class="{active: activetab == 'capture'}" @click="clickTab('capture')">
                    <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
                    {{ mstrings.assessmentgradecapture }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-secondary" :class="{active: activetab == 'conversion'}" @click="clickTab('conversion')">
                    <i class="fa fa-exchange" aria-hidden="true"></i>&nbsp;
                    {{ mstrings.conversion }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-secondary" :class="{active: activetab == 'aggregate'}" @click="clickTab('aggregate')">
                    <i class="fa fa-compress" aria-hidden="true"></i>&nbsp;
                    {{ mstrings.coursegradeaggregation }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-secondary" :class="{active: activetab == 'audit'}" @click="clickTab('audit')">
                    <i class="fa fa-history" aria-hidden="true"></i>&nbsp;
                    {{ mstrings.auditlog }}
                </a>
            </li>
            <li class="nav-item" v-if="settingscapability">
                <a class="nav-link btn btn-secondary" :class="{active: activetab == 'settings'}" @click="clickTab('settings')">
                    <i class="fa fa-cog" aria-hidden="true"></i>&nbsp;
                    {{ mstrings.settings }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script setup>
    import {ref, defineEmits, inject, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";

    const activetab = ref('capture');
    const settingscapability = ref(false);
    const mstrings = inject('mstrings');

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
            toast.error('Error communicating with server (see console)');
        });

    });
</script>
<template>
    <DebugDisplay :debug="debug"></DebugDisplay>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded mb-2">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item" :class="{active: activetab == 'configure'}">
                    <a class="nav-link" role="button" @click="clickTab('configure')" @keyup.enter="clickTab('configure')" @keyup.left="handleKeyNavigation('settings', $event)" @keyup.right="handleKeyNavigation('capture', $event)" name="configure" :aria-label="mstrings.configure" tabindex="0">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.configure }}
                    </a>
                </li>
                <li class="nav-item" :class="{active: activetab == 'capture'}">
                    <a class="nav-link" role="button" @click="clickTab('capture')" @keyup.enter="clickTab('capture')" @keyup.left="handleKeyNavigation('configure', $event)" @keyup.right="handleKeyNavigation('conversion', $event)" name="capture" :aria-label="mstrings.assessmentgradecapture" tabindex="0">
                        <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.assessmentgradecapture }}
                    </a>
                </li>
                <li class="nav-item" :class="{active: activetab == 'conversion'}">
                    <a class="nav-link" role="button" @click="clickTab('conversion')" @keyup.enter="clickTab('conversion')" @keyup.left="handleKeyNavigation('capture', $event)" @keyup.right="handleKeyNavigation('aggregation', $event)" name="conversion" :aria-label="mstrings.manageconversion" tabindex="0">
                        <i class="fa fa-exchange" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.manageconversion }}
                    </a>
                </li>
                <li class="nav-item" v-if="props.viewaggregation" :class="{active: activetab == 'aggregation'}">
                    <a class="nav-link" role="button" @click="clickTab('aggregation')" @keyup.enter="clickTab('aggregation')" @keyup.left="handleKeyNavigation('conversion', $event)" @keyup.right="handleKeyNavigation('audit', $event)" name="aggregation" :aria-label="mstrings.coursegradeaggregation" tabindex="0">
                        <i class="fa fa-compress" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.coursegradeaggregation }}
                    </a>
                </li>
                <li class="nav-item" :class="{active: activetab == 'audit'}">
                    <a class="nav-link" role="button" @click="clickTab('audit')" @keyup.enter="clickTab('audit')" @keyup.left="handleKeyNavigation('aggregation', $event)" @keyup.right="handleKeyNavigation('settings', $event)" name="audit" :aria-label="mstrings.auditlog" tabindex="0">
                        <i class="fa fa-history" aria-hidden="true"></i>&nbsp;
                        {{ mstrings.auditlog }}
                    </a>
                </li>
                <li class="nav-item" v-if="settingscapability" :class="{active: activetab == 'settings'}">
                    <a class="nav-link" role="button" @click="clickTab('settings')" @keyup.enter="clickTab('settings')" @keyup.left="handleKeyNavigation('audit', $event)" @keyup.right="handleKeyNavigation('capture', $event)" name="settings" :aria-label="mstrings.settings" tabindex="0">
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

    const activetab = ref('configure');
    const settingscapability = ref(false);
    const debug = ref({});
    const mstrings = inject('mstrings');
    let whichtableft = '';
    let whichtabright = '';

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
     * Give focus to the element to the left of the current one.
     * Unless of course the user doesn't have permission to do so.
     *
     * @param elemname
     */
    function moveLeft(elemname) {
        let el = '';
        switch (elemname) {
            case 'settings':
                el = whichtableft;
            break;
            case 'aggregation':
                el =  ((props.viewaggregation) ? 'aggregation' : 'conversion');
            break;
            default:
                el = elemname;
            break;
        }
        let tmp = document.getElementsByName(el);
        tmp[0].focus({ focusVisible:true });
    }

    /**
     * Give focus to the element to the right of the current one.
     * Unless of course the user doesn't have permission to do so.
     *
     * @param elemname
     */
    function moveRight(elemname) {
        let el = '';
        switch (elemname) {
            case 'settings':
                el = whichtabright;
            break;
            case 'aggregation':
                el =  ((props.viewaggregation) ? 'aggregation' : 'audit');
            break;
            default:
                el = elemname;
            break;
        }
        let tmp = document.getElementsByName(el);
        tmp[0].focus({ focusVisible:true });
    }

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
            whichtableft = ((settingscapability.value) ? 'settings' : 'audit');
            whichtabright = ((settingscapability.value) ? 'settings' : 'capture');
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
        text-decoration: underline;
    }

    .navbar-dark .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.7);
    }
</style>
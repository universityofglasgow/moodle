<template>
    <div class="mt-4">
        <div>
            <div>
                <InitialBar :selected="first" :label="strings.firstname" @selected="first_selected"></InitialBar>
                <InitialBar :selected="last" :label="strings.lastname" @selected="last_selected"></InitialBar>
            </div>
        </div>
        <div v-if="showreset">
            <button class="btn btn-primary btn-small" @click="reset_filter">{{ strings.resetfilter }}</button>
        </div>
    </div>
</template>

<script setup>
    import {ref, defineEmits, onMounted, defineExpose} from '@vue/runtime-core';
    import InitialBar from '@/components/InitialBar.vue';
    import { getstrings } from '@/js/getstrings.js';

    const emit = defineEmits(['selected']);

    const first = ref('all');
    const last = ref('all');
    const strings = ref({});
    const showreset = ref(false);

    defineExpose({
        reset_filter,
    });

    /**
     * Process letter selected in one of the bars
     */
    function first_selected(letter) {
        first.value = letter;
        showreset.value = (first.value != 'all') || (last.value != 'all');
        emit('selected', first.value, last.value);
    }

    function last_selected(letter) {
        last.value = letter;
        showreset.value = (first.value != 'all') || (last.value != 'all');
        emit('selected', first.value, last.value);
    }

    /**
     * Reset filter back to all/all
     */
    function reset_filter() {
        first.value = 'all';
        last.value = 'all';
    }

    onMounted(() => {
        const stringslist = [
            'firstname',
            'lastname',
            'resetfilter'
        ];

        getstrings(stringslist)
        .then(results => {
            Object.keys(results).forEach((name) => {strings.value[name] = results[name]});
        });
    })
</script>
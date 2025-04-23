<template>
    <div class="initialbar d-flex flex-wrap justify-content-center justify-content-md-start">
        <span class="initialbarlabel mr-2">{{ props.label }}</span>

        <nav class="initialbargroups d-flex flex-wrap justify-content-center justify-content-md-start">
            <ul class="pagination pagination-sm">
                <li class="initialbarall page-item" :class="{active: is_active('all')}">
                    <a data-initial="" class="page-link" href="#" @click="letterclicked('all', $event)">{{ mstrings.all }}</a>
                </li>
            </ul>
            <ul class="pagination pagination-sm">
                <li v-for="letter in letters1" :key="letter" class="page-item" :class="{active: is_active(letter)}">
                    <a class="page-link" href="#" @click="letterclicked(letter, $event)">{{letter}}</a>
                </li>
            </ul>
            <ul class="pagination pagination-sm">
                <li v-for="letter in letters2" :key="letter" class="page-item" :class="{active: is_active(letter)}">
                    <a class="page-link" href="#" @click="letterclicked(letter, $event)">{{letter}}</a>
                </li>
            </ul>            
        </nav>
    </div>    
</template>

<script setup>
    import {ref, computed, defineProps, defineEmits, watch, inject} from '@vue/runtime-core';

    const props = defineProps({
        'label': String,
        'selected': String
    });

    const emit = defineEmits(['selected']);

    const activeletter = ref('all');
    const mstrings = inject('mstrings');

    const letters1 = computed(() => {
        return Array.from("ABCDEFGHIJKLM");
    });

    const letters2 = computed(() => {
        return Array.from("NOPQRSTUVWXYZ");
    });    

    function letterclicked(letter, event) {
        event.preventDefault();
        activeletter.value = letter;
        emit('selected', letter);
    }

    function is_active(letter) {
        return activeletter.value == letter;
    }

    watch(() => props.selected, (selected) => {
        activeletter.value = selected;
        emit('selected', activeletter.value);
    })
</script>
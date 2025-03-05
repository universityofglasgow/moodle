<template>
    <div id="staffview">
        <h1>{{ mstrings.studentmygradesstaffview }}</h1>
    </div>

    <div id="staffview_summary">
        {{ mstrings.staffview_summary }}
    </div>

    <div id="studentmygradesstaffview_container">
        <MainView />
    </div>
</template>

<script setup>
    import { inject } from 'vue';
    import MainView from '../src/views/MainView.vue';

    const mstrings = inject('mstrings');
</script>

<style scoped>
    body#studentmygradesstaffview_container {
        background:#ffffff !important;
    }
</style>

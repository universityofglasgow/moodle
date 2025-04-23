<template>
    <span>
        {{ moodlestring }}
    </span>
</template>

<script setup>
    import {ref, onMounted, defineProps} from 'vue';

    const props = defineProps({
        name: String,
        component: String
    })

    const moodlestring = ref('');

    onMounted(() => {
        const GU = window.GU;

        // Default component is local_gugrades (this one)
        const finalcomponent = (!props.component) ? 'local_gugrades' : props.component;

        const strings = [
            {
                key: props.name,
                component: finalcomponent
            }
        ];
        GU.getStrings(strings)
        .then(result => {
            moodlestring.value = result[0];
        });
    });
</script>

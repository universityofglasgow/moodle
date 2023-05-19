<template>
    <img v-if="loaded" :src="url" :alt="fullname" class="userpicture defaultuserpic" width="35" height="35"/>
</template>

<script setup>
    import {ref, onMounted, defineProps} from 'vue';

    const props = defineProps({
        userid: Number,
        fullname: String
    })

    const url = ref('');
    const loaded = ref(false);

    onMounted(() => {
        const GU = window.GU;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_user_picture_url',
            args: {
                userid: props.userid
            }
        }])[0]
        .then((result) => {
            url.value = result.url;
            loaded.value = true;
        })
        .catch((error) => {
            window.console.log(error);
        });
    });
</script>
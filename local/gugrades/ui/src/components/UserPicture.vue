<template>
    <a :href="profileurl">
        <img v-if="loaded" :src="url" :alt="fullname" class="userpicture defaultuserpic" width="35" height="35"/>
    </a>
</template>

<script setup>
    import {ref, onMounted, defineProps} from 'vue';

    const props = defineProps({
        userid: Number,
        fullname: String
    })

    const url = ref('');
    const profileurl = ref('');
    const loaded = ref(false);

    onMounted(() => {
        const GU = window.GU;
        const fetchMany = GU.fetchMany;
        const courseid = GU.courseid;

        fetchMany([{
            methodname: 'local_gugrades_get_user_picture_url',
            args: {
                courseid: courseid,
                userid: props.userid
            }
        }])[0]
        .then((result) => {
            url.value = result.url;
            profileurl.value = result.profileurl;
            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
        });
    });
</script>
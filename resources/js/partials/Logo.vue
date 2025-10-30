<template>
    <img :class="height" :src="`/images/logo-black-transparent.png`" :alt="`${appName} Logo`">
</template>

<script>
export default {
    props: {
        height: {
            type: String,
            default: 'h-16'
        }
    },
    data() {
        return {
            appName: import.meta.env.VITE_APP_NAME
        }
    }
};
</script>

<template>
    <div :class="['absolute top-0 left-0 right-0 bottom-0 z-10', bgClass, { 'border border-dashed border-blue-300' : showBorder }]">
        <div v-if="showSpinningLoader" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-20">
            <Loader></Loader>
        </div>
    </div>
</template>

  <script>

    import Loader from '@Partials/Loader.vue';

    export default {
        components: { Loader },
        props: {
            bgClass: {
                type: String,
                default: 'bg-white/50'
            },
            showBorder: {
                type: Boolean,
                default: true
            },
            showSpinningLoader: {
                type: Boolean,
                default: true
            },
        }
    };

  </script>

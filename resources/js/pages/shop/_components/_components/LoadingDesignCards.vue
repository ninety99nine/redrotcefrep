<template>

    <div class="pt-8 flex items-center justify-center">
        <Loader>
            <span class="text-sm ml-2">Loading store</span>
        </Loader>
    </div>

</template>

<script>

    import Loader from '@Partials/Loader.vue';

    export default {
        props: {
            type: {
                type: String
            }
        },
        components: { Loader }
    }
</script>

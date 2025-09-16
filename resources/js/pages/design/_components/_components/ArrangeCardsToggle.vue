<template>

    <Switch
        size="xs"
        suffixText="Change arrangement"
        v-model="designState.wantsToArrangeDesignCards">
    </Switch>

</template>

<script>

    import Switch from '@Partials/Switch.vue';

    export default {
        inject: ['designState'],
        components: { Switch },
        props: {
            type: {
                type: String
            }
        }
    }
</script>

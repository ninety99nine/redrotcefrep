<template>

    <div class="max-w-4xl mx-auto pb-20">

        <Menu menuClass="max-w-4xl mx-auto"></Menu>
        <DesignCardManager placement="storefront"></DesignCardManager>

    </div>

</template>

<script>

    import Menu from '@Pages/shop/_components/menu/Menu.vue';
    import DesignCardManager from '@Pages/shop/_components/design-card-manager/DesignCardManager.vue';

    export default {
        components: {
            Menu, DesignCardManager
        }
    }
</script>

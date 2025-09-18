<template>

    <Drawer
        position="left"
        ref="myCartDrawer"
        :showFooter="false"
        :scrollOnContent="false"
        targetClass="mockup-phone">

        <template #trigger="props">

            <Button
                size="xs"
                type="outline"
                :leftIcon="Menu"
                leftIconSize="18"
                :action="props.showDrawer">
            </Button>

        </template>

        <template #content>

            <h3 class="font-semibold text-gray-900 border-b border-gray-200 p-4">Menu</h3>

            <DesignCardManager placement="menu"></DesignCardManager>

        </template>

    </Drawer>

</template>

<script>

    import { Menu } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Drawer from '@Partials/Drawer.vue';
    import DesignCardManager from '@Pages/shop/_components/design-card-manager/DesignCardManager.vue';

    export default {
        components: {
            Button, Drawer, DesignCardManager
        },
        data() {
            return {
                Menu
            }
        }
    }
</script>

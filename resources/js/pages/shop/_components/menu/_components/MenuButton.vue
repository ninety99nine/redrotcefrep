<template>

    <Drawer
        position="left"
        contentClass=""
        ref="menuDrawer"
        :showFooter="false"
        :closeOnX="closeOnX"
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
        inject: ['notificationState'],
        components: {
            Button, Drawer, DesignCardManager
        },
        data() {
            return {
                Menu
            }
        },

        watch: {
            //  Track isDesigningMenu while swithing between 'edit-storefront' and 'edit-menu'
            //  since both use the same vue-router component for preview on the simulator:
            //  see: resources/js/router/index.js and notice the following line:
            //  preview: () => import('@Pages/shop/storefront/Storefront.vue').

            isDesigningMenu(newValue) {
                if(newValue) {
                    this.$refs.menuDrawer.showDrawer();
                }else{
                    this.$refs.menuDrawer.hideDrawer();
                }
            }
        },
        computed: {
            isDesigningMenu() {
                return this.$route.name == 'edit-menu';
            }
        },
        methods: {
            closeOnX() {
                if(this.isDesigningMenu) {
                    this.notificationState.showSuccessNotification(`Only closes on the actual store`);
                }else{
                    this.$refs.menuDrawer.hideDrawer();
                }
            }
        },
        mounted() {
            if(this.isDesigningMenu) {
                this.$refs.menuDrawer.showDrawer();
            }
        }
    }
</script>

<template>

    <aside v-if="storeMode" class="select-none fixed top-0 left-0 z-20 w-64 h-screen pt-16 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0">

        <div class="h-full overflow-y-auto bg-white">

            <div class="font-medium">

                <template v-if="isShowingSettings">

                    <div class="flex justify-center my-4">

                        <!-- Return to dashboard -->
                        <Button
                            size="sm"
                            type="light"
                            :leftIcon="MoveLeft"
                            buttonClass="w-full"
                            :action="navigateToHome"
                            :skeleton="isLoadingStore">
                            <span class="ml-1">Return to dashboard</span>
                        </Button>

                    </div>

                </template>

                <template
                    :key="index"
                    v-for="(navMenu, index) in navMenus">

                    <div
                        @click.stop="() => navMenuHasChildren(navMenu) ? toggleNavMenuExpansion(index) : navigateToNavRoute(navMenu)"
                        :class="[{ 'bg-gray-100' : isActiveNavMenu(navMenu) && !navMenuHasChildren(navMenu) }, 'cursor-pointer py-2.5 px-4 text-gray-900 hover:bg-gray-100 group']">

                        <div class="flex items-center text-gray-700">

                            <Skeleton v-if="isLoadingStore" width="w-5" height="h-5" :shine="false"></Skeleton>

                            <!-- Home Icon -->
                            <House v-else-if="navMenu.name == 'Home'" size="20"></House>

                            <!-- Orders Icon -->
                            <Inbox v-else-if="navMenu.name == 'Orders'" size="20"></Inbox>

                            <!-- Products Icon -->
                            <Box v-else-if="navMenu.name == 'Products'" size="20"></Box>

                            <!-- Customers Icon -->
                            <UserRound v-else-if="navMenu.name == 'Customers'" size="20"></UserRound>

                            <!-- Promotions Icon -->
                            <TicketPercent v-else-if="navMenu.name == 'Promotions'" size="20"></TicketPercent>

                            <!-- Marketing Icon -->
                            <Megaphone v-else-if="navMenu.name == 'Marketing'" size="20"></Megaphone>

                            <!-- Design Icon -->
                            <WandSparkles v-else-if="navMenu.name == 'Design'" size="20"></WandSparkles>

                            <!-- Reviews Icon -->
                            <Star v-else-if="navMenu.name == 'Reviews'" size="20"></Star>

                            <!-- Analytics Icon -->
                            <ChartArea v-else-if="navMenu.name == 'Analytics'" size="20"></ChartArea>

                            <!-- Transactions Icon -->
                            <Banknote v-else-if="navMenu.name == 'Transactions'" size="20"></Banknote>

                            <!-- Pages Icon -->
                            <Files v-else-if="navMenu.name == 'Pages'" size="20"></Files>

                            <!-- Offline Store Icon -->
                            <Smartphone v-else-if="navMenu.name == 'Offline Store'" size="20"></Smartphone>

                            <!-- Subscriptions Icon -->
                            <Rocket v-else-if="navMenu.name == 'Subscriptions'" size="20"></Rocket>

                            <!-- Settings Icon -->
                            <Settings v-else-if="navMenu.name == 'Settings'" size="20"></Settings>

                            <Skeleton v-if="isLoadingStore" width="w-24" :shine="true" class="ms-3"></Skeleton>
                            <span v-else :class="[{ 'text-gray-900' : isActiveNavMenu(navMenu) }, 'w-full text-sm ms-3 group-hover:text-gray-900']">{{ navMenu.name }}</span>

                            <div v-if="navMenuHasChildren(navMenu)">
                                <ChevronUp v-if="navMenu.expanded" size="16"></ChevronUp>
                                <ChevronDown v-else size="16"></ChevronDown>
                            </div>

                        </div>

                    </div>

                    <VueSlideUpDown v-if="navMenuHasChildren(navMenu)" :active="navMenuHasChildren(navMenu) && hasExpandedNavMenu(navMenu)">
                        <template
                            :key="index2"
                            v-for="(navMenuChild, index2) in navMenu.children">

                            <div
                                @click.stop="navigateToNavRoute(navMenuChild, index)"
                                :class="[{ 'bg-gray-100' : isActiveNavMenu(navMenuChild) }, 'cursor-pointer pl-8 py-1 text-gray-900 hover:bg-gray-100 group']">

                                <span :class="[{ 'text-gray-900' : isActiveNavMenu(navMenuChild) }, 'font-normal text-sm ms-3 text-gray-500 group-hover:text-gray-900']">{{ navMenuChild.name }}</span>

                            </div>

                        </template>
                    </VueSlideUpDown>

                    <div v-if="['Team Members', 'Subscriptions'].includes(navMenu.name)" class="border-t border-gray-300 border-dashed pt-2 mt-2"></div>

                </template>

            </div>

        </div>

    </aside>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import VueSlideUpDown from 'vue-slide-up-down';
    import { House, Inbox, Box, UserRound, TicketPercent, Megaphone, WandSparkles, Star, ChartArea, Banknote, Files, Smartphone, Rocket, Settings, ChevronUp, ChevronDown, MoveLeft } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            House, Inbox, Box, UserRound, TicketPercent, Megaphone, WandSparkles, Star,
            ChartArea, Banknote, Files, Smartphone, Rocket, Settings, ChevronUp, ChevronDown,
            Button, Skeleton, VueSlideUpDown
        },
        props: {
            storeMode: {
                type: Boolean
            },
            isLoadingStore: {
                type: Boolean
            }
        },
        data() {
            return {
                MoveLeft,
                navMenus: [],
            }
        },
        watch: {
            '$route'() {
                this.navMenus = this.buildNavMenus();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isShowingSettings() {
                return this.$route.meta.settings === true;
            }
        },
        methods: {
            navigateToHome() {
                this.$router.push({
                    name: 'show-store-home',
                    params: { store_id: this.store.id }
                })
            },
            isActiveNavMenu(navMenu) {
                const routeNames = [navMenu.routeName];
                if(navMenu.hasOwnProperty('associatedRouteNames')) routeNames.push(...navMenu.associatedRouteNames);
                return routeNames.includes(this.$route.name);
            },
            hasActiveNavMenuChildren(navMenu) {
                return this.navMenuHasChildren(navMenu) && navMenu.children.some(subNavMenu => this.isActiveNavMenu(subNavMenu));
            },
            navMenuHasChildren(navMenu) {
                return (navMenu.children ?? []).length
            },
            hasExpandedNavMenu(navMenu) {
                return navMenu.expanded;
            },
            expandNavMenu(index) {
                for (let i = 0; i < this.navMenus.length; i++) {
                    if(i == index) {
                        this.navMenus[i].expanded = true;
                    }else{
                        this.navMenus[i].expanded = false;
                    }
                }
            },
            toggleNavMenuExpansion(index) {
                for (let i = 0; i < this.navMenus.length; i++) {
                    if(i == index) {
                        this.navMenus[i].expanded = !this.navMenus[i].expanded;
                    }else{
                        this.navMenus[i].expanded = false;
                    }
                }
            },
            collapseAllNavMenus() {
                for (let i = 0; i < this.navMenus.length; i++) {
                    this.navMenus[i].expanded = false;
                }
            },
            navigateToNavRoute(navMenu, index = null) {
                if(index == null) {
                    this.collapseAllNavMenus();
                }else{
                    this.expandNavMenu(index);
                }

                if(['show-store-home'].includes(navMenu.routeName)) {
                    this.$router.push({
                        name: navMenu.routeName,
                        params: { store_id: this.store.id }
                    })
                }else{
                    this.$router.push({
                        name: navMenu.routeName,
                        query: { store_id: this.store.id }
                    })
                }
            },
            buildNavMenus() {

                let navMenus;

                if(this.isShowingSettings) {

                    navMenus = [
                        {
                            name: 'Store Settings',
                            routeName: 'show-general-settings'
                        },
                        {
                            name: 'Payment Methods',
                            routeName: 'show-payment-methods',
                            associatedRouteNames: ['add-payment-method', 'edit-payment-method'],
                        },
                        {
                            name: 'Delivery Methods',
                            routeName: 'show-delivery-methods',
                            associatedRouteNames: ['add-delivery-method', 'edit-delivery-method'],
                        },
                        {
                            name: 'Workflows',
                            routeName: 'show-workflows',
                            associatedRouteNames: ['add-workflow', 'edit-workflow'],
                        },
                        {
                            name: 'Checkout',
                            routeName: 'show-checkout-settings',
                        },
                        {
                            name: 'Domains',
                            routeName: 'show-domains',
                            associatedRouteNames: ['edit-domain', 'buy-domain', 'add-domain'],
                        },
                        {
                            name: 'Account',
                            routeName: 'show-account-settings',
                        },
                        {
                            name: 'Billing',
                            routeName: 'show-billing-settings',
                        },
                        {
                            name: 'Team',
                            routeName: 'show-team-members',
                            associatedRouteNames: ['edit-team-member', 'add-team-member'],
                        },
                        {
                            name: 'SEO',
                            routeName: 'show-seo-settings',
                        }
                    ];

                }else{

                    navMenus = [
                        {
                            name: 'Home',
                            routeName: 'show-store-home'
                        },
                        {
                            name: 'Orders',
                            routeName: 'show-orders',
                            associatedRouteNames: ['show-order', 'create-order'],
                        },
                        {
                            name: 'Products',
                            children: [
                                {
                                    name: 'All',
                                    routeName: 'show-products',
                                    associatedRouteNames: ['create-product', 'edit-product']
                                },
                                {
                                    name: 'Tags',
                                    routeName: 'show-product-tags',
                                    associatedRouteNames: ['create-product-tag', 'edit-product-tag']
                                },
                                {
                                    name: 'Categories',
                                    routeName: 'show-categories',
                                    associatedRouteNames: ['create-categories', 'edit-category']
                                },
                                {
                                    name: 'Import',
                                    routeName: 'import-products'
                                },
                                {
                                    name: 'Bulk Edit',
                                    routeName: 'bulk-edit-products'
                                },
                            ]
                        },
                        {
                            name: 'Customers',
                            children: [
                                {
                                    name: 'All',
                                    routeName: 'show-customers',
                                    associatedRouteNames: ['create-customer', 'edit-customer']
                                },
                                {
                                    name: 'Tags',
                                    routeName: 'show-customer-tags',
                                    associatedRouteNames: ['create-customer-tag', 'edit-customer-tag']
                                },
                                {
                                    name: 'Import',
                                    routeName: 'import-customers'
                                },
                                {
                                    name: 'Bulk Edit',
                                    routeName: 'bulk-edit-customers'
                                },
                            ]
                        },
                        {
                            name: 'Promotions',
                            children: [
                                {
                                    name: 'All',
                                    routeName: 'show-promotions',
                                    associatedRouteNames: ['create-promotion', 'edit-promotion']
                                },
                                {
                                    name: 'Import',
                                    routeName: 'import-promotions'
                                },
                                {
                                    name: 'Bulk Edit',
                                    routeName: 'bulk-edit-promotions'
                                },
                            ]
                        },
                        {
                            name: 'Marketing',
                            routeName: 'show-marketing'
                        },
                        {
                            name: 'Reviews',
                            routeName: 'show-reviews',
                            associatedRouteNames: ['create-review', 'edit-review']
                        },
                        {
                            name: 'Analytics',
                            routeName: 'show-analytics'
                        },
                        {
                            name: 'Design',
                            routeName: 'edit-storefront',
                            associatedRouteNames: ['edit-checkout', 'edit-payment', 'edit-menu']
                        },
                        {
                            name: 'Settings',
                            routeName: 'show-general-settings'
                        }
                    ];

                }

                for (let index = 0; index < navMenus.length; index++) {

                    if(this.navMenuHasChildren(navMenus[index])) {
                        navMenus[index].expanded = this.hasActiveNavMenuChildren(navMenus[index]);
                    }

                }

                return navMenus;
            },
        },
        created() {
            this.navMenus = this.buildNavMenus();
        }
    };

</script>



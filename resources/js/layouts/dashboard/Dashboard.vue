<template>

    <div class="w-full bg-linear-to-b from-blue-100 to-blue-50 min-h-screen overflow-x-hidden">

        <!-- Notifications -->
        <Notifications></Notifications>

        <nav :class="['select-none fixed top-0 z-30 w-full', { 'bg-white border-b border-gray-200' : !isOnboarding }]">

            <div class="px-3 py-3 lg:px-5 lg:pl-3">

                <div class="flex items-center justify-between">

                    <div class="w-full flex items-center justify-start rtl:justify-end space-x-4">

                        <!-- Menu Toggle Button -->
                        <Button v-if="storeMode" type="light" :leftIcon="Menu" buttonClass="block md:hidden"></Button>

                        <div class="flex items-center space-x-4">

                            <template v-if="storeMode">

                                <!-- Store Logo -->
                                <Skeleton v-if="isLoadingStore" width="w-8" height="h-8" :shine="true"></Skeleton>
                                <StoreLogo v-else size="w-10 h-10" :showButton="false"></StoreLogo>

                                <!-- Store Name -->
                                <Skeleton v-if="isLoadingStore" width="w-32" :shine="true"></Skeleton>
                                <Select
                                    v-else
                                    class="w-48"
                                    :search="false"
                                    :options="storeOptions"
                                    v-model="selectedStoreId">

                                    <template #footer>

                                        <div class="border-t border-dashed border-gray-300 pt-2 m-2">

                                            <Button
                                                size="xs"
                                                type="light"
                                                :leftIcon="Plus"
                                                buttonClass="w-full"
                                                :action="navigateToCreateStore">
                                                <span class="ml-1">Create New Store</span>
                                            </Button>

                                        </div>

                                    </template>

                                </Select>

                                <!-- Visit Store Icon -->
                                <Skeleton v-if="isLoadingStore" width="w-8" :shine="true"></Skeleton>
                                <Button
                                    size="xs"
                                    type="light"
                                    :action="openWebLink"
                                    :leftIcon="ExternalLink">
                                    <span class="ml-1">visit</span>
                                </Button>

                            </template>

                            <template v-else-if="!isOnboarding">

                                <!-- Application Logo -->
                                <Logo
                                    height="h-10"
                                    @click.stop="navigateToShowStores"
                                    class="cursor-pointer hover:shadow-sm active:scale-95 transition-all duration-250">
                                </Logo>

                            </template>

                        </div>

                    </div>

                    <template v-if="changeHistoryState.hasChangeHistory || duplicateOrderId">

                        <ChangeHistoryNavigation></ChangeHistoryNavigation>

                    </template>

                    <template v-else>

                        <div
                            v-if="authUser && !isOnboarding"
                            class="flex w-full justify-center items-center">
                            <div
                                @click.stop="navigateToShowStores"
                                class="cursor-pointer animated-border-blue rounded-full overflow-hidden hover:shadow-sm active:scale-95 transition-all duration-250">
                                <h2 class="py-2 px-8 text-xs text-blue-500 bg-blue-50 font-semibold whitespace-nowrap truncate">
                                    Helping {{ authUser.first_name }} sell better
                                </h2>
                            </div>
                        </div>

                        <div class="w-full flex justify-end items-center space-x-8">

                            <div v-if="storeMode" class="flex items-center space-x-4">

                                <!-- Manage Stores -->
                                <Button :action="navigateToShowStores" type="light" size="sm" :skeleton="isLoadingStore" icon="refresh">
                                    <span>Manage Stores</span>
                                </Button>

                                <!-- Upgrade -->
                                <Button :action="navigateToPricingPlans" type="primary" size="sm" :skeleton="isLoadingStore" icon="rocket">
                                    <span>Upgrade</span>
                                </Button>

                            </div>

                            <div class="flex items-center ms-3">

                                <Dropdown
                                    position="left"
                                    dropdownClasses="w-72">

                                    <template #trigger="props">

                                        <div
                                            @click="props.toggleDropdown"
                                            class="cursor-pointer flex text-sm bg-gray-100 rounded-full focus:ring-4 focus:ring-gray-250">
                                            <div class="flex items-center justify-center w-8 h-8 border border-gray-300 text-gray-500 rounded-full p-2 hover:scale-110 transition-all duration-300">
                                                <UserRound size="16"></UserRound>
                                            </div>
                                        </div>

                                    </template>

                                    <template #content="props">

                                        <!-- Profile Menu -->
                                        <div class="max-h-60 overflow-auto">

                                            <div v-if="authUser" class="p-4 space-y-2 border-b border-gray-100" role="none">

                                                <!-- Name -->
                                                <p class="text-sm text-gray-900 font-medium truncate w-4/5" role="none">
                                                    {{ authUser.name }}
                                                </p>

                                                <!-- Email -->
                                                <p v-if="authUser.email" class="text-xs text-gray-500 truncate w-4/5" role="none">
                                                    {{ authUser.email }}
                                                </p>

                                                <!-- Mobile Number -->
                                                <p v-if="authUser.mobile_number" class="text-xs text-gray-500 truncate w-4/5" role="none">
                                                    {{ authUser.mobile_number.international }}
                                                </p>

                                            </div>

                                            <!-- Profile Menu Items -->
                                            <div class="py-1" role="none">

                                                <template
                                                    :key="index"
                                                    v-for="(navMenu, index) in profileNavMenus">

                                                    <div @click="(event) => navMenu.name == 'Sign Out' ? logout() : navMenu.action(props.toggleDropdown(event))" class="cursor-pointer flex space-x-2 items-center py-3 px-4 text-gray-900 hover:bg-gray-100 group">

                                                        <Loader v-if="navMenu.name == 'Sign Out' && isLoggingOut"></Loader>

                                                        <span class="text-sm text-gray-500 group-hover:text-gray-900">
                                                            {{ navMenu.name }}
                                                        </span>

                                                    </div>

                                                </template>

                                            </div>

                                        </div>

                                    </template>

                                </Dropdown>

                            </div>

                        </div>

                    </template>

                </div>

            </div>

        </nav>

        <aside v-if="storeMode" class="select-none fixed top-0 left-0 z-20 w-64 h-screen pt-16 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0">

            <div class="h-full overflow-y-auto bg-white">

                <div class="font-medium">

                    <template v-if="isShowingSettings">

                        <div class="flex items-center space-x-2 py-2.5 px-6 bg-yellow-100 text-black border-b border-gray-100 mb-4">
                            <Settings size="16"></Settings>
                            <span class="text-sm">Settings</span>
                        </div>

                        <div class="flex justify-center mb-4">

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

        <div :class="['relative', { 'sm:ml-64' : storeMode }]">

            <!-- Page Loader -->
            <transition v-if="storeMode" name="fade-loader">
                <div v-if="uiState.isLoading" class="absolute top-0 bottom-0 left-0 right-0 inset-0 bg-white bg-opacity-90 z-20">
                    <div class="absolute w-full flex justify-center items-center h-screen">
                        <Loader size="custom" customSize="w-8 h-8 border-4"></Loader>
                    </div>
                </div>
            </transition>

            <!-- Dashboard Content -->
            <router-view></router-view>

        </div>

    </div>

</template>

<script>

    import Logo from '@Partials/Logo.vue';
    import Loader from '@Partials/Loader.vue';
    import Select from '@Partials/Select.vue';
    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import VueSlideUpDown from 'vue-slide-up-down';
    import StoreLogo from '@Components/StoreLogo.vue';
    import Notifications from '@Layouts/dashboard/components/Notifications.vue';
    import ChangeHistoryNavigation from '@Layouts/dashboard/components/ChangeHistoryNavigation.vue';
    import { Plus, Box, Star, Menu, Files, Megaphone, House, Inbox, Rocket, LogOut, ChartArea, MoveLeft, UserRound, Banknote, Settings, ExternalLink, ChevronUp, ChevronDown, Smartphone, WandSparkles, TicketPercent } from 'lucide-vue-next';

    export default {
        inject: ['uiState', 'formState', 'authState', 'storeState', 'notificationState', 'changeHistoryState'],
        components: {
            Box, Star, Menu, Files, Megaphone, House, Inbox, Rocket, LogOut, ChartArea, UserRound, Banknote, Settings, ExternalLink, ChevronUp, ChevronDown, Smartphone, WandSparkles, TicketPercent,
            Logo, Loader, Select, Button, Dropdown, Skeleton, VueSlideUpDown, StoreLogo, Notifications, ChangeHistoryNavigation
        },
        data() {
            return {
                Plus,
                Menu,
                MoveLeft,
                stores: [],
                ExternalLink,
                navMenus: [],
                isLoadingStores: true,
                selectedStoreId: null,
                profileNavMenus: [
                    {
                        name: 'Manage Stores',
                        action: this.navigateToShowStores
                    },
                    {
                        name: 'Sign Out',
                        routeName: null,
                    }
                ],
                isLoggingOut: false
            }
        },
        watch: {
            storeId() {
                this.showStore();
                if(!this.hasStores) this.showStores();
            },
            '$route'() {
                this.navMenus = this.buildNavMenus();
            },
            selectedStoreId(newValue, oldValue) {
                if(oldValue) {
                    this.navigateToShowStoreHome(newValue);
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            authUser() {
                return this.authState.user;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeId() {
                return this.$route.params.store_id || this.$route.query.store_id;
            },
            storeMode() {
                return !this.isOnboarding && this.storeId;
            },
            isOnboarding() {
                return this.$route.meta.onboarding === true;
            },
            isShowingSettings() {
                return this.$route.meta.settings === true;
            },
            duplicateOrderId() {
                return this.$route.query.duplicate_order_id;
            },
            hasStores() {
                return this.stores.length > 0;
            },
            storeOptions() {
                return this.stores.map((store) => {
                    return {
                        value: store.id,
                        label: store.name
                    }
                });
            }
        },
        methods: {
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
            buildNavMenus() {

                let navMenus;

                if(this.isShowingSettings) {

                    navMenus = [
                        {
                            name: 'General',
                            routeName: 'show-general-settings'
                        },
                        {
                            name: 'Checkout',
                            routeName: 'show-checkout-settings',
                        },
                        {
                            name: 'Workflows',
                            routeName: 'show-workflows',
                            associatedRouteNames: ['add-workflow', 'edit-workflow'],
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
                        },
                        /*
                        {
                            name: 'Checkout',
                            routeName: 'show-store-checkout-settings'
                        },
                        {
                            name: 'Social Links',
                            routeName: 'show-store-social-settings'
                        },
                        */
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
                        },
                        /*
                        {
                            name: 'Promotions',
                            routeName: 'show-store-promotions',
                            associatedRouteNames: ['show-store-promotion', 'create-store-promotion'],
                        },
                        {
                            name: 'Reviews',
                            routeName: 'show-store-reviews',
                        },
                        {
                            name: 'Transactions',
                            routeName: 'show-store-transactions',
                        },
                        {
                            name: 'Team Members',
                            routeName: 'show-store-team-members',
                        },
                        {
                            name: 'Pages',
                            routeName: 'show-store-pages',
                            associatedRouteNames: ['show-store-page', 'create-store-page'],
                        },
                        {
                            name: 'Offline Store',
                            routeName: 'show-offline-store',
                        },
                        {
                            name: 'Subscriptions',
                            routeName: 'show-store-subscriptions',
                            associatedRouteNames: ['show-store-subscription']
                        }
                        */
                    ];

                }

                for (let index = 0; index < navMenus.length; index++) {

                    if(this.navMenuHasChildren(navMenus[index])) {
                        navMenus[index].expanded = this.hasActiveNavMenuChildren(navMenus[index]);
                    }

                }

                return navMenus;
            },
            navigateToShowStoreHome(storeId) {
                this.$router.push({
                    name: 'show-store-home',
                    params: { store_id: storeId }
                })
            },
            navigateToShowStores(toggleDropdown = null) {
                this.$router.push({
                    name: 'show-stores'
                });
                if(toggleDropdown) toggleDropdown();
            },
            navigateToCreateStore() {
                this.$router.push({
                    name: 'create-store',
                    query: { can_go_back: 1 }
                });
            },
            navigateToPricingPlans() {
                this.$router.push({
                    name: 'show-pricing-plans',
                    query: { store_id: this.store.id }
                })
            },
            navigateToHome() {
                this.$router.push({
                    name: 'show-store-home',
                    params: { store_id: this.store.id }
                })
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
            openWebLink() {
                if (this.store.web_link) {
                    window.open(this.store.web_link, '_blank');
                }
            },
            async showStores() {
                try {

                    this.isLoadingStores = true;

                    let config = {
                        params: {
                            association: 'team member'
                        }
                    };

                    const response = await axios.get('/api/stores', config);

                    this.pagination = response.data;
                    this.stores = this.pagination.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching stores';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch stores:', error);
                } finally {
                    this.isLoadingStores = false;
                }
            },
            async showStore(silentUpdate = false) {
                try {

                    if(!this.storeId) return;

                    this.selectedStoreId = this.storeId;

                    if(!silentUpdate) this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: [
                                'logo', 'seoImage', 'productTags', 'customerTags', 'categories',
                                'myMembership', 'activeSubscription.pricingPlan', 'address'
                            ].join(',')
                        }
                    };

                    const response = await axios.get(`/api/stores/${this.storeId}`, config);
                    this.storeState.setStore(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({ name: 'show-stores' });
                    }

                } finally {
                    if(!silentUpdate) this.storeState.isLoadingStore = false;
                }
            },
            async logout() {
                try {

                    this.isLoggingOut = true;
                    await axios.post('/api/auth/logout');

                    this.authState.unsetUser();
                    this.authState.unsetToken();
                    this.$router.replace({ name: 'login' });

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while signing out';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to sign out:', error);
                } finally {
                    this.isLoggingOut = false;
                }
            },
        },
        created() {
            this.showStores();
            if(this.storeId) this.showStore();
            this.navMenus = this.buildNavMenus();
            this.storeState.silentUpdate = () => this.showStore(true);
        }
    };

</script>

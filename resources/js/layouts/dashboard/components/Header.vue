<template>

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
                                    v-model="localSelectedStoreId">

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
                                    @click.stop="() => navigateToShowStores()"
                                    class="cursor-pointer hover:shadow-sm active:scale-95 transition-all duration-250">
                                </Logo>

                            </template>

                        </div>

                    </div>

                    <template v-if="changeHistoryState.showActionButtons || changeHistoryState.hasChangeHistory || duplicateOrderId">

                        <ChangeHistoryNavigation></ChangeHistoryNavigation>

                    </template>

                    <template v-else>

                        <!-- Search Input -->
                        <div
                            v-if="!isOnboarding"
                            class="relative w-full md:max-w-md group">

                            <!-- Native HTML input -->
                            <input
                                type="text"
                                @input="onSearch"
                                autocomplete="off"
                                spellcheck="false"
                                v-model="searchTerm"
                                placeholder="How can i help..."
                                class="w-full pl-12 pr-12 py-3 text-xs font-medium
                                    bg-white/90 backdrop-blur-xl
                                    border border-gray-200 rounded-2xl
                                    placeholder-gray-400 text-gray-800
                                    focus:outline-none focus:ring-4 focus:ring-blue-500/20
                                    focus:border-blue-400 focus:bg-white
                                    shadow-inner shadow-blue-500/5
                                    transition-all duration-300
                                    group-hover:bg-white group-hover:shadow-lg
                                    caret-blue-500"
                            />

                            <!-- Left: AI Icon + Badge -->
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center gap-2 pointer-events-none">
                                <div class="relative">
                                <!-- Subtle pulse glow -->
                                <div class="absolute -inset-1 rounded-lg bg-linear-to-br from-blue-500 to-purple-600
                                                blur-xl opacity-30 group-focus-within:opacity-50 animate-pulse"></div>
                                </div>
                                <span class="text-md font-bold bg-linear-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent hidden sm:inline">
                                    AI
                                </span>
                            </div>

                        </div>

                        <div class="w-full flex justify-end items-center space-x-4">

                            <!-- Upgrade -->
                            <Button
                                :leftIcon="Zap"
                                v-if="storeMode && !isLoadingStore && !activeSubscription"
                                :action="navigateToPricingPlans" type="primary" size="sm" :skeleton="isLoadingStore" icon="rocket">
                                <span>Upgrade</span>
                            </Button>

                            <div class="flex items-center">

                                <Dropdown
                                    position="left"
                                    dropdownClasses="w-72">

                                    <template #trigger="props">

                                        <div
                                            @click="props.toggleDropdown"
                                            class="flex items-center justify-center space-x-2 pl-4 cursor-pointer text-sm bg-gray-50 rounded-full focus:ring-4 focus:ring-gray-250">
                                            <span class="text-xs font-semibold text-gray-700">{{ authUser.first_name }}</span>
                                            <div class="flex items-center justify-center w-8 h-8 px-2 border border-gray-300 text-gray-500 rounded-full p-2 hover:scale-110 transition-all duration-300">
                                                <UserRound size="12"></UserRound>
                                            </div>
                                        </div>

                                    </template>

                                    <template #content="props">

                                        <!-- Profile Menu -->
                                        <div class="max-h-60 overflow-auto">

                                            <div
                                                v-if="authUser"
                                                class="p-4 space-y-2 border-b border-gray-100 cursor-pointer hover:bg-gray-100 group"
                                                @click="(event) => navigateToAccountSettings(() => props.toggleDropdown(event))">

                                                <!-- Name -->
                                                <p class="text-sm text-gray-900 font-medium truncate w-4/5">
                                                    {{ authUser.name }}
                                                </p>

                                                <!-- Email -->
                                                <p v-if="authUser.email" class="text-xs text-gray-500 truncate w-4/5">
                                                    {{ authUser.email }}
                                                </p>

                                                <!-- Mobile Number -->
                                                <p v-if="authUser.mobile_number" class="text-xs text-gray-500 truncate w-4/5">
                                                    {{ authUser.mobile_number.international }}
                                                </p>

                                            </div>

                                            <!-- Profile Menu Items -->
                                            <template
                                                :key="index"
                                                v-for="(navMenu, index) in profileNavMenus">

                                                <div @click="(event) => navMenu.name == 'Sign Out' ? logout() : navMenu.action(() => props.toggleDropdown(event))" class="cursor-pointer flex space-x-2 items-center py-2.5 px-4 text-gray-900 hover:bg-gray-100 group">

                                                    <Loader v-if="navMenu.name == 'Sign Out' && isLoggingOut"></Loader>

                                                    <span class="text-sm text-gray-500 group-hover:text-gray-900">
                                                        {{ navMenu.name }}
                                                    </span>

                                                </div>

                                            </template>

                                        </div>

                                    </template>

                                </Dropdown>

                            </div>

                        </div>

                    </template>

                </div>

            </div>

        </nav>

</template>

<script>

    import Logo from '@Partials/Logo.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Select from '@Partials/Select.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import { Zap, Plus, Menu, ExternalLink, UserRound } from 'lucide-vue-next';
    import ChangeHistoryNavigation from '@Layouts/dashboard/components/ChangeHistoryNavigation.vue';

    export default {
        inject: ['storeState', 'authState', 'formState', 'notificationState', 'changeHistoryState'],
        components: {
            UserRound,
            Logo, Input, Button, Loader, Select, Dropdown, Skeleton, StoreLogo, ChangeHistoryNavigation
        },
        props: {
            stores: {
                type: Array
            },
            storeMode: {
                type: Boolean
            },
            isOnboarding: {
                type: Boolean
            },
            isLoadingStore: {
                type: Boolean
            },
            selectedStoreId: {
                type: [String, null]
            }
        },
        data() {
            return {
                Zap,
                Plus,
                Menu,
                ExternalLink,
                searchTerm: '',
                isLoggingOut: false
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            authUser() {
                return this.authState.user;
            },
            activeSubscription() {
                return this.store.active_subscription;
            },
            localSelectedStoreId: {
                get() {
                    return this.selectedStoreId;
                },
                set(storeId) {
                    this.navigateToShowStoreHome(storeId);
                    this.$emit("update:selectedStoreId", storeId);
                }
            },
            storeOptions() {
                return this.stores.map((store) => {
                    return {
                        value: store.id,
                        label: store.name
                    }
                });
            },
            hasStores() {
                return this.stores.length > 0;
            },
            duplicateOrderId() {
                return this.$route.query.duplicate_order_id;
            },
            profileNavMenus() {
                let menus =  [
                    {
                        name: 'My Stores',
                        action: this.navigateToShowStores
                    }
                ];

                if(this.storeMode) {
                    menus.push(
                    {
                        name: 'Billing',
                        action: this.navigateToShowBillingSettings
                    });
                }

                menus.push(
                {
                    name: 'Sign Out',
                    routeName: null
                });

                return menus;
            },
        },
        methods: {
            navigateToCreateStore() {
                this.$router.push({
                    name: 'create-store',
                    query: { can_go_back: 1 }
                });
            },
            navigateToShowStoreHome(storeId) {
                console.log('navigateToShowStoreHome');
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
            navigateToAccountSettings(toggleDropdown) {
                this.$router.push({
                    name: 'show-account-settings',
                    query: { store_id: this.localSelectedStoreId }
                });
                toggleDropdown();
            },
            navigateToShowBillingSettings(toggleDropdown) {
                this.$router.push({
                    name: 'show-billing-settings',
                    query: { store_id: this.localSelectedStoreId }
                });
                toggleDropdown();
            },
            navigateToPricingPlans() {
                this.$router.push({
                    name: 'show-pricing-plans',
                    query: { store_id: this.store.id }
                })
            },
            openWebLink() {
                if (this.store.web_link) {
                    window.open(this.store.web_link, '_blank');
                }
            },
            onSearch() {

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
        }
    };

</script>

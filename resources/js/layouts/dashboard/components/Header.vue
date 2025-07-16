<template>

    <nav class="select-none fixed top-0 z-30 w-full bg-white border-b border-gray-200">

        <div class="px-3 py-3 lg:px-5 lg:pl-3">

            <div class="flex items-center justify-between">

                <div class="w-full flex items-center justify-start rtl:justify-end">

                    <!-- Menu Toggle Button -->
                    <button
                        type="button"
                        @click.prevent.stop
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-4">

                        <template v-if="storeMode">

                            <!-- Logo -->
                            <Skeleton v-if="isLoadingStore" width="w-8" height="h-8" :shine="true"></Skeleton>
                            <StoreLogo v-else size="w-10 h-10" :showButton="false"></StoreLogo>

                            <!-- Store Name -->
                            <Skeleton v-if="isLoadingStore" width="w-32" :shine="true"></Skeleton>
                            <a v-else :href="this.store.web_link" target="_blank" class="cursor-pointer active:scale-95 transition-all duration-250">
                                <h2 class="text-xl font-semibold">{{ store.name }}</h2>
                            </a>

                            <!-- Visit Store Icon -->
                            <Skeleton v-if="isLoadingStore" width="w-8" :shine="true"></Skeleton>
                            <a v-else :href="this.store.web_link" target="_blank" class="cursor-pointer rounded-md border p-1 border-transparent hover:border-gray-300 hover:bg-gray-50">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>

                        </template>

                        <template v-else>

                            <!-- Logo -->
                            <Logo
                                height="h-10"
                                @click.stop="navigateToManageStores"
                                class="cursor-pointer hover:shadow-sm active:scale-95 transition-all duration-250">
                            </Logo>

                        </template>

                    </div>

                </div>

                <template v-if="changeHistoryState.hasHistoryItems">

                    <ChangeHistoryNavigation></ChangeHistoryNavigation>

                </template>

                <template v-else>


                    <div
                        v-if="authUser"
                        class="w-full flex justify-center items-center">
                        <div
                            @click.stop="navigateToManageStores"
                            class="cursor-pointer animated-border-blue rounded-full overflow-hidden hover:shadow-sm active:scale-95 transition-all duration-250">
                            <h2 class="py-2 px-8 text-xs text-blue-500 bg-blue-50 font-semibold whitespace-nowrap">
                                Helping {{ authUser.firstName }} sell better
                            </h2>
                        </div>
                    </div>

                    <div class="w-full flex justify-end items-center space-x-8">

                        <div v-if="storeMode" class="flex items-center space-x-4">

                            <!-- Manage Stores -->
                            <Button :action="navigateToManageStores" type="light" size="sm" :skeleton="isLoadingStore" icon="refresh">
                                <span>Manage Stores</span>
                            </Button>

                            <!-- Upgrade -->
                            <Button :action="navigateToPricingPlans" type="primary" size="sm" :skeleton="isLoadingStore" icon="rocket">
                                <span>Upgrade</span>
                            </Button>

                        </div>

                        <div class="flex items-center ms-3">

                            <!-- Profile Avatar -->
                            <div>
                                <div id="profile-dropdown-trigger" class="cursor-pointer flex text-sm bg-gray-100 rounded-full focus:ring-4 focus:ring-gray-250">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="w-8 h-8 border border-gray-300 text-gray-500 rounded-full p-2 hover:scale-110 transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Menu -->
                            <div id="profile-dropdown" class="w-72 border z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm">

                                <div v-if="authUser" class="px-4 py-3 space-y-2" role="none">

                                    <!-- Name -->
                                    <p class="text-sm text-gray-900 font-medium truncate w-4/5" role="none">
                                        {{ authUser._attributes.name }}
                                    </p>

                                    <!-- Email -->
                                    <p v-if="authUser.email" class="text-xs text-gray-500 truncate w-4/5" role="none">
                                        {{ authUser.email }}
                                    </p>

                                    <!-- Mobile Number -->
                                    <p v-if="authUser.mobileNumber" class="text-xs text-gray-500 truncate w-4/5" role="none">
                                        {{ authUser.mobileNumber.national }}
                                    </p>

                                </div>

                                <!-- Profile Menu Items -->
                                <div class="py-1" role="none">

                                    <template
                                        :key="index"
                                        v-for="(navMenu, index) in profileNavMenus">

                                        <div @click="navMenu.name == 'Sign Out' ? attemptLogout() : navigateToNavRoute(navMenu)" class="cursor-pointer flex space-x-2 items-center py-3 px-4 text-gray-900 hover:bg-gray-100 group">

                                            <SpinningLoader v-if="navMenu.name == 'Sign Out' && isLoggingOut"></SpinningLoader>

                                            <span class="text-sm text-gray-500 group-hover:text-gray-900">
                                                {{ navMenu.name }}
                                            </span>

                                        </div>

                                    </template>

                                </div>

                            </div>

                        </div>

                    </div>

                </template>

            </div>

        </div>

    </nav>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';

    export default {
        inject: [],
        components: { Skeleton, StoreLogo },
        props: ['storeMode'],
        data() {
            return {

            }
        },
        computed: {
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
        methods: {

        },
        mounted() {

        },
        created() {

        }
    };

</script>

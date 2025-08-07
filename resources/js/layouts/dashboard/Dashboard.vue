<template>

    <div class="w-full bg-gradient-to-b from-blue-100 to-white-100 min-h-screen overflow-x-hidden">

        <!-- Notifications -->
        <Notifications></Notifications>

        <nav class="select-none fixed top-0 z-30 w-full bg-white border-b border-gray-200">

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
                                <a v-else :href="this.store.web_link" target="_blank" class="cursor-pointer active:scale-95 transition-all duration-250">
                                    <h2 class="text-xl font-semibold">{{ store.name }}</h2>
                                </a>

                                <!-- Visit Store Icon -->
                                <Skeleton v-if="isLoadingStore" width="w-8" :shine="true"></Skeleton>
                                <Button v-else type="light" :leftIcon="ExternalLink" :action="openWebLink"></Button>

                            </template>

                            <template v-else>

                                <!-- Application Logo -->
                                <Logo
                                    height="h-10"
                                    @click.stop="navigateToManageStores"
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
                            v-if="authUser"
                            class="flex w-full justify-center items-center">
                            <div
                                @click.stop="navigateToManageStores"
                                class="cursor-pointer animated-border-blue rounded-full overflow-hidden hover:shadow-sm active:scale-95 transition-all duration-250">
                                <h2 class="py-2 px-8 text-xs text-blue-500 bg-blue-50 font-semibold whitespace-nowrap truncate">
                                    Helping {{ authUser.first_name }} sell better
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

                                <Dropdown dropdownClasses="w-72">

                                    <template #trigger="props">

                                        <div
                                            @click="props.toggleDropdown"
                                            class="cursor-pointer flex text-sm bg-gray-100 rounded-full focus:ring-4 focus:ring-gray-250">
                                            <div class="flex items-center justify-center w-8 h-8 border border-gray-300 text-gray-500 rounded-full p-2 hover:scale-110 transition-all duration-300">
                                                <UserRound size="16"></UserRound>
                                            </div>
                                        </div>

                                    </template>

                                    <template #content>

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

                                                    <div @click="navMenu.name == 'Sign Out' ? logout() : navigateToNavRoute(navMenu)" class="cursor-pointer flex space-x-2 items-center py-3 px-4 text-gray-900 hover:bg-gray-100 group">

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

            <div class="h-full py-6 overflow-y-auto bg-white">

                <div class="font-medium">

                    <template v-if="isShowingSettings">

                        <!-- Return to dashboard -->
                        <Button
                            size="sm"
                            type="light"
                            buttonClass="mb-4 ml-2"
                            icon="short-left-arrow"
                            :action="navigateToHome"
                            :skeleton="isLoadingStore">
                            <span>Return to dashboard</span>
                        </Button>

                    </template>

                    <template
                        :key="index"
                        v-for="(navMenu, index) in navMenus">

                        <div
                            @click="() => navMenuHasChildren(navMenu) ? toggleNavMenuExpansion(index) : navigateToNavRoute(navMenu)"
                            :class="[{ 'bg-gray-100' : isActiveNavMenu(navMenu) && !navMenuHasChildren(navMenu) }, 'cursor-pointer py-2.5 px-4 text-gray-900 hover:bg-gray-100 group']">

                            <div class="flex items-center text-gray-700">

                                <Skeleton v-if="isLoadingStore" width="w-5" height="h-5" :shine="false"></Skeleton>

                                <!-- Home Icon -->
                                <svg v-else-if="navMenu.name == 'Home'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75V21h5.25v-4.5a2.25 2.25 0 014.5 0V21H21V9.75M1.5 12L12 2.25 22.5 12" />
                                </svg>

                                <!-- Orders Icon -->
                                <svg v-else-if="navMenu.name == 'Orders'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                                </svg>

                                <!-- Products Icon -->
                                <svg v-else-if="navMenu.name == 'Products'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>

                                <!-- Customers Icon -->
                                <svg v-else-if="navMenu.name == 'Customers'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>

                                <!-- Promotions Icon -->
                                <svg v-else-if="navMenu.name == 'Promotions'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.99 14.993 6-6m6 3.001c0 1.268-.63 2.39-1.593 3.069a3.746 3.746 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043 3.745 3.745 0 0 1-3.068 1.593c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.746 3.746 0 0 1-1.043-3.297 3.746 3.746 0 0 1-1.593-3.068c0-1.268.63-2.39 1.593-3.068a3.746 3.746 0 0 1 1.043-3.297 3.745 3.745 0 0 1 3.296-1.042 3.745 3.745 0 0 1 3.068-1.594c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.297 3.746 3.746 0 0 1 1.593 3.068ZM9.74 9.743h.008v.007H9.74v-.007Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>

                                <!-- Reviews Icon -->
                                <svg v-else-if="navMenu.name == 'Reviews'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>

                                <!-- Transactions Icon -->
                                <svg v-else-if="navMenu.name == 'Transactions'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>

                                <!-- Team Members Icon -->
                                <svg v-else-if="navMenu.name == 'Team Members'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>

                                <!-- Pages Icon -->
                                <svg v-else-if="navMenu.name == 'Pages'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>

                                <!-- Offline Store Icon -->
                                <svg v-else-if="navMenu.name == 'Offline Store'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>

                                <!-- Subscriptions Icon -->
                                <svg v-else-if="navMenu.name == 'Subscriptions'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>

                                <!-- Settings Icon -->
                                <svg v-else-if="navMenu.name == 'Settings'" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                <Skeleton v-if="isLoadingStore" width="w-24" :shine="true" class="ms-3"></Skeleton>
                                <span v-else :class="[{ 'text-gray-900' : isActiveNavMenu(navMenu) }, 'w-full text-sm ms-3 group-hover:text-gray-900']">{{ navMenu.name }}</span>

                                <div v-if="navMenuHasChildren(navMenu)">
                                    <ChevronUp v-if="navMenu.expanded" size="16"></ChevronUp>
                                    <ChevronDown v-else size="16"></ChevronDown>
                                </div>

                            </div>

                        </div>

                        <template v-if="navMenuHasChildren(navMenu) && hasExpandedNavMenu(navMenu)">

                            <template
                                :key="index"
                                v-for="(navMenuChild, index) in navMenu.children">

                                <div
                                    @click="navigateToNavRoute(navMenuChild)"
                                    :class="[{ 'bg-gray-100' : isActiveNavMenu(navMenuChild) }, 'cursor-pointer pl-8 py-1 text-gray-900 hover:bg-gray-100 group']">

                                    <span :class="[{ 'text-gray-900' : isActiveNavMenu(navMenuChild) }, 'font-normal text-sm ms-3 text-gray-500 group-hover:text-gray-900']">{{ navMenuChild.name }}</span>

                                </div>

                            </template>

                        </template>

                        <div v-if="['Team Members', 'Subscriptions'].includes(navMenu.name)" class="border-t border-gray-300 border-dashed pt-2 mt-2"></div>

                    </template>

                    <template v-if="!isShowingSettings">

                        <!-- Sign Out -->
                        <div @click="logout" class="cursor-pointer">

                            <div class="flex items-center py-3 px-4 text-gray-900 hover:bg-gray-100 group">

                                <Skeleton v-if="isLoadingStore" width="w-5" height="h-5" :shine="false"></Skeleton>

                                <Loader v-else-if="isLoggingOut"></Loader>
                                <svg v-else class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>

                                <Skeleton v-if="isLoadingStore" width="w-8" :shine="true" class="ms-3"></Skeleton>
                                <span v-else class="text-sm ms-3 text-gray-500 group-hover:text-gray-900">
                                    Sign Out
                                </span>

                            </div>

                        </div>

                    </template>

                </div>

            </div>

        </aside>

        <div :class="['relative', { 'sm:ml-64' : storeMode }]">

            <!-- Page Loader -->
            <transition v-if="storeMode" name="fade-loader">
                <div v-if="uiState.isLoading" class="absolute top-0 bottom-0 left-0 right-0 inset-0 bg-white bg-opacity-90 z-50">
                    <div class="absolute w-full flex justify-center items-center h-screen">
                        <Loader size="custom" customSize="w-8 h-8 border-4"></Loader>
                    </div>
                </div>
            </transition>

            <!-- Dashboard Content -->
            <router-view></router-view>

            <!-- Footer -->
            <Footer></Footer>

        </div>

    </div>

</template>

<script>

    import Logo from '@Partials/Logo.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';
    import Footer from '@Layouts/dashboard/components/Footer.vue';
    import { Menu, UserRound, ExternalLink, ChevronUp, ChevronDown } from 'lucide-vue-next';
    import Notifications from '@Layouts/dashboard/components/Notifications.vue';
    import ChangeHistoryNavigation from '@Layouts/dashboard/components/ChangeHistoryNavigation.vue';

    export default {
        inject: ['uiState', 'formState', 'authState', 'storeState', 'notificationState', 'changeHistoryState'],
        components: {
            ChevronUp, ChevronDown, Logo, Loader, Button, Dropdown, Skeleton, StoreLogo, Footer,
            Notifications, ChangeHistoryNavigation, UserRound
        },
        data() {
            return {
                Menu,
                ExternalLink,
                navMenus: [],
                profileNavMenus: [
                    {
                        name: 'Manage Stores',
                        routeName: 'show-stores',
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
            '$route.params.store_id'(newValue, oldValue) {
                if(this.storeId) this.showStore();
            },
            '$route.query.store_id'(newValue, oldValue) {
                if(this.storeId) this.showStore();
            },
            '$route.meta.settings'() {
                this.navMenus = this.buildNavMenus();
            }
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
            toggleNavMenuExpansion(index) {
                this.navMenus[index].expanded = !this.navMenus[index].expanded;
            },
            buildNavMenus() {

                let navMenus;

                if(this.isShowingSettings) {

                    navMenus = [
                        {
                            name: 'General',
                            routeName: 'show-store-general-settings'
                        },
                        {
                            name: 'Checkout',
                            routeName: 'show-store-checkout-settings'
                        },
                        {
                            name: 'Workflows',
                            routeName: 'show-store-workflows',
                            associatedRouteNames: ['show-store-workflow', 'create-store-workflow'],
                        },
                        {
                            name: 'Social Links',
                            routeName: 'show-store-social-settings'
                        },
                        {
                            name: 'Delivery / Pickup',
                            routeName: 'show-store-delivery-methods',
                            associatedRouteNames: ['show-store-delivery-method', 'create-store-delivery-method'],
                        },
                        {
                            name: 'Payment Methods',
                            routeName: 'show-store-payment-method-settings',
                            associatedRouteNames: ['show-store-payment-method', 'create-store-payment-method'],
                        },
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
                                    routeName: 'show-tags',
                                    associatedRouteNames: ['create-tags', 'edit-tag']
                                },
                                {
                                    name: 'Categories',
                                    routeName: 'show-categories',
                                    associatedRouteNames: ['create-categories', 'edit-category']
                                },
                                {
                                    name: 'Bulk Edit',
                                    routeName: 'bulk-edit-products'
                                },
                            ]
                        },
                        {
                            name: 'Customers',
                            routeName: 'show-store-customers',
                            associatedRouteNames: ['show-store-customer', 'create-store-customer'],
                        },
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
                        },
                        {
                            name: 'Settings',
                            routeName: 'show-store-general-settings'
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
            navigateToManageStores() {
                this.$router.push({
                    name: 'show-stores'
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
            navigateToNavRoute(navMenu) {
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
            async showStore(silentUpdate = false) {
                try {

                    if(!silentUpdate) this.storeState.isLoadingStore = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'tags', 'categories', 'activeSubscription.pricingPlan', /*  'storeRollingNumbers', 'userStoreAssociation'  */].join(',')
                        }
                    };

                    const response = await axios.get(`/api/stores/${this.storeId}`, config);
                    this.storeState.setStore(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch store:', error);

                    if(error.status == 404) {
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

                    authState.unsetUser();
                    authState.unsetToken();
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
            if(this.storeId) this.showStore();
            this.navMenus = this.buildNavMenus();
            this.storeState.silentUpdate = () => this.showStore(true);
        }
    };

</script>

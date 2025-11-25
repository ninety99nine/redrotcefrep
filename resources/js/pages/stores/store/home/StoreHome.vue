<template>

    <div class="select-none pt-24 px-20 pb-40">

        <h1 class="flex items-center justify-between space-x-2 text-3xl font-semibold mb-6">
            <div v-if="store"
                class="flex items-center space-x-4">
                <span>👋</span>
                <span>Welcome back, {{ authUser ? authUser.first_name : '' }}</span>
            </div>
            <div v-else
                class="flex items-center space-x-2">
                <Skeleton width="w-6" height="h-6" color="bg-white/50" rounded="rounded-md" :shine="true" />
                <Skeleton width="w-40" height="h-2" color="bg-white/50" rounded="rounded-md" :shine="true" />
            </div>

            <Button
                size="xs"
                type="light"
                :leftIcon="Megaphone"
                :action="navigateToMarketing">
                <span class="ml-1">Marketing</span>
            </Button>
        </h1>

        <div class="grid grid-cols-12 gap-4">

            <div class="col-span-8 space-y-4">

                <!-- Analytics Section -->
                <div class="bg-white p-4 shadow-sm rounded-xl">

                    <div class="mb-4">

                        <Datepicker
                            v-if="store"
                            :range="true"
                            v-model="dateRange"
                            format="dd MMM yyyy"
                            modelType="yyyy-MM-dd"
                            :enableTimePicker="false"
                            @change="showStoreBasicInsights"
                            :errorText="formState.getFormError('start_date')"/>

                        <Skeleton v-else width="w-full" height="h-6" rounded="rounded-lg" :shine="true" />

                    </div>

                    <div class="grid grid-cols-3 divide-x divide-gray-100">

                        <div class="flex flex-col items-center">
                            <h2 :class="['text-sm', { 'text-gray-300' : !store }]">Views</h2>
                            <h3 v-if="store && basicInsights" class="text-xl font-semibold">{{ basicInsights.total_views }}</h3>
                            <Skeleton v-else-if="isLoadingStoreBasicInsights" width="w-20" height="h-2" rounded="rounded-full" class="mt-2" :shine="true" />
                        </div>

                        <div class="flex flex-col items-center">
                            <h2 :class="['text-sm', { 'text-gray-300' : !store }]">Orders</h2>
                            <h3 v-if="store && basicInsights" class="text-xl font-semibold">{{ basicInsights.total_orders }}</h3>
                            <Skeleton v-else-if="isLoadingStoreBasicInsights" width="w-20" height="h-2" rounded="rounded-full" class="mt-2" :shine="true" />
                        </div>

                        <div class="flex flex-col items-center">
                            <h2 :class="['text-sm', { 'text-gray-300' : !store }]">Sales</h2>
                            <h3 v-if="store && basicInsights" class="text-xl font-semibold">{{ basicInsights.total_sales.amount_with_currency }}</h3>
                            <Skeleton v-else-if="isLoadingStoreBasicInsights" width="w-20" height="h-2" rounded="rounded-full" class="mt-2" :shine="true" />
                        </div>

                    </div>

                </div>

                <!-- Orders Section -->
                <div class="bg-white p-4 shadow-sm rounded-xl">

                    <div class="flex items-start justify-between">

                        <div>
                            <h2 :class="['text-lg font-semibold', { 'text-gray-300' : !store }]">Orders</h2>
                            <h3 :class="['text-sm mb-4', { 'text-gray-300' : !store }]">Last 30 days</h3>
                        </div>

                        <Button
                            size="xs"
                            type="light"
                            :leftIcon="Plus"
                            :skeleton="!store"
                            :action="navigateToCreateOrder">
                            <span>Add Order</span>
                        </Button>

                    </div>

                    <div class="space-y-2">

                        <div
                            @click.stop="store && statusCounts ? navigateToShowOrdersWaiting() : null"
                            :class="['flex items-center space-x-2 bg-white p-4 rounded-2xl transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 hover:shadow-sm', store ? 'cursor-pointer' : 'cursor-not-allowed']">

                            <Inbox v-if="store && statusCounts" class="text-gray-500" />
                            <Skeleton v-else width="w-6" height="h-6" rounded="rounded-full" :shine="true" />

                            <p v-if="store && statusCounts" class="text-sm"><span class="font-bold">{{ statusCounts.status_counts.waiting }}</span> {{ statusCounts.status_counts.waiting == 1 ? 'order' : 'orders' }} waiting</p>
                            <Skeleton v-else width="w-40" height="h-2" rounded="rounded-full" :shine="true" />

                        </div>

                        <div
                            @click.stop="store && statusCounts ? navigateToShowOrdersToConfirmPayments() : null"
                            :class="['flex items-center space-x-2 bg-white p-4 rounded-2xl transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 hover:shadow-sm', store ? 'cursor-pointer' : 'cursor-not-allowed']">

                            <HandCoins v-if="store && statusCounts" class="text-gray-500" />
                            <Skeleton v-else width="w-6" height="h-6" rounded="rounded-full" :shine="true" />

                            <p v-if="store && statusCounts" class="text-sm"><span class="font-bold">{{ statusCounts.payment_status_counts.waiting_confirmation }}</span> {{ statusCounts.status_counts.waiting == 1 ? 'order' : 'orders' }} to confirm payments</p>
                            <Skeleton v-else width="w-40" height="h-2" rounded="rounded-full" :shine="true" />

                        </div>

                    </div>

                </div>

                <!-- Action Cards -->
                <div class="grid grid-cols-3 gap-4">

                    <div
                        :key="i"
                        v-for="i in 3"
                        class="bg-white p-3 shadow-sm rounded-xl flex flex-col items-center">

                        <div v-if="isLoadingStore" class="w-full flex flex-col items-center">
                            <Skeleton width="w-7" height="h-7" rounded="rounded-lg" :shine="true" class="mb-2" />
                            <Skeleton width="w-4/5" height="h-4" rounded="rounded-md" :shine="true" class="mb-4" />
                            <Skeleton width="w-full" height="h-6" rounded="rounded-md" :shine="true" />
                        </div>

                        <div v-else class="flex flex-col items-center">

                            <component v-if="store" :is="getIcon(i)" size="28" class="text-gray-500 mb-2" />
                            <Skeleton v-else width="w-7" height="h-7" rounded="rounded-full" :shine="true" class="mb-2" />

                            <p v-if="store" class="text-xs text-center text-gray-600 mb-4">{{ getCardText(i) }}</p>
                            <div v-else class="w-full flex flex-col items-center space-y-1 mb-4">
                                <Skeleton width="w-4/5" height="h-2" rounded="rounded-md" :shine="true" />
                                <Skeleton width="w-3/5" height="h-2" rounded="rounded-md" :shine="true" />
                            </div>

                            <Button
                                size="xs"
                                type="light"
                                class="w-full"
                                :leftIcon="Plus"
                                :skeleton="!store"
                                buttonClass="w-full"
                                :action="getCardAction(i)">
                                <span>{{ getButtonText(i) }}</span>
                            </Button>

                        </div>

                    </div>

                </div>

                <!-- Join WhatsApp Group -->
                <JoinOurWhatsappGroup :mockMessages="mockMessages"></JoinOurWhatsappGroup>

            </div>

            <div class="col-span-4 space-y-4">

                <div class="bg-white p-4 shadow-sm rounded-xl">

                    <div class="flex items-center justify-between space-x-2">

                        <div class="flex items-center space-x-2">

                            <div v-if="store && activeSubscription" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <Skeleton v-else-if="!store" width="w-2" height="h-2" rounded="rounded-full" :shine="true" />

                            <div v-if="store" class="flex items-center space-x-1">

                                <h2 class="font-semibold">{{ activeSubscription ? pricingPlan.name : 'Basic Plan' }}</h2>

                                <Tooltip
                                    trigger="hover"
                                    :content="activeSubscription ? pricingPlan.description : 'You are on the Basic Plan with limited features'"
                                />

                            </div>
                            <Skeleton v-else width="w-24" height="h-2" rounded="rounded-md" :shine="true" />

                        </div>

                        <Button
                            size="xs"
                            type="light"
                            :leftIcon="Zap"
                            :action="navigateToPricingPlans"
                            v-if="store && activeSubscription">
                            <span>Change plan</span>
                        </Button>

                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Zap"
                            :action="navigateToPricingPlans"
                            v-else-if="store && !activeSubscription">
                            <span>Upgrade</span>
                        </Button>

                    </div>

                </div>

                <div class="bg-white p-4 shadow-sm rounded-xl">

                    <div>
                        <h2 class="font-semibold mb-2">Get Updates & Promotions</h2>
                        <h3 class="text-sm text-gray-500 mb-4">Follow us on social media to stay updated with the latest features and tips</h3>
                    </div>

                    <div class="flex justify-center space-x-4">

                        <a
                            :key="index"
                            target="_blank"
                            :href="`${platform.link}`"
                            v-for="(platform, index) in platforms"
                            class="p-2 rounded-lg hover:bg-gray-100 hover:scale-105 transition-all duration-300">

                            <img class="w-8" :src="`/images/social-media-icons/${platform.name}.png`" />

                        </a>

                    </div>

                </div>

                <div class="bg-white p-4 shadow-sm rounded-xl">

                    <div>
                        <h2 class="font-semibold mb-2">Join Live Onboarding Session</h2>
                        <h3 class="text-sm text-gray-500 mb-4">Explore key features and get your questions answered on our upcoming live session</h3>
                    </div>

                    <div class="flex items-center justify-center relative mb-4">

                        <div class="w-20 h-20 relative">
                            <img class="rounded-full border-2 border-transparent absolute top-0 -right-2" :src="`/images/team/julian.jpeg`" />
                        </div>
                        <div class="w-20 h-20 relative">
                            <img class="rounded-full border-2 border-white absolute top-0 -left-2" :src="`/images/team/bonolo.jpeg`" />
                        </div>

                    </div>

                    <div class="flex justify-center">

                        <Button
                            size="xs"
                            type="light"
                            :action="bookYourSeat"
                            :leftIcon="CalendarDays">
                            <span>Book Your Seat</span>
                        </Button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

import dayjs from 'dayjs';
import Button from '@Partials/Button.vue';
import Tooltip from '@Partials/Tooltip.vue';
import Skeleton from '@Partials/Skeleton.vue';
import Datepicker from '@Partials/Datepicker.vue';
import JoinOurWhatsappGroup from '@Components/JoinOurWhatsappGroup.vue';
import { Box, Plus, Inbox, Zap, Megaphone, UsersRound, CreditCard, HandCoins, CalendarDays } from 'lucide-vue-next';

export default {
    inject: ['formState', 'authState', 'storeState', 'notificationState'],
    components: {
        Box, Inbox, UsersRound, CreditCard, HandCoins, Button, Tooltip, Skeleton, Datepicker, JoinOurWhatsappGroup
    },
    data() {
        return {
            Zap,
            Plus,
            Megaphone,
            CalendarDays,
            dateRange: [
                dayjs().startOf('month').format('YYYY-MM-DD'), // e.g., "2025-10-01"
                dayjs().format('YYYY-MM-DD'),                  // e.g., "2025-10-25"
            ],
            statusCounts: null,
            basicInsights: null,
            isLoadingOrderStatusCounts: false,
            isLoadingStoreBasicInsights: false,
            bookYourSeatLink: import.meta.env.VITE_BOOK_YOUR_SEAT_LINK,
            platforms: [
                { name: 'whatsapp', link: 'https://chat.whatsapp.com/FR9xLOjlcIs6KO0uHiLnTc' },
                { name: 'instagram', link: 'https://www.instagram.com/perfect_order_botswana' },
                { name: 'facebook', link: 'https://web.facebook.com/profile.php?id=61552503999976' },
            ],
            mockMessages: [
                {
                    sender: 'Support Team',
                    text: 'Hello everyone! Our support team is here to help with your e-commerce platform questions. Feel free to ask! 😊', timestamp: dayjs().subtract(6, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'You', text: 'Hi, how do I add a discount code to my store?', timestamp: dayjs().subtract(5, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team', text: 'Great question! In your dashboard, go to *Settings > Discounts*, click *Create Discount*, and set the code, amount, and conditions. Check [this guide](https://yourstore.com/docs/discounts) for details.', timestamp: dayjs().subtract(4, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'Emma', text: 'I’m struggling with setting up shipping options. Any tips?', timestamp: dayjs().subtract(3, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#dc16fc'
                },
                {
                    sender: 'Support Team', text: 'Hi Emma! Navigate to *Settings > Shipping*, and add zones with rates. You can set flat rates or weight-based shipping. See [Shipping Setup](https://yourstore.com/docs/shipping) for a step-by-step. Let us know if you need more help!', timestamp: dayjs().subtract(2, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'You', text: 'Thanks! Can I also track my store’s analytics?', timestamp: dayjs().subtract(1, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team', text: 'Absolutely! Go to *Analytics* in your dashboard, select a date range, and view total views, orders, and sales. Try it out and let us know if you have questions!', timestamp: dayjs().format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                }
            ]
        };
    },
    watch: {
        store() {
            this.showOrderStatusCounts();
            this.showStoreBasicInsights();
        }
    },
    computed: {
        authUser() {
            return this.authState.user;
        },
        store() {
            return this.storeState.store;
        },
        isLoadingStore() {
            return this.storeState.isLoadingStore;
        },
        activeSubscription() {
            return this.store.active_subscription;
        },
        pricingPlan() {
            return this.activeSubscription ? this.activeSubscription.pricing_plan : null;
        },
    },
    methods: {
        navigateToMarketing() {
            this.$router.push({
                name: 'show-marketing',
                query: { store_id: this.store.id },
            });
        },
        navigateToPricingPlans() {
            this.$router.push({
                name: 'show-pricing-plans',
                query: { store_id: this.store.id },
            });
        },
        navigateToCreateProduct() {
            this.$router.push({
                name: 'create-product',
                query: { store_id: this.store.id },
            });
        },
        navigateToAddTeamMember() {
            this.$router.push({
                name: 'add-team-member',
                query: { store_id: this.store.id },
            });
        },
        navigateToCreateOrder() {
            this.$router.push({
                name: 'create-order',
                query: { store_id: this.store.id },
            });
        },
        navigateToPaymentMethods() {
            this.$router.push({
                name: 'show-payment-methods',
                query: { store_id: this.store.id },
            });
        },
        navigateToShowOrdersWaiting() {
            this.$router.push({
                name: 'show-orders',
                query: {
                    store_id: this.store.id,
                    filterExpressions: 'status:in:waiting'
                }
            });
        },
        navigateToShowOrdersToConfirmPayments() {
            this.$router.push({
                name: 'show-orders',
                query: {
                    store_id: this.store.id,
                    filterExpressions: 'payment_status:in:waiting confirmation'
                }
            });
        },
        bookYourSeat() {
            if (this.store.web_link) {
                window.open(this.bookYourSeatLink, '_blank');
            }
        },
        getIcon(index) {
            const icons = [Box, UsersRound, CreditCard];
            return icons[index - 1];
        },
        getCardText(index) {
            const texts = [
                'Add store products your customers will love',
                'Invite your team to help manage and grow your business',
                'Allow customers to make payments when checking out',
            ];
            return texts[index - 1];
        },
        getButtonText(index) {
            const texts = ['Add Products', 'Invite Team', 'Add Payments'];
            return texts[index - 1];
        },
        getCardAction(index) {
            const actions = [
                this.navigateToCreateProduct,
                this.navigateToAddTeamMember,
                this.navigateToPaymentMethods,
            ];
            return actions[index - 1];
        },
        async showOrderStatusCounts() {

            try {

                this.isLoadingOrderStatusCounts = true;

                let config = {
                    params: {
                        store_id: this.store.id,
                        end_date: dayjs().format('YYYY-MM-DD'),                      // e.g., "2025-10-16"
                        start_date: dayjs().subtract(30, 'day').format('YYYY-MM-DD'), // e.g., "2025-09-16"
                    }
                };

                const response = await axios.get(`/api/orders/status-counts`, config);
                this.statusCounts = response.data;

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order status counts';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to fetch order status counts:', error);
            } finally {
                this.isLoadingOrderStatusCounts = false;
            }
        },
        async showStoreBasicInsights() {

            try {

                if(this.dateRange == null) {
                    this.basicInsights = null;
                    return;
                }

                this.isLoadingStoreBasicInsights = true;

                let config = {
                    params: {
                        end_date: this.dateRange[1],
                        start_date: this.dateRange[0]
                    }
                };

                const response = await axios.get(`/api/stores/${this.store.id}/basic-insights`, config);
                this.basicInsights = response.data;

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching store insights';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to fetch store insights:', error);
            } finally {
                this.isLoadingStoreBasicInsights = false;
            }
        }
    },
    created() {
        if(this.store) {
            this.showOrderStatusCounts();
            this.showStoreBasicInsights();
        }
    }
};
</script>

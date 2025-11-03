<template>

    <div class="min-h-screen bg-linear-to-br from-blue-50 via-white to-indigo-50 py-24 px-6">

        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="text-center mb-8">

                <h1 class="flex items-center justify-center gap-2 text-3xl font-bold text-gray-900 mb-3">
                    Pricing Plans
                </h1>

                <p class="text-gray-600 max-w-2xl mx-auto text-base">
                    Choose the perfect plan for your store and start selling instantly!
                </p>

            </div>

            <!-- Loader -->
            <div v-if="isLoadingPricingPlans" class="flex justify-center py-20">
                <Loader />
            </div>

            <template v-else>

                <!-- Billing Toggle -->
                <div class="flex justify-center mb-4">

                    <div class="inline-flex bg-gray-100 p-1 rounded-full shadow-inner">

                        <button
                            @click="() => changePricingPlan('monthly')"
                            :class="[
                                'px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300 flex items-center gap-2',
                                isShowingMonthlyPricingPlans
                                    ? 'bg-white text-blue-700 shadow-sm'
                                    : 'text-gray-600 hover:text-gray-800'
                            ]">
                            Monthly
                        </button>

                        <button
                            @click="() => changePricingPlan('annually')"
                            :class="[
                                'px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300 flex items-center gap-2',
                                isShowingAnnualPricingPlans
                                    ? 'bg-white text-blue-700 shadow-sm'
                                    : 'text-gray-600 hover:text-gray-800'
                            ]">
                            Annually
                        </button>

                    </div>

                </div>

                <!-- Pricing Cards -->
                <div class="flex justify-center gap-6 max-w-6xl mx-auto mb-8">

                    <!-- Basic Plan (Only show if no active subscription) -->
                    <div
                        v-if="!hasActiveSubscription"
                        class="group relative bg-white border border-gray-200 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:border-gray-300 transition-all duration-300">

                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Basic Plan</h3>
                            <p class="text-sm text-gray-500 mt-1">Basic store access with limited features</p>
                        </div>

                        <div class="mb-8">
                            <span class="text-4xl font-bold text-gray-900">$0.00</span>
                            <span class="text-gray-500 text-sm">
                            / <template v-if="isShowingMonthlyPricingPlans">month</template>
                            <template v-else>year</template>
                            </span>
                        </div>

                        <div class="space-y-3 mb-8">
                            <div v-for="(feature, index) in freePlanFeatures" :key="index" class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ feature }}
                            </div>
                        </div>

                        <Button
                            size="md"
                            type="outline"
                            :disabled="true"
                            buttonClass="w-full">
                            Current Plan
                        </Button>

                    </div>

                    <!-- Paid Plans -->
                    <div
                        :key="index"
                        v-for="(pricingPlan, index) in filteredPricingPlans"
                        :class="[
                            'group relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border',
                            isCurrentPlan(pricingPlan) ? 'border-blue-500' : 'border-gray-200'
                        ]">

                        <!-- Recommended Badge -->
                        <div
                            v-if="pricingPlan.metadata?.recommended"
                            class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                            Most Popular
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900">{{ pricingPlan.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1 max-w-xs">{{ pricingPlan.description }}</p>
                        </div>

                        <div class="mb-8">
                            <span class="text-4xl font-bold text-gray-900">{{ pricingPlan.price.amount_with_currency }}</span>
                            <span class="text-gray-500 text-sm">
                            / <template v-if="isShowingMonthlyPricingPlans">month</template>
                            <template v-else>year</template>
                            </span>
                        </div>

                        <!-- CTA Button -->
                        <Button
                            size="lg"
                            buttonClass="w-full mb-6"
                            :type="buttonType(pricingPlan)"
                            :loading="pricingPlanIndex === index"
                            :disabled="buttonDisabled(pricingPlan, index)"
                            :action="() => payPricingPlan(pricingPlan, index)">
                            <div v-if="pricingPlanIndex === index" class="flex items-center justify-center space-x-2">
                                <Loader color="border-gray-300" />
                                <span class="animate-pulse">Preparing payment...</span>
                            </div>
                            <template v-else>
                                {{ buttonText(pricingPlan) }}
                            </template>
                        </Button>

                        <!-- Features -->
                        <div class="space-y-3">
                            <div v-for="(feature, idx) in pricingPlan.features" :key="idx" class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ feature }}
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Join WhatsApp Group -->
                <JoinOurWhatsappGroup :mockMessages="mockMessages"></JoinOurWhatsappGroup>

            </template>

        </div>

    </div>

</template>

<script>

import dayjs from 'dayjs';
import { Rocket } from 'lucide-vue-next';
import Button from '@Partials/Button.vue';
import Loader from '@Partials/Loader.vue';
import JoinOurWhatsappGroup from '@Components/JoinOurWhatsappGroup.vue';

export default {
    inject: ['formState', 'storeState', 'notificationState'],
    components: { Rocket, Button, Loader, JoinOurWhatsappGroup },
    data() {
        return {
            Rocket,
            pagination: null,
            pricingPlans: [],
            pricingPlanIndex: null,
            isLoadingPricingPlans: false,
            isGeneratingPaymentLink: false,
            activePricingPlan: 'monthly',
            freePlanFeatures: [
                'WhatsApp order form',
                'Manual payment methods',
                'Up to 20 images'
            ],
            mockMessages: [
                {
                    sender: 'You',
                    text: 'What’s the difference between the Basic and Pro plan?',
                    timestamp: dayjs().subtract(5, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team',
                    text: 'Great question! The *Basic Plan* gives you WhatsApp orders and manual payments. The *Premium Plan* adds priority support, unlimited images, and analytics. Check the features list for each plan for full details!',
                    timestamp: dayjs().subtract(4, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'Emma',
                    text: 'Can I switch from monthly to annual later?',
                    timestamp: dayjs().subtract(3, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#dc16fc'
                },
                {
                    sender: 'Support Team',
                    text: 'Yes, Emma! You can upgrade or change your billing cycle anytime. Just click *Upgrade* or *Downgrade* on any plan. Annual plans save you up to 20%!',
                    timestamp: dayjs().subtract(2, 'minute').format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                },
                {
                    sender: 'You',
                    text: 'Perfect! I’m ready to upgrade to Pro.',
                    timestamp: dayjs().subtract(1, 'minute').format('HH:mm'),
                    isOwnMessage: true
                },
                {
                    sender: 'Support Team',
                    text: 'Awesome! Click *Upgrade* on the Pro plan card. We’ll prepare your secure payment link in seconds. Let’s get your store growing!',
                    timestamp: dayjs().format('HH:mm'),
                    isOwnMessage: false,
                    nameColor: '#165dfc'
                }
            ]
        };
    },
    computed: {
        store() {
            return this.storeState.store;
        },
        isLoadingStore() {
            return this.storeState.isLoadingStore;
        },
        activeSubscription() {
            return this.store?.active_subscription;
        },
        currentPricingPlan() {
            return this.activeSubscription?.pricing_plan;
        },
        hasActiveSubscription() {
            return !!this.activeSubscription;
        },
        isShowingMonthlyPricingPlans() {
            return this.activePricingPlan === 'monthly';
        },
        isShowingAnnualPricingPlans() {
            return this.activePricingPlan === 'annually';
        },
        filteredPricingPlans() {
            const frequency = this.isShowingMonthlyPricingPlans ? 'month' : 'year';
            return this.pricingPlans.filter(
                (plan) => plan.metadata?.store_subscription?.frequency === frequency
            );
        }
    },
    methods: {
        changePricingPlan(plan) {
            this.activePricingPlan = plan;
        },
        isCurrentPlan(plan) {
            if (!this.currentPricingPlan) return false;
            return plan.id === this.currentPricingPlan.id;
        },
        buttonText(plan) {
            if (this.isCurrentPlan(plan)) return 'Current Plan';
            if (!this.hasActiveSubscription) return 'Subscribe Now';
            const currentPrice = parseFloat(this.currentPricingPlan.price.amount);
            const planPrice = parseFloat(plan.price.amount);
            return planPrice > currentPrice ? 'Upgrade' : 'Downgrade';
        },
        buttonType(plan) {
            if (this.isCurrentPlan(plan)) return 'outline';
            return 'primary';
        },
        buttonDisabled(plan, index) {
            if (this.isCurrentPlan(plan)) return true;
            return this.isGeneratingPaymentLink && this.pricingPlanIndex !== index;
        },
        async showPricingPlans() {
            try {
                this.isLoadingPricingPlans = true;
                const config = {
                params: {
                    platform: 'web',
                    billing_type: 'one time',
                    type: 'store subscription',
                }
                };
                const response = await axios.get('/api/pricing-plans', config);
                this.pagination = response.data;
                this.pricingPlans = this.pagination.data;
            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching pricing plans';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to fetch pricing plans:', error);
            } finally {
                this.isLoadingPricingPlans = false;
            }
        },
        async payPricingPlan(pricingPlan, index) {
            if (this.isGeneratingPaymentLink) return;

            try {
                this.pricingPlanIndex = index;
                this.isGeneratingPaymentLink = true;

                const data = {
                store_id: this.store.id,
                payment_method_type: 'dpo'
                };

                const response = await axios.post(`/api/pricing-plans/${pricingPlan.id}/pay`, data);
                const dpoPaymentUrl = response.data.metadata.dpo_payment_url;
                window.location.href = dpoPaymentUrl;
            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while subscribing';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to subscribe:', error);
            } finally {
                this.isGeneratingPaymentLink = false;
                this.pricingPlanIndex = null;
            }
        }
    },
    created() {
        this.showPricingPlans();
    }
};
</script>

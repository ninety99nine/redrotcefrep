<template>

    <div class="min-h-screen relative">

        <div class="z-10 pt-32 px-20 relative">

            <div class="flex flex-col items-center mb-10">

                <h1 class="flex justify-center items-center space-x-2 text-xl text-gray-700 font-semibold mb-4">
                    <Rocket size="24"></Rocket>
                    <span>Pricing Plans</span>
                </h1>

                <p class="w-96 text-sm text-gray-500 text-center">Get the best plan for your store. Pay monthly or annually, with no hidden fees. Start selling instantly!</p>

            </div>

            <!-- Loader -->
            <div
                class="flex justify-center"
                v-if="isLoadingPricingPlans">
                <Loader></Loader>
            </div>

            <template v-if="!isLoadingPricingPlans">

                <div class="flex justify-center mb-4">

                    <div class="w-fit flex justify-center items-center bg-white py-0.5 px-1 border border-blue-300 rounded-full mb-4 relative">

                        <div :class="[isShowingMonthlyPricingPlans ? '-translate-x-12' : 'translate-x-12', 'absolute w-24 bg-blue-500 h-6 rounded-full transition-transform duration-500 ease-in-out']"></div>
                        <div @click="() => changePricingPlan('monthly')" :class="[isShowingMonthlyPricingPlans ? 'text-white' : 'text-gray-500', 'z-10 py-1 px-4 rounded-full w-24 text-sm text-center cursor-pointer transition-transform duration-500 ease-in-out']">Monthly</div>
                        <div @click="() => changePricingPlan('annually')" :class="[isShowingAnnualPricingPlans ? 'text-white' : 'text-gray-500', 'z-10 py-1 px-4 rounded-full w-24 text-sm text-center cursor-pointer transition-transform duration-500 ease-in-out']">Annually</div>

                    </div>

                </div>

                <div class="flex justify-center mb-8">
                    <img :src="'/images/store-rooftop.png'" class="w-96">
                </div>

                <div class="flex justify-center space-x-4 mb-8">

                    <div
                        class="animated-border-blue w-80 bg-white py-4 px-4 shadow-sm rounded-xl">

                        <h1 class="text-xl text-gray-700 font-bold mb-2">
                            Free Plan
                        </h1>

                        <h2 class="text-gray-500 text-sm mb-4">
                            Free store access with limited features
                        </h2>

                        <h2 class="text-3xl text-gray-700 font-bold space-x-1 mb-4">
                            <span>$0.00</span>
                            <span class="text-sm text-gray-500 font-normal">/</span>
                            <span class="text-sm text-gray-500 font-normal">
                                <template v-if="isShowingMonthlyPricingPlans">month</template>
                                <template v-else-if="isShowingAnnualPricingPlans">year</template>
                            </span>
                        </h2>

                        <div class="bg-gray-50 border p-4 rounded-lg">
                            <div
                                :key="index"
                                v-for="(feature, index) in freePlanFeatures">

                                <span class="text-gray-500 text-xs">{{ feature }}</span>

                            </div>
                        </div>

                    </div>

                    <div
                        :key="index"
                        v-for="(pricingPlan, index) in filteredPricingPlans"
                        class="animated-border-blue w-80 bg-white py-4 px-4 shadow-sm rounded-xl">

                        <h1 class="text-xl text-gray-700 font-bold mb-2">
                            {{ pricingPlan.name }}
                        </h1>

                        <h2 class="w-4/5 text-gray-500 text-sm mb-4">
                            {{ pricingPlan.description }}
                        </h2>

                        <h2 class="text-3xl text-gray-700 font-bold space-x-1 mb-4">
                            <span>{{ pricingPlan.price.amount_with_currency }}</span>
                            <span class="text-sm text-gray-500 font-normal">/</span>
                            <span class="text-sm text-gray-500 font-normal">
                                <template v-if="isShowingMonthlyPricingPlans">month</template>
                                <template v-else-if="isShowingAnnualPricingPlans">year</template>
                            </span>
                        </h2>

                        <Button
                            size="sm"
                            type="primary"
                            :rightIcon="Rocket"
                            buttonClass="w-full mb-4"
                            :loading="pricingPlanIndex == index"
                            :action="() => payPricingPlan(pricingPlan, index)"
                            :disabled="isGeneratingPaymentLink && pricingPlanIndex != index">
                            <span>Subscribe</span>
                        </Button>

                        <p v-if="pricingPlanIndex == index" class="animate-pulse text-xs text-center text-blue-500 bg-blue-50 rounded-full p-2 mb-4">
                            We are preparing your payment...
                        </p>

                        <div class="bg-green-50 border border-green-500 p-4 rounded-lg">
                            <div
                                :key="index"
                                v-for="(feature, index) in pricingPlan.features">

                                <span class="text-xs">{{ feature }}</span>

                            </div>
                        </div>

                    </div>

                </div>

            </template>

        </div>

        <img :src="'/images/clouds.png'" class="absolute top-32">

    </div>

</template>

<script>

    import { Rocket } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: { Rocket, Button, Loader },
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
            isShowingMonthlyPricingPlans() {
                return this.activePricingPlan == 'monthly';
            },
            isShowingAnnualPricingPlans() {
                return this.activePricingPlan == 'annually';
            },
            filteredPricingPlans() {
                if(this.isShowingMonthlyPricingPlans) {
                    return this.pricingPlans.filter((pricingPlan) => pricingPlan.metadata.store_subscription.frequency == 'month')
                }else{
                    return this.pricingPlans.filter((pricingPlan) => pricingPlan.metadata.store_subscription.frequency == 'year')
                }
            }
        },
        methods: {
            changePricingPlan(activePricingPlan) {
                this.activePricingPlan = activePricingPlan;
            },
            async showPricingPlans() {
                try {

                    this.isLoadingPricingPlans = true;

                    let config = {
                        params: {
                            'platform': 'web',
                            'billing_type': 'one time',
                            'type': 'store subscription',
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

                try {

                    if(this.isGeneratingPaymentLink) return;

                    this.pricingPlanIndex = index;
                    this.isGeneratingPaymentLink = true;

                    let data = {
                        'store_id': this.store.id,
                        'payment_method_type': 'dpo'
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
                }

            }
        },
        created() {
            this.showPricingPlans();
        }
    };

</script>

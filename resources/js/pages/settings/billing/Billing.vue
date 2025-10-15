<template>

    <div class="max-w-2xl mx-auto pt-24 pb-40">

        <!-- Subscription Card -->
        <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

            <h1 class="text-lg font-bold border-b border-gray-300 border-dashed pb-2 mb-4">Subscription</h1>

            <div v-if="isLoadingStore || store.active_subscription">

                <Skeleton v-if="isLoadingStore" width="w-16" height="h-4" class="mb-2" />
                <h1 v-else class="text-gray-700 font-bold mb-2">
                    {{ store.active_subscription.pricing_plan.name }}
                </h1>

                <Skeleton v-if="isLoadingStore" width="w-1/2" class="mb-2" />
                <h2 v-else class="w-4/5 text-gray-500 text-sm mb-4">
                    {{ store.active_subscription.pricing_plan.description }}
                </h2>

                <div
                    v-if="isLoadingStore"
                    class="flex items-end space-x-2 mb-2">
                    <Skeleton width="w-16" height="h-4" />
                    <span class="text-sm text-gray-500 font-normal">/</span>
                    <Skeleton width="w-16" height="h-4" />
                </div>

                <h2
                    v-else
                    class="text-3xl text-gray-700 font-bold space-x-1 mb-4">

                    <span>{{ store.active_subscription.pricing_plan.price.amount_with_currency }}</span>

                    <span class="text-sm text-gray-500 font-normal">/</span>

                    <span class="text-sm text-gray-500 font-normal"
                        v-if="store.active_subscription.pricing_plan.metadata.store_subscription.duration > 1">
                        {{ store.active_subscription.pricing_plan.metadata.store_subscription.duration }}
                    </span>

                    <span class="text-sm text-gray-500 font-normal">{{ store.active_subscription.pricing_plan.metadata.store_subscription.frequency }}</span>

                </h2>

                <div class="text-sm text-gray-600 mb-4">
                    <Skeleton v-if="isLoadingStore" width="w-1/4" class="mb-2" />
                    <p v-else><strong>Start date:</strong> {{ formattedDatetime(store.active_subscription.start_at) }}</p>

                    <Skeleton v-if="isLoadingStore" width="w-1/4" />
                    <p v-else><strong>End date:</strong> {{ formattedDatetime(store.active_subscription.end_at) }}</p>
                </div>

                <div :class="['border p-4 rounded-lg', isLoadingStore ? 'bg-gray-50 border-gray-300' : 'bg-green-50 border-green-500']">
                    <div
                        :key="index"
                        v-for="(feature, index) in store?.active_subscription?.pricing_plan?.features || [1,2,3]">

                        <Skeleton v-if="isLoadingStore" width="w-1/3" :class="[ index == 2 ? 'mb-0' : 'mb-2']" />
                        <span v-else class="text-xs">{{ feature }}</span>

                    </div>
                </div>

                <Button
                    size="lg"
                    type="primary"
                    buttonClass="w-full mt-4"
                    :skeleton="isLoadingStore"
                    :action="navigateToPricingPlans">
                    <span>Upgrade Plan</span>
                </Button>

            </div>

            <div v-else>

                <Skeleton v-if="isLoadingStore" class="w-20 h-4 mb-2" />
                <h1 class="text-gray-700 font-bold mb-2">
                    Free Plan
                </h1>

                <Skeleton v-if="isLoadingStore" class="w-20 h-6 mb-2" />
                <h2 class="w-4/5 text-gray-500 text-sm mb-4">
                    Free store access with limited features
                </h2>

                <h2 class="text-3xl text-gray-700 font-bold space-x-1 mb-4">
                    <span>$0.00</span>
                    <span class="text-sm text-gray-500 font-normal">/</span>
                    <span class="text-sm text-gray-500 font-normal">month</span>
                </h2>

                <Button
                    size="lg"
                    type="primary"
                    :rightIcon="Rocket"
                    buttonClass="w-full mb-4"
                    :action="navigateToPricingPlans">
                    <span class="mr-2">Upgrade Now</span>
                </Button>

                <div class="bg-gray-50 border border-gray-500 p-4 rounded-lg">
                    <div
                        :key="index"
                        v-for="(feature, index) in freePlanFeatures">
                        <span class="text-gray-600 text-xs">{{ feature }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</template>

<script>

    import { Rocket } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedDatetime } from '@Utils/dateUtils';

    export default {
        inject: ['storeState'],
        components: {
            Button, Popover, Skeleton
        },
        data() {
            return {
                Rocket,
                freePlanFeatures: [
                    'Limited WhatsApp orders',
                    'Basic store access',
                    'Standard support',
                ],
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
        methods: {
            formattedDatetime,
            navigateToPricingPlans() {
                this.$router.push({
                    name: 'show-pricing-plans',
                    query: { store_id: this.store.id }
                })
            },
        }
    };
</script>

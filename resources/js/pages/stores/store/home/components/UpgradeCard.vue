<template>

    <div class="animated-border-blue bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl">

        <div class="w-full flex items-center justify-between">

            <h1 class="space-x-2 text-md text-gray-700 font-bold">

                <Skeleton v-if="isLoadingStore" width="w-32"></Skeleton>
                <span v-else>
                    <template v-if="pricingPlan">{{ pricingPlan.name }} Plan</template>
                    <template v-else>Free Plan</template>
                </span>

            </h1>

            <Button
                size="xs"
                type="primary"
                id="upgradeButton"
                :action="navigateToPricingPlans"
                v-if="!isLoadingStore && !activeSubscription">
                <span>Upgrade</span>
            </Button>

            <Button
                v-else
                size="xs"
                type="primary"
                :skeleton="isLoadingStore"
                :action="navigateToShowBillingSettings">
                <span>{{ isLoadingStore ? 'Checking' : 'View Subscription' }}</span>
            </Button>

        </div>

        <div v-if="isLoadingStore" class="space-y-2">
            <Skeleton width="w-2/3"></Skeleton>
            <Skeleton width="w-1/2"></Skeleton>
        </div>

        <div v-else-if="activeSubscription">

            <p class="text-xs text-gray-600 mb-2">
                You are now subscribed to the {{ pricingPlan.name }} plan
            </p>

            <Countdown :showMoreInfoPopover="false" :time="activeSubscription.end_at" textClass="text-xs">
                <template #prefix="props">
                    <Clock size="20"></Clock>
                    <span v-if="!props.hasExpired" class="text-xs">Expires in</span>
                </template>
            </Countdown>

        </div>

        <p v-else class="text-xs text-gray-600">
            You are on the Free plan with limited features.
            Do so much more with an upgrade ✨
        </p>

    </div>

</template>

<script>

    import { Clock } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import Countdown from '@Partials/Countdown.vue';

    export default {
        inject: ['storeState'],
        components: {
            Clock, Button, Skeleton, Countdown
        },
        data() {
            return {
                upgradeButtonAnimationTimeout: null
            };
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue && !this.activeSubscription) {
                    this.$nextTick(() => {
                        this.manageUpgradeButtonAnimation();
                    });
                }
            }
        },
        computed: {
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
            navigateToPricingPlans() {
                this.$router.push({
                    name: 'show-pricing-plans',
                    query: { store_id: this.store.id }
                })
            },
            async navigateToShowBillingSettings() {
                await this.$router.push({
                    name: 'show-billing-settings',
                    query: { store_id: this.store.id }
                });
            },
            manageUpgradeButtonAnimation() {

                // Get the element
                const upgradeButton = document.getElementById("upgradeButton");

                if(upgradeButton) {

                    // Add the bounce class after 5 seconds
                    setTimeout(() => {
                        upgradeButton.classList.add("smooth-bounce");
                    }, 5000);

                    // Function to toggle the bounce animation
                    const toggleBounce = () => {

                        // Remove the bounce class smoothly
                        upgradeButton.classList.remove("smooth-bounce");

                        // After 5 seconds, add the bounce class smoothly
                        setTimeout(() => {
                            upgradeButton.classList.add("smooth-bounce");
                        }, 5000);

                    };

                    // Set interval to add and remove the class every 10 seconds (5s on, 5s off)
                    this.upgradeButtonAnimationTimeout = setInterval(() => {
                        toggleBounce();
                    }, 10000); // This runs the toggle function every 10 seconds

                }

            }
        },
        unmounted() {
            if (this.upgradeButtonAnimationTimeout) {
                clearInterval(this.upgradeButtonAnimationTimeout);
            }
        }
    };

</script>

<style>

    /* Smooth transition for the scale and bounce */
    @keyframes bounceSmooth {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }

    /* Apply smooth bounce effect */
    .smooth-bounce {
        animation: bounceSmooth 2.5s ease infinite;
    }

</style>

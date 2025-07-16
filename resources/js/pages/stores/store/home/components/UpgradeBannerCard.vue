<template>

    <div v-if="!isLoadingStore && !activeSubscription" class="animated-border-blue shadow-sm rounded-xl overflow-hidden mb-8">

        <div class="bg-white space-y-2 py-4 px-4">

            <div class="w-full flex items-center justify-between">

                <div class="flex items-center space-x-2 text-md text-gray-700 font-bold">
                    <Lock size="20"></Lock>
                    <span>Free Plan</span>
                </div>

                <Button
                    size="sm"
                    type="primary"
                    id="upgradeButton"
                    :rightIcon="Rocket"
                    :action="navigateToPricingPlans">
                    <span>Upgrade</span>
                </Button>

            </div>

            <p class="text-xs text-gray-600">
                You are on the Free plan with limited features.
                Do so much more with an upgrade ✨
            </p>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { Lock, Rocket } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Lock, Button
        },
        data() {
            return {
                Rocket,
                upgradeButtonAnimationTimeout: null
            };
        },
        watch: {
            store(newValue) {
                if (newValue && !this.activeSubscription) {
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
            }
        },
        methods: {
            navigateToPricingPlans() {
                this.$router.push({
                    name: 'show-pricing-plans',
                    query: { 'store_id': this.store.id }
                })
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

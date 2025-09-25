<template>

    <div v-if="isLoadingStore || isLoadingOrder || hasDeliveryAddress" class="bg-white rounded-lg">

        <div class="flex items-center space-x-2 mb-4">

            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>

            <span class="text-gray-700 font-semibold">Delivery Address</span>

        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoadingStore || isLoadingOrder" class="w-full border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50 space-y-1">
            <Skeleton width="w-4/5" :shine="true"></Skeleton>
            <Skeleton width="w-1/3" :shine="true"></Skeleton>
        </div>

        <div v-else>

            <p class="text-sm mb-4">{{ deliveryAddress.complete_address }}</p>

            <template v-if="deliveryAddress.latitude && deliveryAddress.latitude">

                <!-- Google Maps -->
                <div
                    class="rounded-lg overflow-hidden">

                    <GoogleMaps
                        height="350px"
                        :gmpDraggable="false"
                        :latitude="deliveryAddress.latitude"
                        :longitude="deliveryAddress.longitude">
                    </GoogleMaps>

                </div>

                <div class="flex justify-end mt-4">

                    <Button
                        size="xs"
                        type="light"
                        :action="openGoogleMap"
                        :rightIcon="ExternalLink">
                        <span>Google Maps</span>
                    </Button>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { ExternalLink } from 'lucide-vue-next';
    import GoogleMaps from '@Partials/GoogleMaps.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button, Skeleton, GoogleMaps, ExternalLink },
        data() {
            return {
                ExternalLink
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasDeliveryAddress() {
                return this.deliveryAddress != null;
            },
            deliveryAddress() {
                return this.order.delivery_address;
            },
            googleMapsUrl() {
                return `https://www.google.com/maps?q=${this.deliveryAddress.latitude},${this.deliveryAddress.longitude}`;
            }
        },
        methods: {
            openGoogleMap() {
                window.open(this.googleMapsUrl, '_blank');
            }
        }
    };

</script>

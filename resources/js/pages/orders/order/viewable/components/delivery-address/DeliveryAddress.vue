<template>

    <div class="bg-white rounded-lg p-4 mb-4">

        <div class="flex items-center space-x-2 mb-4">

            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>

            <span class="text-gray-700 font-semibold">Delivery Address</span>

        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="w-full border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50 space-y-1">
            <Skeleton width="w-4/5" :shine="true"></Skeleton>
            <Skeleton width="w-1/3" :shine="true"></Skeleton>
        </div>

        <div v-else>

            <p class="text-sm mb-4">{{ deliveryAddress.complete_address }}</p>

            <div class="flex justify-end">

                <!-- Collection Status -->
                <CollectionStatus class="mb-4"></CollectionStatus>

            </div>

            <!-- Google Maps -->
            <div class="rounded-lg overflow-hidden">

                <GoogleMaps
                    height="350px"
                    :gmpDraggable="false"
                    :latitude="deliveryAddress.latitude"
                    :longitude="deliveryAddress.longitude">
                </GoogleMaps>

            </div>

            <div v-if="googleMapsUrl" class="flex justify-end mt-4">

                <Button
                    size="xs"
                    type="light"
                    buttonClass="h-full"
                    :rightIcon="ExternalLink"
                    :action="openGoogleMapsLink">
                    <span>Google Maps</span>
                </Button>

            </div>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { ExternalLink } from 'lucide-vue-next';
    import GoogleMaps from '@Partials/GoogleMaps.vue';
    import CollectionStatus from '@Pages/orders/order/components/order-header/CollectionStatus.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button, Skeleton, GoogleMaps, CollectionStatus },
        data() {
            return {
                ExternalLink
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasDeliveryAddress() {
                return this.order?.delivery_address != null;
            },
            deliveryAddress() {
                const address = this.order?.delivery_address || {};
                return {
                    ...address,
                    latitude: address.latitude ? parseFloat(address.latitude) : null,
                    longitude: address.longitude ? parseFloat(address.longitude) : null
                };
            },
            googleMapsUrl() {
                if (!this.isLoadingOrder && this.deliveryAddress.latitude && this.deliveryAddress.longitude) {
                    return `https://www.google.com/maps?q=${this.deliveryAddress.latitude},${this.deliveryAddress.longitude}`;
                }
                return null;
            }
        },
        methods: {
            openGoogleMapsLink() {
                window.open(this.googleMapsUrl, '_blank');
            }
        }
    };

</script>

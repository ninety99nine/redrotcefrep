<template>

    <div v-if="isLoadingOrder || hasDeliveryAddress" class="bg-white rounded-lg p-4 mb-4">

        <div class="flex items-center space-x-2 mb-4">

            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>

            <span class="text-gray-700 font-semibold">Delivery Address</span>

        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoadingOrder || !hasOrder" class="w-full border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50 space-y-1">
            <LineSkeleton width="w-4/5" :shine="true"></LineSkeleton>
            <LineSkeleton width="w-1/3" :shine="true"></LineSkeleton>
        </div>

        <div v-else>

            <p class="text-sm mb-4">{{ deliveryAddress._attributes.completeAddress }}</p>

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

                <a :href="googleMapsUrl" target="_blank">
                    <Button type="light" size="xs" icon="externalLink">
                        <span>Google Maps</span>
                    </Button>
                </a>

            </div>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/buttons/Button.vue';
    import GoogleMaps from '@Partials/maps/GoogleMaps.vue';
    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button, GoogleMaps, LineSkeleton },
        computed: {
            order() {
                return this.orderState.order;
            },
            hasOrder() {
                return this.orderState.hasOrder;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            hasDeliveryAddress() {
                return this.order._relationships.deliveryAddress != null;
            },
            deliveryAddress() {
                const address = this.order._relationships.deliveryAddress || {};
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
        }
    };

</script>

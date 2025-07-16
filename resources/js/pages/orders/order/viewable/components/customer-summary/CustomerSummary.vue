<template>

    <div class="bg-white rounded-lg space-y-3 p-4 mb-4">

        <div class="flex items-center space-x-2">

            <div class="p-1.5 bg-gray-100 rounded-full border border-gray-200">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-full" height="h-4" :shine="true"></Skeleton>
            <span v-else-if="order.customer_name" class="text-gray-700 font-semibold truncate">{{ order.customer_name }}</span>
            <span v-else class="text-gray-700 font-semibold">Customer</span>

        </div>

        <div class="space-y-2 ml-2">

            <template v-if="isLoadingStore || isLoadingOrder || !hasOrder || !order.customer_mobile_number || order.customer_email">

                <div v-if="isLoadingStore || isLoadingOrder || !hasOrder || order.customer_mobile_number" class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-3/5" :shine="true"></Skeleton>
                    <span v-else class="text-sm">{{ order.customer_mobile_number.national }}</span>
                </div>

                <div v-if="isLoadingStore || isLoadingOrder || !hasOrder || order.customer_email" class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <Skeleton v-if="isLoadingStore || isLoadingOrder || !hasOrder" width="w-1/3" :shine="true"></Skeleton>
                    <span v-else class="text-sm truncate">{{ order.customer_email }}</span>
                </div>

            </template>

            <span v-else class="text-sm">No information</span>

        </div>

        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder || (hasOrder && hasCustomer)" class="flex justify-end mt-4">

            <Button
                size="xs"
                type="light"
                :rightIcon="ArrowRight"
                :action="navigateToCustomer"
                :skeleton="isLoadingStore || isLoadingOrder || !hasOrder">
                <span>Show Profile</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ArrowRight } from 'lucide-vue-next';
    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['storeState', 'orderState'],
        components: { Button, Skeleton },
        data() {
            return {
                ArrowRight
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
            hasCustomer() {
                return this.order.customer != null;
            },
            customer() {
                return this.order.customer;
            }
        },
        methods: {
            navigateToCustomer() {

                this.$router.push({
                    name: 'show-customer',
                    params: {
                        'customer_id': this.customer.id
                    },
                    query: {
                        'store_id': this.store.id
                    }
                });

            }
        }
    };

</script>

<template>

    <div class="bg-white rounded-lg p-4 mb-4">

        <div class="flex items-center space-x-2 mb-4">

            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>

            <span class="text-gray-700 font-semibold">Payment Link</span>

        </div>

        <!-- Order Team Member (Loading Placeholder) -->
        <div v-if="isLoadingStore || isLoadingOrder || !hasOrder" class="flex items-center w-full border-b shadow-sm rounded-lg p-4 bg-gray-50 space-x-1">

            <Skeleton width="w-full" height="h-8" rounded="rounded-md" :shine="true"></Skeleton>
            <Skeleton width="w-8" height="h-8" rounded="rounded-md" :shine="true" class="flex-shrink-0"></Skeleton>

        </div>

        <div v-else class="flex space-x-1 items-stretch">

            <Copy
                class="w-full"
                :text="paymentLink"
                :placeholder="`...`"
                :loading="isLoadingOrder || !hasOrder">
            </Copy>

            <Button
                size="sm"
                type="light"
                buttonClass="h-full"
                :action="openPaymentLink"
                :rightIcon="ExternalLink">
            </Button>

        </div>

    </div>

</template>

<script>

    import Copy from '@Partials/Copy.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { ExternalLink } from 'lucide-vue-next';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Copy, Button, Skeleton },
        data() {
            return {
                ExternalLink
            }
        },
        computed: {
            store() {
                return this.storeState.store;
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
            paymentLink() {
                return 'https://www.google.com';
            }
        },
        methods: {
            openPaymentLink() {
                window.open(this.paymentLink, '_blank');
            }
        }
    };

</script>

<template>

    <div class="bg-white overflow-hidden rounded-lg space-y-4 p-4">

        <h1 class="text-lg text-gray-700 font-semibold mb-4">Comments</h1>

        <!-- Order Payment (Loading Placeholder) -->
        <div v-if="isLoadingOrder || !hasOrder" class="space-y-2">

            <div v-for="(_, index) in [1, 2, 3]" :key="index" class="border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50">
                <div class="w-full flex items-center justify-between">
                    <Skeleton width="w-32" :shine="true"></Skeleton>
                    <Skeleton width="w-16" :shine="true"></Skeleton>
                </div>
            </div>

        </div>

        <div v-else>
            Comments go here
        </div>

    </div>

</template>

<script>

    import Skeleton from '@Partials/Skeleton.vue';

    export default {
        inject: ['orderState'],
        components: { Skeleton },
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
        },
    };

</script>

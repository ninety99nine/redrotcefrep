<template>
    <div class="bg-white rounded-lg p-4 mb-4">

        <h1 class="text-lg text-gray-700 font-semibold pb-4">Order History</h1>

        <div class="relative">

            <div class="max-h-80 overflow-y-auto py-4 px-4 border-t border-b">

                <!-- Timeline Vertical Line -->
                <div class="absolute left-2 top-0 bottom-40 h-full w-0.5 bg-gray-200"></div>

                <!-- Order History (Loading Placeholder) -->
                <div v-if="isLoadingOrder || !hasOrder" class="space-y-6">

                    <div
                        :key="index"
                        v-for="(_, index) in [1, 2, 3]"
                         class="relative flex items-start space-x-4">

                        <!-- Timeline Dot -->
                        <div class="w-4 h-4 bg-gray-200 rounded-full absolute left-[-15px] top-0.5"></div>

                        <!-- Skeleton Loading -->
                        <div class="border-b shadow-sm rounded-lg py-6 px-4 bg-gray-50 space-y-1 w-full">
                            <LineSkeleton width="w-2/3" :shine="true"></LineSkeleton>
                            <LineSkeleton width="w-1/3" :shine="true"></LineSkeleton>
                        </div>

                    </div>

                </div>

                <!-- Order History (Grouped by Date) -->
                <div v-else class="text-gray-700 space-y-6">

                    <div
                        class="relative"
                        v-for="(items, date, index) in sortedGroupedOrderHistory" :key="index">

                        <!-- Timeline Dot (Only for new date) -->
                        <div class="w-4 h-4 bg-gray-200 rounded-full absolute left-[-15px] top-0.5"></div>

                        <!-- Date Header -->
                        <p class="text-sm font-semibold text-gray-500 ml-2 mb-2">{{ date }}</p>

                        <!-- Order History Items -->
                        <div class="space-y-2">

                            <div
                                :key="itemIndex"
                                v-for="(item, itemIndex) in items"
                                class="relative flex items-start space-x-4">

                                <div class="ml-4">
                                    <p class="text-sm">{{ item.comment }}</p>
                                    <p class="text-xs text-gray-400">{{ formattedTime(item.createdAt) }}</p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import LineSkeleton from '@Partials/skeletons/LineSkeleton.vue';
    import { formattedLongDate, formattedTime } from '@Utils/dateUtils.js';

    export default {
        inject: ['orderState'],
        components: { LineSkeleton },
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
            orderHistory() {
                return this.isLoadingOrder ? [] : this.order._relationships.orderHistory;
            },
            groupedOrderHistory() {
                if (!this.orderHistory.length) return {};

                return this.orderHistory.reduce((acc, item) => {
                    let date = this.formattedLongDate(item.createdAt); // Jul 14, 2024
                    if (!acc[date]) {
                        acc[date] = [];
                    }
                    acc[date].push(item);
                    return acc;
                }, {});
            },
            sortedGroupedOrderHistory() {
                // Sort dates in descending order
                let sortedDates = Object.keys(this.groupedOrderHistory).sort((a, b) => {
                    return new Date(b) - new Date(a); // Newest date first
                });

                // Sort items within each date in descending order based on time
                let sortedHistory = {};
                sortedDates.forEach(date => {
                    sortedHistory[date] = this.groupedOrderHistory[date].sort((a, b) => {
                        return new Date(b.createdAt) - new Date(a.createdAt); // Newest time first
                    });
                });

                return sortedHistory;
            }
        },
        methods: {
            formattedTime: formattedTime,
            formattedLongDate: formattedLongDate
        }
    };

</script>

<template>

    <div class="flex space-x-2 items-center">

        <!-- Previous Order -->
        <Button
            size="xs"
            type="light"
            :leftIcon="ChevronLeft"
            :disabled="!hasPreviousOrder"
            :action="() => onView('prev')"
            :skeleton="isLoadingStore || isLoadingOrder">
        </Button>

        <!-- Next Order -->
        <Button
            size="xs"
            type="light"
            :leftIcon="ChevronRight"
            :disabled="!hasNextOrder"
            :action="() => onView('next')"
            :skeleton="isLoadingStore || isLoadingOrder">
        </Button>

    </div>

</template>

<script>

    import axios from 'axios';
    import Button from '@Partials/Button.vue';
    import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

    export default {
        inject: ['formState', 'orderState', 'storeState', 'notificationState'],
        components: { Button },
        data() {
            return {
                ChevronLeft,
                ChevronRight,
                pagination: null,
                searchTerm: null,
                filterExpressions: [],
                sortingExpressions: [],
                isLoadingOrders: false
            }
        },
        watch: {
            isLoadingOrder(newValue) {
                if(!newValue) {
                    this.getOrders();
                }
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            store() {
                return this.storeState.store;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasSearchTerm() {
                return this.searchTerm != null && this.searchTerm.trim() != '';
            },
            hasFilterExpressions() {
                return this.filterExpressions.length > 0;
            },
            hasSortingExpressions() {
                return this.sortingExpressions.length > 0;
            },
            hasNextOrder() {
                if(this.isLoadingOrder || this.isLoadingOrders || !this.pagination || !this.pagination.data.length) return false;

                let currentIndex = this.pagination.data.findIndex(order => order.id === this.order.id);

                // Check if there's another order in the same page OR another page exists
                return currentIndex < this.pagination.data.length - 1 || this.pagination.nextPageUrl !== null;
            },
            hasPreviousOrder() {
                if(this.isLoadingOrder || this.isLoadingOrders || !this.pagination || !this.pagination.data.length) return false;

                let currentIndex = this.pagination.data.findIndex(order => order.id === this.order.id);

                // Check if there's a previous order in the same page OR another page exists
                return currentIndex > 0 || this.pagination.prevPageUrl !== null;
            }
        },
        methods: {
            onView(direction) {

                let currentIndex = this.pagination.data.findIndex(order => order.id === this.order.id);

                const query = {
                    searchTerm: this.searchTerm,
                    filterExpressions: this.filterExpressions.join('|'),
                    sortingExpressions: this.sortingExpressions.join('|'),
                }

                if(direction === 'next') {

                    if(currentIndex < this.pagination.data.length - 1) {

                        // Go to next order in the same page
                        this.$router.push({
                            name: 'show-order',
                            query: query,
                            params: {
                                store_id: this.store.id,
                                'order_id': this.pagination.data[currentIndex + 1].id
                            }
                        });

                    }else if(this.pagination.nextPageUrl) {

                        // Load the next page of orders
                        this.getOrders(this.pagination.nextPageUrl);

                    }

                }else if(direction === 'prev') {

                    if(currentIndex > 0) {

                        // Go to previous order in the same page
                        this.$router.push({
                            name: 'show-order',
                            query: query,
                            params: {
                                store_id: this.store.id,
                                'order_id': this.pagination.data[currentIndex - 1].id
                            }
                        });

                    }else if(this.pagination.prevPageUrl) {

                        // Load the previous page of orders
                        this.getOrders(this.pagination.prevPageUrl);

                    }

                }

            },
            async getOrders(url = null) {

                try {

                    this.isLoadingOrders = true;

                    let config = {};

                    if(url == null) {

                        url = `/api/orders`;

                        config = {
                            params: {
                                'per_page': this.perPage,
                                store_id: this.store.id
                            }
                        }

                        if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                        if(this.hasFilterExpressions) {
                            config.params['_filters'] = this.filterExpressions.join('|');
                        }

                        if(this.hasSortingExpressions) {
                            config.params['_sort'] = this.sortingExpressions.join('|');
                        }

                    }

                    const response = await axios.get(url, config);

                    this.pagination = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching orders';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch orders:', error);
                } finally {
                    this.isLoadingOrders = false;
                }

            }
        },
        created() {
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
        }
    };

</script>

<template>

    <div class="flex space-x-2 items-center">

        <!-- Previous Order -->
        <Button
            size="xs"
            type="light"
            :leftIcon="ChevronLeft"
            :disabled="!hasPreviousOrder"
            :action="() => onView('prev')"
            :skeleton="isLoadingStore || isLoadingOrder || !hasOrder">
        </Button>

        <!-- Next Order -->
        <Button
            size="xs"
            type="light"
            :leftIcon="ChevronRight"
            :disabled="!hasNextOrder"
            :action="() => onView('next')"
            :skeleton="isLoadingStore || isLoadingOrder || !hasOrder">
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
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                isLoadingOrders: false,
                cancelTokenSource: null
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
            hasOrder() {
                return this.orderState.hasOrder;
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
                if(this.isLoadingStore || this.isLoadingOrder || this.isLoadingOrders || !this.pagination || !this.pagination.data.length) return false;

                let currentIndex = this.pagination.data.findIndex(orderId => orderId === this.order.id);

                // Check if there's another order in the same page OR another page exists
                return currentIndex < this.pagination.data.length - 1 || this.pagination.next_page_url !== null;
            },
            hasPreviousOrder() {
                if(this.isLoadingStore || this.isLoadingOrder || this.isLoadingOrders || !this.pagination || !this.pagination.data.length) return false;

                let currentIndex = this.pagination.data.findIndex(orderId => orderId === this.order.id);

                // Check if there's a previous order in the same page OR another page exists
                return currentIndex > 0 || this.pagination.prev_page_url !== null;
            }
        },
        methods: {
            onView(direction) {

                let currentIndex = this.pagination.data.findIndex(orderId => orderId === this.order.id);

                const query = {
                    store_id: this.store.id,
                    searchTerm: this.searchTerm,
                    filterExpressions: this.filterExpressions.join('|'),
                    sortingExpressions: this.sortingExpressions.join('|')
                }

                if(direction === 'next') {

                    if(currentIndex < this.pagination.data.length - 1) {

                        // Go to next order in the same page
                        this.$router.push({
                            name: 'show-order',
                            params: {
                                order_id: this.pagination.data[currentIndex + 1]
                            },
                            query: query
                        });

                    }else if(this.pagination.next_page_url) {

                        // Load the next page of orders
                        const page = new URL(this.pagination.next_page_url).searchParams.get('page');
                        this.getOrders(page);

                    }

                }else if(direction === 'prev') {

                    if(currentIndex > 0) {

                        // Go to previous order in the same page
                        this.$router.push({
                            name: 'show-order',
                            params: {
                                order_id: this.pagination.data[currentIndex - 1]
                            },
                            query: query
                        });

                    }else if(this.pagination.prev_page_url) {

                        // Load the previous page of orders
                        const page = new URL(this.pagination.prev_page_url).searchParams.get('page');
                        this.getOrders(page);

                    }

                }

            },
            async getOrders(page = 1) {

                const currentRequestId = ++this.latestRequestId;

                try {

                    this.isLoadingOrders = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one'); // Cancel previous request if it exists
                    }

                    this.cancelTokenSource = axios.CancelToken.source(); // Create a new cancel token source

                    let config = {
                        params: {
                            page: page,
                            _pagination: '1',
                            store_id: this.store.id
                        },
                        cancelToken: this.cancelTokenSource.token // Attach cancel token
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;

                    if(this.hasFilterExpressions) {
                        config.params['_filters'] = this.filterExpressions.join('|');
                    }

                    if(this.hasSortingExpressions) {
                        config.params['_sort'] = this.sortingExpressions.join('|');
                    }

                    const response = await axios.get(`/api/orders`, config);

                    // Only process response if it matches the latest request
                    if (currentRequestId !== this.latestRequestId) return;

                    this.pagination = response.data;

                } catch (error) {

                    if (axios.isCancel(error)) return; // Ignore canceled requests

                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching order navigation';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch order navigation:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingOrders = false;
                    this.cancelTokenSource = null;
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

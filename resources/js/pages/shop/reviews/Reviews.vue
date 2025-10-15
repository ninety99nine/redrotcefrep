<template>
    <div>

        <Menu menuClass="max-w-4xl mx-auto"></Menu>

        <div class="max-w-4xl mx-auto pt-8 pb-40">

            <div v-if="isLoadingStore" class="pt-8 flex items-center justify-center">
                <Loader>
                    <span class="text-sm ml-2">Loading store</span>
                </Loader>
            </div>

            <template v-else>

                <div class="select-none">

                    <!-- Heading and Add Review Button -->
                    <div class="flex justify-between items-end mb-4">

                        <div class="space-y-2">
                            <h1 class="text-lg font-semibold">{{ store.name }}</h1>
                            <h1 class="text-lg font-semibold">Customer Reviews</h1>
                        </div>

                        <Button
                            size="sm"
                            type="primary"
                            :leftIcon="Star"
                            :action="onAddReview">
                            <span class="ml-1">Write A Review</span>
                        </Button>

                    </div>

                    <!-- Search Bar -->
                    <div class="flex justify-center items-center space-x-4 mb-4">

                        <Input
                            type="search"
                            :debounced="true"
                            class="w-full"
                            v-model="searchTerm"
                            :skeleton="isLoadingStore"
                            placeholder="Search by name, comment or rating e.g 3"
                            @input="isLoadingReviews = true">
                        </Input>

                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoadingReviews" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="i in 4" :key="i" class="p-4 bg-gray-100 rounded-lg">
                            <Skeleton width="w-1/2" :shine="true" class="mb-2"></Skeleton>
                            <Skeleton width="w-3/4" :shine="true" class="mb-2"></Skeleton>
                            <Skeleton width="w-full" height="h-4" :shine="true"></Skeleton>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div v-else-if="hasReviews" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div
                            :key="review.id"
                            v-for="review in reviews"
                            class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all duration-300">

                            <div class="flex items-center space-x-2 mb-2">

                                <div class="flex space-x-1">

                                    <Star
                                        :size="16"
                                        :key="star"
                                        v-for="star in 5"
                                        :fill="star <= review.rating ? 'gold' : 'none'"
                                        :stroke="star <= review.rating ? 'gold' : 'gray'"
                                    />

                                </div>

                                <span class="text-sm font-medium text-gray-900">{{ review.rating }}/5</span>

                            </div>

                            <p v-if="review.comment" class="text-sm text-gray-900 mb-2">{{ review.comment }}</p>

                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">{{ review.name || 'Anonymous' }}</span>
                                <span class="text-xs text-gray-400">•</span>
                                <span class="text-xs text-gray-600">{{ formattedRelativeDate(review.created_at) }}</span>
                            </div>

                        </div>
                    </div>

                    <!-- No Reviews -->
                    <p v-else class="text-sm text-center p-4 rounded-lg bg-gray-50 mb-4">
                        No reviews found. Be the first to share your experience!
                    </p>

                </div>

            </template>

        </div>

    </div>

</template>

<script>

    import Pill from '@Partials/Pill.vue';
    import { Star } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isNotEmpty } from '@Utils/stringUtils.js';
    import Menu from '@Pages/shop/_components/menu/Menu.vue';
    import { formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Pill,Input,Loader,Button,Skeleton,Menu,Star
        },
        data() {
            return {
                Star,
                reviews: [],
                searchTerm: null,
                lastSearchTerm: null,
                isLoadingReviews: false,
                hasLoadedInitialReviews: false
            };
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            searchTerm() {
                this.showReviews();
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            hasSearchTerm() {
                return isNotEmpty(this.searchTerm);
            },
            hasReviews() {
                return this.reviews.length > 0;
            }
        },
        methods: {
            isNotEmpty,
            formattedRelativeDate,
            async setup() {
                if (this.store) {
                    if (!this.hasLoadedInitialReviews) {
                        await this.showReviews();
                    }
                }
            },
            onAddReview() {
                this.$router.push({
                    name: 'create-shop-review',
                    query: {
                        alias: this.store.alias
                    }
                });
            },
            async showReviews() {
                try {
                    this.isLoadingReviews = true;
                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };
                    if (this.hasSearchTerm) {
                        config.params['search'] = this.searchTerm;
                    }
                    this.lastSearchTerm = this.searchTerm;
                    const response = await axios.get(`/api/reviews`, config);
                    if (this.searchTerm === this.lastSearchTerm) {
                        const pagination = response.data;
                        this.reviews = pagination.data;
                        this.hasLoadedInitialReviews = true;
                    }
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching reviews';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch reviews:', error);
                } finally {
                    this.isLoadingReviews = false;
                }
            }
        },
        created() {
            this.setup();
        }
    };
</script>

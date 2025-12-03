<template>

    <div class="max-w-4xl mx-auto pb-40">

        <div v-if="isLoadingStore || isLoadingUser" class="pt-8 flex items-center justify-center">
            <Loader>
                <span class="text-sm ml-2">Loading...</span>
            </Loader>
        </div>

        <template v-else>

            <div class="select-none relative bg-white rounded-lg space-y-4 p-4">

                    <BackdropLoader v-if="isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="flex items-center space-x-4">

                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="navigateToShowShopReviews"
                            :leftIcon="MoveLeft">
                            <span>Reviews</span>
                        </Button>

                        <h1 class="sm:text-lg text-xl font-semibold">Write A Review</h1>

                    </div>

                    <template v-if="reviewForm">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-gray-300 shadow rounded-lg p-4 space-y-4">

                            <!-- Name Input -->
                            <Input
                                type="text"
                                label="Name"
                                placeholder="Your name"
                                v-model="reviewForm.name"
                                secondaryLabel="(optional)"
                                :errorText="formState.getFormError('name')">
                            </Input>

                            <!-- Mobile Number Input -->
                            <Input
                                type="text"
                                label="Mobile Number"
                                placeholder="+26772000001"
                                secondaryLabel="(optional)"
                                v-model="reviewForm.mobile_number"
                                :errorText="formState.getFormError('mobile_number')">
                            </Input>

                        </div>

                        <div class="border border-gray-300 shadow rounded-lg p-4 space-y-4">

                            <!-- Rating Input with Stars -->
                            <div>

                                <label class="text-sm leading-6 font-medium text-gray-900">Rating</label>

                                <div class="flex space-x-1 mt-2">

                                    <Star
                                        :size="32"
                                        :key="star"
                                        v-for="star in 5"
                                        class="cursor-pointer"
                                        @click="setRating(star)"
                                        :fill="star <= reviewForm.rating ? 'gold' : 'none'"
                                        :stroke="star <= reviewForm.rating ? 'gold' : 'gray'"
                                    />

                                </div>

                                <p v-if="formState.getFormError('rating')" class="mt-1 text-sm text-red-600">
                                    {{ formState.getFormError('rating') }}
                                </p>

                            </div>

                            <!-- Comment Textarea -->
                            <Input
                                rows="2"
                                type="textarea"
                                label="Comment"
                                v-model="reviewForm.comment"
                                placeholder="Share your experience..."
                                :errorText="formState.getFormError('comment')"
                                tooltipContent="A short description of your feedback">
                            </Input>

                            <!-- Submit Button -->
                            <div class="flex justify-end">

                                <Button
                                    size="sm"
                                    type="primary"
                                    :leftIcon="Star"
                                    :action="createReview"
                                    :loading="isSubmitting">
                                    <span>Send Review</span>
                                </Button>

                            </div>

                        </div>

                    </template>

            </div>

        </template>

    </div>

</template>

<script>

    import axios from 'axios';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isEmpty } from '@Utils/stringUtils.js';
    import { Star, MoveLeft } from 'lucide-vue-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';

    export default {
        inject: ['formState', 'reviewState', 'storeState', 'notificationState'],
        components: {
            Input,Button,Loader,Skeleton,BackdropLoader,Star,MoveLeft
        },
        data() {
            return {
                Star,
                MoveLeft,
                isSubmitting: false,
                isLoadingUser: false
            };
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            reviewForm() {
                return this.reviewState.reviewForm;
            }
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            }
        },
        methods: {
            isEmpty,
            async setup() {
                if (this.store) {
                    this.reviewState.setReviewForm(null, true);
                }
            },
            navigateToShowShopReviews() {
                this.$router.push({
                    name: 'show-shop-reviews',
                    query: {
                        alias: this.store.alias
                    }
                });
            },
            setRating(rating) {
                this.reviewForm.rating = rating;
            },
            async createReview() {
                try {
                    if (this.isSubmitting) return;

                    this.formState.hideFormErrors();

                    if (this.isEmpty(this.reviewForm.rating)) {
                        this.formState.setFormError('rating', 'The rating is required');
                    }

                    if (this.reviewForm.rating < 1 || this.reviewForm.rating > 5) {
                        this.formState.setFormError('rating', 'Rating must be between 1 and 5');
                    }

                    if (this.formState.hasErrors) {
                        return;
                    }

                    this.isSubmitting = true;

                    const data = {
                        ...this.reviewForm,
                        store_id: this.store.id
                    };

                    const response = await axios.post('/api/reviews', data);
                    this.notificationState.showSuccessNotification('Review submitted! It will be visible after approval.');

                    await this.navigateToShowShopReviews();
                    this.reviewState.reset();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while submitting your review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create review:', error);
                } finally {
                    this.isSubmitting = false;
                }
            }
        },
        created() {
            this.setup();
        },
        beforeRouteLeave(to, from, next) {
            if (this.reviewState.reviewForm && (this.reviewForm.rating || this.reviewForm.comment || this.reviewForm.name || this.reviewForm.mobile_number)) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            this.reviewState.reset();
            next();
        }
    };
</script>

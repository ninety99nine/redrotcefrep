<template>

    <div class="pt-24 px-4 pb-80">

        <div class="grid grid-cols-12 gap-4 mb-4">

            <div class="col-span-8 col-start-3">

                <div class="select-none bg-white rounded-lg p-4 mb-4">

                    <div class="flex items-center space-x-4">

                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="goBack"
                            :leftIcon="MoveLeft">
                            <span>Back</span>
                        </Button>

                        <div v-if="isLoadingStore || isLoadingReview || (isEditing && !hasReview)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>

                        <template v-else>

                            <!-- Heading -->
                            <div class="flex items-center space-x-1">
                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isCreating ? 'Add Review' : `Review #${reviewForm.id || '...'}` }}
                                </h1>
                                <Popover content="Reviews allow customers to share feedback on their experience with your store." placement="top"></Popover>
                            </div>

                        </template>

                    </div>

                </div>

                <div class="relative mb-4">

                    <BackdropLoader v-if="isLoadingReview || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>

                    <div class="bg-white rounded-lg space-y-4 p-4">

                        <div class="grid grid-cols-2 gap-4">

                            <!-- Rating Input with Stars -->
                            <div>

                                <label class="text-sm leading-6 font-medium text-gray-900">Rating</label>

                                <div class="flex space-x-1 mt-2">

                                    <Star
                                        :size="24"
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

                            <!-- Visibility Select -->
                            <Select
                                class="w-full"
                                :search="false"
                                label="Visibility"
                                :options="visibilityTypes"
                                v-model="reviewForm.visible"
                                :errorText="formState.getFormError('visible')"
                                @change="reviewState.saveStateDebounced('Visibility changed')"
                                tooltipContent="Set whether the review is visible to others">
                            </Select>

                        </div>

                        <!-- Name Input -->
                        <Input
                            type="text"
                            label="Name"
                            placeholder="John Doe"
                            v-model="reviewForm.name"
                            :errorText="formState.getFormError('name')"
                            @input="reviewState.saveStateDebounced('Name changed')">
                        </Input>

                        <!-- Mobile Number Input -->
                        <Input
                            type="text"
                            label="Mobile Number"
                            placeholder="+26772000001"
                            v-model="reviewForm.mobile_number"
                            :errorText="formState.getFormError('mobile_number')"
                            @input="reviewState.saveStateDebounced('Mobile number changed')">
                        </Input>

                        <!-- Comment Textarea -->
                        <Input
                            rows="2"
                            type="textarea"
                            label="Comment"
                            v-model="reviewForm.comment"
                            :errorText="formState.getFormError('comment')"
                            placeholder="Great experience, highly recommend!"
                            @input="reviewState.saveStateDebounced('Comment changed')"
                            tooltipContent="A short description of the customer's feedback">
                        </Input>

                        <div
                            v-if="review"
                            class="flex space-x-1 items-center justify-end">
                            <span class="text-sm">{{ formattedDatetime(review.created_at) }}</span>
                            <Popover
                                placement="top"
                                :content="formattedRelativeDate(review.created_at)">
                            </Popover>
                        </div>

                    </div>

                </div>

                <div
                    v-if="review"
                    :class="['overflow-hidden rounded-lg space-y-4 p-4 border mb-20', isLoadingReview ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">

                    <!-- Delete Review Info -->
                    <p>Do you want to permanently delete this review? Once deleted, it cannot be recovered.</p>

                    <div class="flex justify-end">

                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Delete Review"
                            approveText="Delete Review"
                            :approveAction="deleteReview"
                            :triggerLoading="isDeletingReview"
                            :approveLoading="isDeletingReview">
                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Delete</p>
                                <p class="mb-8">Are you sure you want to permanently delete this review?</p>
                            </template>
                        </Modal>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import BackdropLoader from '@Partials/BackdropLoader.vue';
    import { Trash2, MoveLeft, Star } from 'lucide-vue-next';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'storeState', 'reviewState', 'changeHistoryState', 'notificationState'],
        components: {
            Input,Modal,Button,Select,Popover,Skeleton,BackdropLoader,Trash2,MoveLeft,Star
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                Star,
                visibilityTypes: [
                    { label: 'Visible', value: true },
                    { label: 'Hidden', value: false }
                ]
            };
        },
        watch: {
            store(newValue, oldValue) {
                if (!oldValue && newValue) {
                    this.setup();
                }
            },
            reviewId(newValue) {
                if (newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            review() {
                return this.reviewState.review;
            },
            hasReview() {
                return this.reviewState.hasReview;
            },
            reviewId() {
                return this.$route.params.review_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingReview() {
                return this.reviewState.isLoadingReview;
            },
            isEditing() {
                return this.$route.name === 'edit-review';
            },
            isCreating() {
                return this.$route.name === 'create-review';
            },
            reviewForm() {
                return this.reviewState.reviewForm;
            },
            isSubmitting() {
                if (this.changeHistoryState.actionButtons.length === 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isDeletingReview() {
                return this.reviewState.isDeletingReview;
            }
        },
        methods: {
            isEmpty,
            formattedDatetime,
            formattedRelativeDate,
            goBack() {
                this.navigateToReviews();
            },
            setRating(rating) {
                this.reviewForm.rating = rating;
                this.reviewState.saveStateDebounced('Rating changed');
            },
            async setup() {
                if (this.reviewForm == null) this.reviewState.setReviewForm(null, this.isCreating);
                if (this.isEditing && this.store) await this.showReview();
            },
            async navigateToReviews() {
                await this.$router.replace({
                    name: 'show-reviews',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async navigateToEditReview(review) {
                await this.$router.push({
                    name: 'edit-review',
                    params: {
                        review_id: review.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async showReview() {
                try {
                    this.reviewState.isLoadingReview = true;
                    let config = {
                        params: {
                            store_id: this.store.id
                        }
                    };
                    const response = await axios.get(`/api/reviews/${this.reviewId}`, config);
                    const review = response.data;
                    this.reviewState.setReview(review);
                    this.reviewState.setReviewForm(review);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch review:', error);
                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-reviews',
                            query: {
                                store_id: this.store.id,
                                searchTerm: this.$route.query.searchTerm,
                                filterExpressions: this.$route.query.filterExpressions,
                                sortingExpressions: this.$route.query.sortingExpressions
                            }
                        });
                    }
                } finally {
                    this.reviewState.isLoadingReview = false;
                }
            },
            async createReview() {
                try {
                    if (this.reviewState.isCreatingReview) return;
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
                    this.reviewState.isCreatingReview = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = {
                        ...this.reviewForm,
                        store_id: this.store.id
                    };
                    const response = await axios.post(`/api/reviews`, data);
                    const createdReview = response.data.review;
                    this.notificationState.showSuccessNotification(`Review created`);
                    this.reviewState.saveOriginalState('Original review');
                    await this.navigateToEditReview(createdReview);
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create review:', error);
                } finally {
                    this.reviewState.isCreatingReview = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async updateReview() {
                try {
                    if (this.reviewState.isUpdatingReview) return;
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
                    this.reviewState.isUpdatingReview = true;
                    this.changeHistoryState.actionButtons[1].loading = true;
                    const data = {
                        ...this.reviewForm,
                        store_id: this.store.id
                    };
                    await axios.put(`/api/reviews/${this.reviewForm.id}`, data);
                    this.notificationState.showSuccessNotification(`Review updated`);
                    this.reviewState.saveOriginalState('Original review');
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update review:', error);
                } finally {
                    this.reviewState.isUpdatingReview = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async deleteReview(hideModal) {
                try {
                    if (this.reviewState.isDeletingReview) return;
                    this.reviewState.isDeletingReview = true;
                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    };
                    await axios.delete(`/api/reviews/${this.review.id}`, config);
                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500)); // Wait for modal to close
                    this.notificationState.showSuccessNotification('Review deleted');
                    await this.navigateToReviews();
                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting review';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete review:', error);
                    hideModal();
                } finally {
                    this.reviewState.isDeletingReview = false;
                }
            },
            setActionButtons() {
                if (this.isCreating || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Create Review',
                        this.isEditing ? this.updateReview : this.createReview,
                        'primary',
                        null
                    );
                }
            },
            setReviewForm(reviewForm) {
                this.reviewState.reviewForm = reviewForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.reviewState.reset();
        },
        created() {
            this.setup();
            this.setActionButtons();
            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setReviewForm;
            }
        }
    };
</script>

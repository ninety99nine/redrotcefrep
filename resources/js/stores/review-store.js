import { defineStore } from 'pinia';
import { useAuthStore as authState } from '@Stores/auth-store.js';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useReviewStore = defineStore('review', {
    state: () => ({
        review: null,
        reviewForm: null,
        isLoadingReview: false,
        isCreatingReview: false,
        isUpdatingReview: false,
        isDeletingReview: false,
    }),
    actions: {
        reset() {
            this.review = null;
            this.reviewForm = null;
            this.isLoadingReview = false;
            this.isCreatingReview = false;
            this.isUpdatingReview = false;
            this.isDeletingReview = false;
            changeHistoryState().reset();
        },
        setReview(review) {
            this.review = review;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.reviewForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.reviewForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.reviewForm);
        },
        setReviewForm(review = null, saveState = true) {
            this.reviewForm = {
                id: review?.id ?? null,
                rating: review?.rating ?? null,
                comment: review?.comment ?? null,
                visible: review?.visible ?? true,
                name: review?.name ?? authState().user?.name ?? null,
                mobile_number: review?.mobile_number?.international ?? authState().user?.mobile_number?.international ?? null,
            };

            if (saveState) {
                this.saveOriginalState('Original review');
            }
        }
    },
    getters: {
        hasReview() {
            return this.review != null;
        }
    }
});

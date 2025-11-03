<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Review;
use App\Enums\Association;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewResources;

class ReviewService extends BaseService
{
    /**
     * Show reviews.
     *
     * @param array $data
     * @return ReviewResources|array
     */
    public function showReviews(array $data): ReviewResources|array
    {
        $userId = $data['user_id'] ?? null;
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association == Association::SUPER_ADMIN || $association == Association::TEAM_MEMBER) {
            $query = Review::query();
        } else {
            $query = Review::query()->visible();
        }

        if ($userId) {
            $query = $query->where('user_id', $userId);
        }

        if ($storeId) {
            $query = $query->where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create review.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createReview(array $data): array
    {
        $user = Auth::user();

        if($user) {
            $data['user_id'] = $user->id;
        }

        $storeId = $data['store_id'];
        Store::findOrFail($storeId);

        $review = Review::create($data);

        return $this->showCreatedResource($review);
    }

    /**
     * Update multiple reviews.
     *
     * @param array $data
     * @return array
     */
    public function updateReviews(array $data): array
    {
        $storeId = $data['store_id'];
        $reviewsData = $data['reviews'] ?? [];

        $totalReviews = 0;

        foreach ($reviewsData as $reviewData) {
            $review = Review::where('id', $reviewData['id'])
                ->where('store_id', $storeId)
                ->first();

            if (!$review) {
                continue;
            }

            // Filter fillable fields
            $fillableData = array_intersect_key(
                $reviewData,
                array_flip($review->getFillable())
            );

            // Update review with fillable data
            $review->update($fillableData);

            $totalReviews = $totalReviews + 1;
        }

        return ['updated' => true, 'message' => $totalReviews . ($totalReviews == 1 ? ' review' : ' reviews') . ' updated'];
    }

    /**
     * Delete reviews.
     *
     * @param array $reviewIds
     * @return array
     * @throws \Exception
     */
    public function deleteReviews(array $reviewIds): array
    {
        $reviews = Review::whereIn('id', $reviewIds)->get();

        if ($totalReviews = $reviews->count()) {
            foreach ($reviews as $review) {
                $this->deleteReview($review);
            }

            return ['message' => $totalReviews . ($totalReviews == 1 ? ' Review' : ' Reviews') . ' deleted'];
        } else {
            throw new \Exception('No Reviews deleted');
        }
    }

    /**
     * Show review.
     *
     * @param Review $review
     * @return ReviewResource
     */
    public function showReview(Review $review): ReviewResource
    {
        return $this->showResource($review);
    }

    /**
     * Update review.
     *
     * @param Review $review
     * @param array $data
     * @return array
     */
    public function updateReview(Review $review, array $data): array
    {
        $review->update($data);

        return $this->showUpdatedResource($review);
    }

    /**
     * Delete review.
     *
     * @param Review $review
     * @return array
     * @throws \Exception
     */
    public function deleteReview(Review $review): array
    {
        $deleted = $review->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Review deleted' : 'Review delete unsuccessful'
        ];
    }
}

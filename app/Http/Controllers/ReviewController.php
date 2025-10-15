<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewService;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewResources;
use App\Http\Requests\Review\ShowReviewRequest;
use App\Http\Requests\Review\ShowReviewsRequest;
use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Requests\Review\DeleteReviewRequest;
use App\Http\Requests\Review\DeleteReviewsRequest;
use App\Http\Requests\Review\UpdateReviewsRequest;

class ReviewController extends Controller
{
    /**
     * @var ReviewService
     */
    protected $service;

    /**
     * ReviewController constructor.
     *
     * @param ReviewService $service
     */
    public function __construct(ReviewService $service)
    {
        $this->service = $service;
    }

    /**
     * Show reviews.
     *
     * @param ShowReviewsRequest $request
     * @return ReviewResources|array
     */
    public function showReviews(ShowReviewsRequest $request): ReviewResources|array
    {
        return $this->service->showReviews($request->validated());
    }

    /**
     * Create review.
     *
     * @param CreateReviewRequest $request
     * @return array
     */
    public function createReview(CreateReviewRequest $request): array
    {
        return $this->service->createReview($request->validated());
    }

    /**
     * Update reviews.
     *
     * @param UpdateReviewsRequest $request
     * @return array
     */
    public function updateReviews(UpdateReviewsRequest $request): array
    {
        return $this->service->updateReviews($request->validated());
    }

    /**
     * Delete multiple reviews.
     *
     * @param DeleteReviewsRequest $request
     * @return array
     */
    public function deleteReviews(DeleteReviewsRequest $request): array
    {
        $reviewIds = request()->input('review_ids', []);
        return $this->service->deleteReviews($reviewIds);
    }

    /**
     * Show review.
     *
     * @param ShowReviewRequest $request
     * @param Review $review
     * @return ReviewResource
     */
    public function showReview(ShowReviewRequest $request, Review $review): ReviewResource
    {
        return $this->service->showReview($review);
    }

    /**
     * Update review.
     *
     * @param UpdateReviewRequest $request
     * @param Review $review
     * @return array
     */
    public function updateReview(UpdateReviewRequest $request, Review $review): array
    {
        return $this->service->updateReview($review, $request->validated());
    }

    /**
     * Delete review.
     *
     * @param DeleteReviewRequest $request
     * @param Review $review
     * @return array
     */
    public function deleteReview(DeleteReviewRequest $request, Review $review): array
    {
        return $this->service->deleteReview($review);
    }
}

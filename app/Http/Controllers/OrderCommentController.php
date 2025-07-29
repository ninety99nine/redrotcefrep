<?php

namespace App\Http\Controllers;

use App\Models\OrderComment;
use App\Services\OrderCommentService;
use App\Http\Resources\OrderCommentResource;
use App\Http\Resources\OrderCommentResources;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Requests\OrderComment\ShowOrderCommentRequest;
use App\Http\Requests\OrderComment\ShowOrderCommentsRequest;
use App\Http\Requests\OrderComment\CreateOrderCommentRequest;
use App\Http\Requests\OrderComment\UpdateOrderCommentRequest;
use App\Http\Requests\OrderComment\DeleteOrderCommentRequest;
use App\Http\Requests\OrderComment\DeleteOrderCommentsRequest;

class OrderCommentController extends Controller
{
    /**
     * @var OrderCommentService
     */
    protected $service;

    /**
     * OrderCommentController constructor.
     *
     * @param OrderCommentService $service
     */
    public function __construct(OrderCommentService $service)
    {
        $this->service = $service;
    }

    /**
     * Show order comment comments.
     *
     * @param ShowOrderCommentsRequest $request
     * @return OrderCommentResources|BinaryFileResponse|array
     */
    public function showOrderComments(ShowOrderCommentsRequest $request): OrderCommentResources|BinaryFileResponse|array
    {
        return $this->service->showOrderComments($request->validated());
    }

    /**
     * Create order comment.
     *
     * @param CreateOrderCommentRequest $request
     * @return array
     */
    public function createOrderComment(CreateOrderCommentRequest $request): array
    {
        return $this->service->createOrderComment($request->validated());
    }

    /**
     * Delete multiple order comments.
     *
     * @param DeleteOrderCommentsRequest $request
     * @return array
     */
    public function deleteOrderComments(DeleteOrderCommentsRequest $request): array
    {
        $orderIds = request()->input('order_ids', []);
        return $this->service->deleteOrderComments($orderIds);
    }

    /**
     * Show order comment.
     *
     * @param ShowOrderCommentRequest $request
     * @param OrderComment $orderComment
     * @return OrderCommentResource
     */
    public function showOrderComment(ShowOrderCommentRequest $request, OrderComment $orderComment): OrderCommentResource
    {
        return $this->service->showOrderComment($orderComment);
    }

    /**
     * Update order comment.
     *
     * @param UpdateOrderCommentRequest $request
     * @param OrderComment $orderComment
     * @return array
     */
    public function updateOrderComment(UpdateOrderCommentRequest $request, OrderComment $orderComment): array
    {
        return $this->service->updateOrderComment($orderComment, $request->validated());
    }

    /**
     * Delete order comment.
     *
     * @param DeleteOrderCommentRequest $request
     * @param OrderComment $orderComment
     * @return array
     */
    public function deleteOrderComment(DeleteOrderCommentRequest $request, OrderComment $orderComment): array
    {
        return $this->service->deleteOrderComment($orderComment);
    }
}

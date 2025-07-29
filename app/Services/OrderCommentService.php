<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Enums\Association;
use App\Models\OrderComment;
use App\Enums\UploadFolderName;
use App\Http\Resources\OrderCommentResource;
use App\Http\Resources\OrderCommentResources;

class OrderCommentService extends BaseService
{
    /**
     * Show order comments.
     *
     * @param array $data
     * @return OrderCommentResources|array
     */
    public function showOrderComments(array $data): OrderCommentResources|array
    {
        $orderId = $data['order_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = OrderComment::query();
        }else if($orderId) {
            $query = OrderComment::where('order_id', $orderId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create order comment.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createOrderComment(array $data): array
    {
        $order = Order::find($data['order_id']);

        $data = array_merge($data, [
            'store_id' => $order->store_id,
            'user_id' => auth()->user()->id
        ]);

        $orderComment = OrderComment::create($data);

        // Create order comment photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_type' => 'order comment',
                'mediable_id' => $orderComment->id,
                'upload_folder_name' => UploadFolderName::ORDER_COMMENT_PHOTO->value
            ]);

        }

        return $this->showCreatedResource($orderComment);
    }

    /**
     * Delete order comments.
     *
     * @param array $orderCommentIds
     * @return array
     * @throws Exception
     */
    public function deleteOrderComments(array $orderCommentIds): array
    {
        $orderComments = OrderComment::whereIn('id', $orderCommentIds)->with(['mediaFiles'])->get();

        if ($totalOrderComments = $orderComments->count()) {

            $mediaFileService = new MediaFileService;

            foreach ($orderComments as $orderComment) {

                foreach ($orderComment->mediaFiles as $mediaFile) {
                    $mediaFileService->deleteMediaFile($mediaFile);
                }

                $orderComment->delete();

            }

            return ['message' => $totalOrderComments . ($totalOrderComments == 1 ? ' Order comment' : ' Order comments') . ' deleted'];

        } else {
            throw new Exception('No order comments deleted');
        }
    }

    /**
     * Show order comment.
     *
     * @param OrderComment $orderComment
     * @return OrderCommentResource
     */
    public function showOrderComment(OrderComment $orderComment): OrderCommentResource
    {
        return $this->showResource($orderComment);
    }

    /**
     * Update order comment.
     *
     * @param OrderComment $orderComment
     * @param array $data
     * @return array
     */
    public function updateOrderComment(OrderComment $orderComment, array $data): array
    {
        $orderComment->update($data);
        return $this->showUpdatedResource($orderComment);
    }

    /**
     * Delete order comment.
     *
     * @param OrderComment $orderComment
     * @return array
     * @throws Exception
     */
    public function deleteOrderComment(OrderComment $orderComment): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($orderComment->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $orderComment->delete();

        if ($deleted) {
            return ['message' => 'Order comment deleted'];
        } else {
            throw new Exception('Order comment delete unsuccessful');
        }
    }
}

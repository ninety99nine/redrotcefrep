<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Enums\Association;
use App\Models\Transaction;
use App\Enums\UploadFolderName;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionResources;
use App\Models\Order;

class TransactionService extends BaseService
{
    /**
     * Show transactions.
     *
     * @param array $data
     * @return TransactionResources|array
     */
    public function showTransactions(array $data): TransactionResources|array
    {
        $orderId = $data['order_id'] ?? null;
        $storeId = $data['store_id'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $paymentMethodId = $data['payment_method_id'] ?? null;
        $requestedByUserId = $data['requested_by_user_id'] ?? null;
        $manuallyVerifiedByUserId = $data['manually_verified_by_user_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Transaction::query();
        }else if($orderId) {
            $query = Transaction::where('owner_id', $orderId)->where('owner_type', 'order');
        }else if($customerId) {
            $query = Transaction::where('customer_id', $customerId);
        }else if($paymentMethodId) {
            $query = Transaction::where('payment_method_id', $paymentMethodId);
        }else if($requestedByUserId) {
            $query = Transaction::where('requested_by_user_id', $requestedByUserId);
        }else if($manuallyVerifiedByUserId) {
            $query = Transaction::where('manually_verified_by_user_id', $manuallyVerifiedByUserId);
        }else {
            $query = Transaction::where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create transaction.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createTransaction(array $data): array
    {
        $transaction = Transaction::create($data);

        if($data['owner_type'] == 'order') {

            $orderService = new OrderService;
            $order = Order::find($data['owner_id']);
            $orderService->updateOrderAmountBalance($order);

        }

        // Create transaction photo if provided
        if (isset($data['photo']) && !empty($data['photo'])) {

            (new MediaFileService)->createMediaFile([
                'file' => $data['photo'],
                'mediable_type' => 'transaction',
                'mediable_id' => $transaction->id,
                'upload_folder_name' => UploadFolderName::TRANSACTION_PHOTO->value
            ]);

        }

        return $this->showCreatedResource($transaction);
    }

    /**
     * Delete transactions.
     *
     * @param array $transactionIds
     * @return array
     * @throws Exception
     */
    public function deleteTransactions(array $transactionIds): array
    {
        $transactions = Transaction::whereIn('id', $transactionIds)->with(['mediaFiles'])->get();

        if ($totalTransactions = $transactions->count()) {

            foreach ($transactions as $transaction) {

                $this->deleteTransaction($transaction);

            }

            return ['message' => $totalTransactions . ($totalTransactions == 1 ? ' Transaction' : ' Transactions') . ' deleted'];

        } else {
            throw new Exception('No transactions deleted');
        }
    }

    /**
     * Show transaction.
     *
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function showTransaction(Transaction $transaction): TransactionResource
    {
        return $this->showResource($transaction);
    }

    /**
     * Update transaction.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return array
     */
    public function updateTransaction(Transaction $transaction, array $data): array
    {
        $transaction->update($data);
        return $this->showUpdatedResource($transaction);
    }

    /**
     * Delete transaction.
     *
     * @param Transaction $transaction
     * @return array
     * @throws Exception
     */
    public function deleteTransaction(Transaction $transaction): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($transaction->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $transaction->delete();

        if($transaction->owner_type == 'order') {

            $orderService = new OrderService;
            $order = Order::find($transaction->owner_id);
            $orderService->updateOrderAmountBalance($order);

        }

        if ($deleted) {
            return ['message' => 'Transaction deleted'];
        } else {
            throw new Exception('Transaction delete unsuccessful');
        }
    }

    /**
     * Renew transaction payment link.
     *
     * @param Transaction $transaction
     * @return TransactionResource
     * @throws Exception
     */
    public function renewTransactionPaymentLink(Transaction $transaction): TransactionResource
    {
        $transaction = $transaction->load(['owner', 'store', 'aiAssistant', 'paymentMethod']);

        if($transaction->isPaid()) throw new Exception('Transaction has been paid and cannot be renewed');

        $paymentMethod = $transaction->paymentMethod;

        if (!$paymentMethod) {
            throw new Exception('The transaction payment method does not exist');
        }

        if ($paymentMethod->type == PaymentMethodType::DPO->value) {

            if(Carbon::parse($transaction->metadata['dpo_payment_url_expires_at'])->isPast()) {

                $user = Auth::user();

                if($transaction->owner_type == 'order') {

                    $companyToken = null;   //  update to capture the store company token
                    //  $dpoPaymentLinkPayload = (new OrderService)->prepareDpoPaymentLinkPayload($user, $transaction);

                }else if($transaction->owner_type == 'pricing plan') {

                    $companyToken = config('app.dpo_company_token');
                    $dpoPaymentLinkPayload = (new PricingPlanService)->prepareDpoPaymentLinkPayload($user, $transaction);

                }

                $transactionToken = $transaction->metadata['dpo_transaction_token'];
                DirectPayOnlineService::cancelPaymentLink($companyToken, $transactionToken);
                $metadata = DirectPayOnlineService::createPaymentLink($companyToken, $dpoPaymentLinkPayload);

                $transaction->update(['metadata' => $metadata]);

            }

            return $this->showResource($transaction);

        } else {

            throw new Exception('The ' . $paymentMethod->name . ' payment method cannot be used to renew transaction payment link');

        }
    }
}

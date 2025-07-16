<?php

namespace App\Services;

use App\Enums\PaymentMethodType;
use Exception;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionResources;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $query = Transaction::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
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
        $transactions = Transaction::whereIn('id', $transactionIds)->get();

        if ($totalTransactions = $transactions->count()) {

            foreach ($transactions as $transaction) {
                $transaction->delete();
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
        $deleted = $transaction->delete();

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

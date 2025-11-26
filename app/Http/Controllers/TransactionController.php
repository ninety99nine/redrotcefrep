<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionResources;
use App\Http\Requests\Transaction\ShowTransactionRequest;
use App\Http\Requests\Transaction\ShowTransactionsRequest;
use App\Http\Requests\Transaction\CreateTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Requests\Transaction\DeleteTransactionRequest;
use App\Http\Requests\Transaction\DeleteTransactionsRequest;
use App\Http\Requests\Transaction\VerifyTransactionPaymentRequest;
use App\Http\Requests\Transaction\RenewTransactionPaymentLinkRequest;

class TransactionController extends Controller
{
    /**
     * @var TransactionService
     */
    protected $service;

    /**
     * TransactionController constructor.
     *
     * @param TransactionService $service
     */
    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Show transactions.
     *
     * @param ShowTransactionsRequest $request
     * @return TransactionResources|array
     */
    public function showTransactions(ShowTransactionsRequest $request): TransactionResources|array
    {
        return $this->service->showTransactions($request->validated());
    }

    /**
     * Create transaction.
     *
     * @param CreateTransactionRequest $request
     * @return array
     */
    public function createTransaction(CreateTransactionRequest $request): array
    {
        return $this->service->createTransaction($request->validated());
    }

    /**
     * Delete multiple transactions.
     *
     * @param DeleteTransactionsRequest $request
     * @return array
     */
    public function deleteTransactions(DeleteTransactionsRequest $request): array
    {
        $transactionIds = request()->input('transaction_ids', []);
        return $this->service->deleteTransactions($transactionIds);
    }

    /**
     * Show transaction.
     *
     * @param ShowTransactionRequest $request
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function showTransaction(ShowTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        return $this->service->showTransaction($transaction);
    }

    /**
     * Update transaction.
     *
     * @param UpdateTransactionRequest $request
     * @param Transaction $transaction
     * @return array
     */
    public function updateTransaction(UpdateTransactionRequest $request, Transaction $transaction): array
    {
        return $this->service->updateTransaction($transaction, $request->validated());
    }

    /**
     * Delete transaction.
     *
     * @param DeleteTransactionRequest $request
     * @param Transaction $transaction
     * @return array
     */
    public function deleteTransaction(DeleteTransactionRequest $request, Transaction $transaction): array
    {
        return $this->service->deleteTransaction($transaction);
    }

    /**
     * Renew transaction payment link.
     *
     * @param DeleteTransactionRequest $request
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function renewTransactionPaymentLink(RenewTransactionPaymentLinkRequest $request, Transaction $transaction): TransactionResource
    {
        return $this->service->renewTransactionPaymentLink($transaction);
    }

    /**
     * Verify transaction payment.
     *
     * @param VerifyTransactionPaymentRequest $request
     * @param Transaction $transaction
     * @return View
     */
    public function verifyTransactionPayment(VerifyTransactionPaymentRequest $request, Transaction $transaction)
    {
        return $this->service->verifyTransactionPayment($transaction);
    }
}

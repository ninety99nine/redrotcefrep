<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Enums\UploadFolderName;
use App\Enums\TransactionPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TransactionVerificationType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'percentage' => 'integer',
            'metadata' => JsonArray::class,
            'created_using_auto_billing' => 'boolean'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_status','failure_type','failure_reason','description','currency','amount','percentage',
        'metadata','requested_by_user_id','verification_type','manually_verified_by_user_id',
        'store_payment_method_id','payment_method_id','created_using_auto_billing',
        'customer_id','store_id','ai_assistant_id','owner_id','owner_type',
    ];

    /**
     * Scope a query by search term.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where('description', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get store.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get payment method.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get store payment method.
     *
     * @return BelongsTo
     */
    public function storePaymentMethod(): BelongsTo
    {
        return $this->belongsTo(StorePaymentMethod::class);
    }

    /**
     * Get customer.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get AI assistant.
     *
     * @return BelongsTo
     */
    public function aiAssistant(): BelongsTo
    {
        return $this->belongsTo(AiAssistant::class);
    }

    /**
     * Get user who requested the transaction.
     *
     * @return BelongsTo
     */
    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * Get user who manually verified the transaction.
     *
     * @return BelongsTo
     */
    public function manuallyVerifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manually_verified_by_user_id');
    }

    /**
     * Get the parent owner model (e.g., PricingPlan).
     *
     * @return MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get photo.
     *
     * @return MorphOne
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::TRANSACTION_PHOTO->value);
    }

    /**
     * Get photos.
     *
     * @return MorphMany
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable')->where('type', UploadFolderName::TRANSACTION_PHOTO->value);
    }

    /**
     * Get media files (photos and other media file types).
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }

    /**
     *  Check if transaction has been paid
     *
     *  @return bool
     */
    public function isPaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::PAID->value);
    }

    /**
     *  Check if transaction has failed payment
     *
     *  @return bool
     */
    public function isFailedPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::FAILED_PAYMENT->value);
    }

    /**
     *  Check if transaction is pending payment
     *
     *  @return bool
     */
    public function isPendingPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::PENDING_PAYMENT->value);
    }

    /**
     *  Check if transaction is subject to manual verification
     *
     *  @return bool
     */
    public function isSubjectToManualVerification()
    {
        return strtolower($this->getRawOriginal('verification_type')) === strtolower(TransactionVerificationType::MANUAL->value);
    }

    /**
     *  Check if transaction is subject to automatic verification
     *
     *  @return bool
     */
    public function isSubjectToAutomaticVerification()
    {
        return strtolower($this->getRawOriginal('verification_type')) === strtolower(TransactionVerificationType::AUTOMATIC->value);
    }
}

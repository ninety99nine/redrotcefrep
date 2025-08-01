<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Casts\CheckoutFees;
use App\Enums\UploadFolderName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class Store extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [

        'deleted_at' => 'datetime',

        'tips' => JsonArray::class,
        'social_media_links' => JsonArray::class,
        'opening_hours' => JsonArray::class,

        'delivery_flat_fee' => Money::class,
        'checkout_fees' => CheckoutFees::class,

        'ussd_mobile_number' => E164PhoneNumberCast::class,
        'contact_mobile_number' => E164PhoneNumberCast::class,
        'whatsapp_mobile_number' => E164PhoneNumberCast::class,

        'online' => 'boolean',
        'show_tips' => 'boolean',
        'show_items' => 'boolean',
        'offer_rewards' => 'boolean',
        'show_promotions' => 'boolean',
        'show_specify_tip' => 'boolean',
        'show_opening_hours' => 'boolean',
        'show_customer_email' => 'boolean',
        'show_delivery_methods' => 'boolean',
        'customer_email_required' => 'boolean',
        'show_customer_last_name' => 'boolean',
        'show_customer_first_name' => 'boolean',
        'customer_last_name_required' => 'boolean',
        'customer_first_name_required' => 'boolean',
        'combine_fees_into_one_amount' => 'boolean',
        'allow_checkout_on_closed_hours' => 'boolean',
        'combine_discounts_into_one_amount' => 'boolean',

        'order_number_padding' => 'integer',
        'order_number_counter' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','alias','email','ussd_mobile_number','contact_mobile_number','whatsapp_mobile_number','call_to_action',
        'description','qr_code_file_path','offer_rewards','reward_percentage_rate','social_media_links','country','currency',
        'language','distance_unit','weight_unit','tax_method','tax_percentage_rate','tax_id','show_opening_hours',
        'allow_checkout_on_closed_hours','opening_hours','online','offline_message','sms_sender_name','customer_section_heading',
        'show_customer_email','show_customer_last_name','show_customer_first_name','customer_email_required',
        'customer_last_name_required','customer_first_name_required','show_items','items_section_heading',
        'show_delivery_methods','delivery_methods_section_heading','delivery_schedule_title',
        'delivery_address_title','show_tips','tip_section_heading','tips','show_specify_tip',
        'show_promotions','promotions_section_heading','cost_breakdown_section_heading',
        'combine_fees_into_one_amount','combine_discounts_into_one_amount','checkout_fees',
        'order_number_padding', 'order_number_counter','order_number_prefix',
        'order_number_suffix'
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
        $query->where('name', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get the store quota.
     *
     * @return BelongsToMany
     */
    public function storeQuota(): HasOne
    {
        return $this->hasOne(StoreQuota::class);
    }

    /**
     * Get the users.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get the logo.
     *
     * @return MorphOne
     */
    public function logo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::STORE_LOGO->value);
    }

    /**
     *  Get the team members.
     */
    public function teamMembers()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the orders.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the promotions.
     *
     * @return HasMany
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Get the customers.
     *
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the tags.
     *
     * @return HasMany
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get the categories.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the media files.
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }

    /**
     * Get the payment methods.
     *
     * @return BelongsToMany
     */
    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'store_payment_method', 'store_id', 'payment_method_id')
                    ->withPivot([
                        'id', 'active', 'custom_name', 'instruction', 'configs', 'position',
                        'store_id', 'payment_method_id', 'created_at', 'updated_at'
                    ])
                    ->using(StorePaymentMethod::class)
                    ->as('store_payment_method');
    }

    /**
     * Get the delivery methods.
     *
     * @return HasMany
     */
    public function deliveryMethods(): HasMany
    {
        return $this->hasMany(DeliveryMethod::class);
    }

    /**
     *  Get the subscriptions.
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner');
    }

    /**
     *  Get active subscription.
     *
     * @return MorphOne
     */
    public function activeSubscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'owner')->active();
    }

    protected $appends = [
        'web_link'
    ];

    public function webLink(): Attribute
    {
        return new Attribute(
            get: fn() => $this->alias ? config('app.url').'/'.$this->alias : null
        );
    }
}

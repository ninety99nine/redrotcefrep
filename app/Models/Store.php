<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\TagType;
use App\Casts\JsonArray;
use App\Casts\CheckoutFees;
use App\Enums\UploadFolderName;
use App\Services\UssdService;
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
use Illuminate\Support\Facades\Auth;
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
        $query->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('ussd_mobile_number', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get store quota.
     *
     * @return BelongsToMany
     */
    public function storeQuota(): HasOne
    {
        return $this->hasOne(StoreQuota::class);
    }

    /**
     * Get users.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->using(StoreUser::class)
                    ->withTimestamps()
                    ->as('store_user');
    }

    /**
     * Get membership.
     *
     * @return hasOne
     */
    public function myMembership(): hasOne
    {
        return $this->hasOne(StoreUser::class)->where('user_id', Auth::user()?->id ?? 0);
    }

    /**
     * Get followers.
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->using(StoreFollower::class)
                    ->as('store_follower')
                    ->withTimestamps();
    }

    /**
     * Get following.
     *
     * @return hasOne
     */
    public function myFollowing(): hasOne
    {
        return $this->hasOne(StoreFollower::class)->where('user_id', Auth::user()?->id ?? 0);
    }

    /**
     * Get visitors.
     *
     * @return BelongsToMany
     */
    public function visitors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->using(StoreVisitor::class)
                    ->as('store_visitor')
                    ->withPivot([
                        'guest_id', 'last_visited_at'
                    ]);
    }

    /**
     * Get logo.
     *
     * @return MorphOne
     */
    public function logo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::STORE_LOGO->value);
    }

    /**
     * Get orders.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get placed orders.
     *
     * @return HasMany
     */
    public function placedOrders()
    {
        return $this->orders()->where('placed_by_user_id', Auth::user()?->id ?? 0);
    }

    /**
     * Get created orders.
     *
     * @return HasMany
     */
    public function createdOrders()
    {
        return $this->orders()->where('created_by_user_id', Auth::user()?->id ?? 0);
    }

    /**
     * Get assigned orders.
     *
     * @return HasMany
     */
    public function assignedOrders()
    {
        return $this->orders()->where('assigned_to_user_id', Auth::user()?->id ?? 0);
    }

    /**
     * Get products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get promotions.
     *
     * @return HasMany
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Get customers.
     *
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get product tags.
     *
     * @return HasMany
     */
    public function productTags(): HasMany
    {
        return $this->hasMany(Tag::class)->where('type', TagType::PRODUCT->value);
    }

    /**
     * Get customer tags.
     *
     * @return HasMany
     */
    public function customerTags(): HasMany
    {
        return $this->hasMany(Tag::class)->where('type', TagType::CUSTOMER->value);
    }

    /**
     * Get categories.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get media files.
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }

    /**
     * Get payment methods.
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
     * Get delivery methods.
     *
     * @return HasMany
     */
    public function deliveryMethods(): HasMany
    {
        return $this->hasMany(DeliveryMethod::class);
    }

    /**
     *  Get subscriptions.
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
        return $this->morphOne(Subscription::class, 'owner')->oldest()->active();
    }

    protected $appends = [
        'web_link', 'ussd_shortcode'
    ];

    public function webLink(): Attribute
    {
        return new Attribute(
            get: fn() => $this->alias ? config('app.url').'/'.$this->alias : null
        );
    }

    public function ussdShortcode(): Attribute
    {
        return new Attribute(
            get: fn () => $this->ussd_mobile_number == null ? null : UssdService::appendToMainShortcode($this->ussd_mobile_number->formatNational(), $this->ussd_mobile_number->getCountry())
        );
    }
}

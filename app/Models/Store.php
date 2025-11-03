<?php

namespace App\Models;

use App\Enums\TagType;
use App\Casts\JsonArray;
use App\Enums\DomainStatus;
use App\Casts\CheckoutFees;
use App\Services\UssdService;
use App\Enums\UploadFolderName;
use Illuminate\Support\Facades\Auth;
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
        'name' => 'string',
        'alias' => 'string',
        'email' => 'string',
        'tax_id' => 'string',
        'country' => 'string',
        'currency' => 'string',
        'language' => 'string',
        'bg_color' => 'string',
        'description' => 'string',
        'message_footer' => 'string',
        'call_to_action' => 'string',
        'offline_message' => 'string',
        'sms_sender_name' => 'string',
        'qr_code_file_path' => 'string',
        'order_number_prefix' => 'string',
        'order_number_suffix' => 'string',
        'line_channel_username' => 'string',
        'telegram_channel_username' => 'string',
        'messenger_channel_username' => 'string',
        'invoice_header' => 'string',
        'invoice_footer' => 'string',
        'invoice_company_name' => 'string',
        'invoice_company_email' => 'string',
        'invoice_company_mobile_number' => E164PhoneNumberCast::class,

        'order_number_padding' => 'integer',
        'order_number_counter' => 'integer',

        'tax_percentage_rate' => 'decimal:2',
        'reward_percentage_rate' => 'decimal:2',

        'online' => 'boolean',
        'combine_fees' => 'boolean',
        'offer_rewards' => 'boolean',
        'show_sms_channel' => 'boolean',
        'combine_discounts' => 'boolean',
        'show_line_channel' => 'boolean',
        'show_opening_hours' => 'boolean',
        'show_whatsapp_channel' => 'boolean',
        'show_telegram_channel' => 'boolean',
        'show_messenger_channel' => 'boolean',
        'allow_checkout_on_closed_hours' => 'boolean',
        'invoice_show_logo' => 'boolean',
        'invoice_show_qr_code' => 'boolean',

        'checkout_fees' => CheckoutFees::class,

        'tips' => JsonArray::class,
        'seo_keywords' => JsonArray::class,
        'opening_hours' => JsonArray::class,
        'ussd_mobile_number' => E164PhoneNumberCast::class,
        'whatsapp_mobile_number' => E164PhoneNumberCast::class
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','description','bg_color','offline_message','alias','email','sms_sender_name','ussd_mobile_number','whatsapp_mobile_number',
        'call_to_action','qr_code_file_path','offer_rewards','reward_percentage_rate','country','currency','language','weight_unit',
        'distance_unit','tax_method','tax_percentage_rate','tax_id','show_opening_hours','allow_checkout_on_closed_hours','opening_hours',
        'online','order_number_padding','order_number_counter','order_number_prefix','order_number_suffix','message_footer','show_sms_channel',
        'show_line_channel','show_whatsapp_channel','show_telegram_channel','show_messenger_channel','line_channel_username',
        'telegram_channel_username','messenger_channel_username','invoice_show_logo','invoice_show_qr_code','invoice_header',
        'invoice_footer','invoice_company_name','invoice_company_email','invoice_company_mobile_number','seo_title',
        'seo_description','seo_keywords','google_analytics_id','meta_pixel_id','tiktok_pixel_id',
        'tips','checkout_fees','combine_fees','combine_discounts',
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
        return $this->belongsToMany(User::class, 'store_user')
                    ->withPivot(['id', 'first_name', 'email', 'mobile_number', 'user_id', 'role_id', 'store_id', 'creator', 'invited_at', 'joined_at'])
                    ->using(StoreUser::class)
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
        return $this->belongsToMany(User::class, 'store_follower')
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
        return $this->belongsToMany(User::class, 'store_visitor')
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
     * SEO image.
     *
     * @return MorphOne
     */
    public function seoImage(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::STORE_SEO_IMAGE->value);
    }

    /**
     * Get roles.
     *
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
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
     * Get design cards.
     *
     * @return HasMany
     */
    public function designCards(): HasMany
    {
        return $this->hasMany(DesignCard::class);
    }

    /**
     * Get workflows.
     *
     * @return HasMany
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
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
     * Get store payment methods.
     *
     * @return HasMany
     */
    public function storePaymentMethods(): HasMany
    {
        return $this->hasMany(StorePaymentMethod::class);
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
                        'id', 'active', 'custom_name', 'instruction', 'configs', 'require_proof_of_payment',
                        'enable_contact_seller_before_payment', 'mark_as_paid_on_customer_confirmation',
                        'position', 'store_id', 'payment_method_id', 'created_at', 'updated_at'
                    ])
                    ->using(StorePaymentMethod::class)
                    ->as('store_payment_method');
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

    /**
     * Get address.
     *
     * @return MorphOne
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'owner');
    }

    /**
     * Get domains.
     *
     * @return HasMany
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get primary domain.
     *
     * @return HasOne
     */
    public function primaryDomain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('status', DomainStatus::CONNECTED->value);
    }

    /**
     * Get page views.
     *
     * @return HasMany
     */
    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
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

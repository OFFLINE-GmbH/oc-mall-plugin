<?php namespace OFFLINE\Mall\Models;

use Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Model;
use System\Models\File;

class ProductFileGrant extends Model
{
    public $table = 'offline_mall_product_file_grants';
    public $dates = ['expires_at'];
    public $belongsTo = [
        'order_product' => [OrderProduct::class, 'deleted' => true],
    ];
    public $casts = [
        'download_count' => 'integer',
    ];
    public $fillable = [
        'order_product_id',
        'max_download_count',
        'download_key',
        'expires_at',
        'display_name',
    ];
    public $attachOne = [
        'file' => File::class,
    ];

    /**
     * Returns the download URL for this grant.
     */
    public function getDownloadLinkAttribute(): string
    {
        $encodedKey = urlencode(base64_encode($this->download_key));

        return Url::to('/mall/download/' . $encodedKey);
    }

    /**
     * Returns the grant specific display name. Falls back to the
     * product file attachment if no display name was specified.
     */
    public function getDisplayNameAttribute(): string
    {
        if (array_key_exists('display_name', $this->attributes) && $this->attributes['display_name']) {
            return $this->attributes['display_name'];
        }

        return optional($this->order_product->product->latest_file)->display_name ?? 'Download ' . $this->id;
    }

    /**
     * Create a download grant for an OrderProduct.
     *
     * @param OrderProduct $orderProduct
     */
    public static function fromOrderProduct(OrderProduct $orderProduct)
    {
        $expires = null;
        if ($days = $orderProduct->product->file_expires_after_days) {
            $expires = Carbon::now()->addDays($days);
        }

        // Create a grant for each product * quantity.
        for ($i = 0; $i < $orderProduct->quantity; $i++) {
            $grant = self::create([
                'order_product_id'   => $orderProduct->id,
                'max_download_count' => $orderProduct->product->file_max_download_count,
                'download_key'       => str_random(64),
                'expires_at'         => $expires,
            ]);
            // Trigger the created event. The site admin can implement custom file attachements
            // for the grants this way.
            Event::fire('mall.product.file_grant.created', [$grant, $orderProduct->product]);
        }
    }
}

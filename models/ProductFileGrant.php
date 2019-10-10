<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Model;

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
        'storage_path',
        'download_key',
        'expires_at',
    ];

    public function getDownloadLinkAttribute()
    {
        $encodedKey = urlencode(base64_encode($this->download_key));

        return Url::to('/mall/download/' . $encodedKey);
    }

    public function getDisplayNameAttribute()
    {
        return optional($this->order_product->product->latest_file)->display_name ?? '';
    }

    /**
     * Create a download grant for an OrderProduct.
     *
     * @param OrderProduct $orderProduct
     */
    public static function fromOrderProduct(OrderProduct $orderProduct)
    {
        if ( ! $orderProduct->product->latest_file) {
            return;
        }

        $expires = null;
        if ($days = $orderProduct->product->file_expires_after_days) {
            $expires = Carbon::now()->addDays($days);
        }

        // Create a grant for each product * quantity.
        for ($i = 0; $i < $orderProduct->quantity; $i++) {
            self::create([
                'order_product_id'   => $orderProduct->id,
                'max_download_count' => $orderProduct->product->file_max_download_count,
                'download_key'       => str_random(64),
                'expires_at'         => $expires,
            ]);
        }
    }
}

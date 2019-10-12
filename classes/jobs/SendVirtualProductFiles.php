<?php

namespace OFFLINE\Mall\Classes\Jobs;

use Cms\Classes\Controller;
use DB;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Mail;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Notification;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderProduct;
use OFFLINE\Mall\Models\ProductFileGrant;

/**
 * This Job generates ProductFileGrants for each purchased product.
 * It also sends the email containing the download links to the customer.
 */
class SendVirtualProductFiles
{
    /**
     * All enabled email notifications. This is used to look up
     * if the product file email should be sent.
     * @var array
     */
    public $enabledNotifications = [];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->enabledNotifications = Notification::getEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function fire(Job $job, $data)
    {
        if ($job->attempts() > 5) {
            logger()->error('Failed to send virtual product files for order.', ['data' => $data]);
            $job->delete();
        }

        $order = Order::with(['virtual_products.product.latest_file'])->findOrFail($data['order']);

        DB::transaction(function () use ($order) {
            // Create download grants for each order product.
            $order->virtual_products->each(function (OrderProduct $product) {
                ProductFileGrant::fromOrderProduct($product);
            });

            // If the file notification has been disabled exit here.
            if ( ! $this->enabledNotifications->has('offline.mall::product.file_download')) {
                return;
            }

            // Re-fetch the products with all relevant relationships.
            $products = $order->virtual_products->fresh([
                'product_file_grants.order_product.product.latest_file',
                'product.latest_file',
            ]);

            $data = [
                'order'       => $order,
                'products'    => $products,
                'account_url' => $this->getAccountUrl(),
            ];

            Mail::send(
                $this->enabledNotifications->get('offline.mall::product.file_download'),
                $data,
                function ($message) use ($order) {
                    $message->to($order->customer->user->email, $order->customer->name);
                }
            );
        });

        $job->delete();
    }

    /**
     * Return the direct URL to a customer's account page.
     *
     * @param string $page
     *
     * @return string
     * @throws \Cms\Classes\CmsException
     */
    protected function getAccountUrl($page = 'orders'): string
    {
        return (new Controller())->pageUrl(
            GeneralSettings::get('account_page'), ['page' => $page]
        );
    }
}

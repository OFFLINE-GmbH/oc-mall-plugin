<?php namespace OFFLINE\Mall\Components;

use Auth;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;

/**
 * The OrdersList component displays a list of all the user's orders.
 */
class OrdersList extends MallComponent
{
    /**
     * Array of all orders.
     *
     * @var Collection
     */
    public $orders;
    /**
     * All available countries.
     *
     * @var Collection
     */
    public $countries;
    /**
     * Link to pay a pending order.
     *
     * @var string
     */
    public $paymentLink;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.ordersList.details.name',
            'description' => 'offline.mall::lang.components.ordersList.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        $user = Auth::getUser();
        if ( ! $user) {
            return;
        }

        $this->paymentLink = $this->getPaymentLink();
        $this->orders = Order
            ::byCustomer($user->customer)
            ->with(['products', 'products.variant'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Get the URL of the payment page.
     *
     * @return string
     */
    protected function getPaymentLink()
    {
        $page = GeneralSettings::get('checkout_page');

        return $this->controller->pageUrl($page, ['step' => 'payment']);
    }
}
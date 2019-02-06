<?php namespace OFFLINE\Mall\Controllers;

use Backend;
use Backend\Classes\Controller;
use BackendMenu;
use Event;
use Flash;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;

class Orders extends Controller
{
    public $implement = [
        Backend\Behaviors\ListController::class,
        Backend\Behaviors\ImportExportController::class,
        Backend\Behaviors\RelationController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public $requiredPermissions = ['offline.mall.manage_orders'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-orders');
    }

    public function index()
    {
        parent::index();
        $this->addCss('/plugins/offline/mall/assets/backend.css');
    }

    public function show()
    {
        $this->bodyClass = 'compact-container';
        $this->pageTitle = trans('offline.mall::lang.titles.orders.show');
        $this->addCss('/plugins/offline/mall/assets/backend.css');


        $order                      = Order::with('products', 'order_state')->findOrFail($this->params[0]);

        $this->initRelation($order, 'payment_logs');

        $this->vars['order']        = $order;
        $this->vars['money']        = app(Money::class);
        $this->vars['orderStates']  = OrderState::orderBy('sort_order', 'ASC')->get();
        $this->vars['paymentState'] = $this->paymentStatePartial($order);
    }

    public function onChangeOrderState()
    {
        $orderState = OrderState::findOrFail(input('state'));

        $this->updateOrder(['order_state_id' => $orderState->id]);

        return [
            '#order_state' => $orderState->name,
        ];
    }


    public function onChangePaymentState()
    {
        $order    = Order::findOrFail(input('id'));
        $newState = input('state');

        $availableStatus = $order->payment_state::getAvailableTransitions();
        if ( ! in_array($newState, $availableStatus)) {
            throw new ValidationException([trans('offline.mall::lang.order.invalid_status')]);
        }

        $order->payment_state = $newState;
        $order->save();

        return [
            '#payment-state'        => trans($newState::label()),
            '#payment-state-toggle' => $this->paymentStatePartial($order),
        ];
    }

    public function onUpdateTrackingInfo()
    {
        $trackingNumber = input('trackingNumber');
        $trackingUrl    = input('trackingUrl');
        $notification   = (bool)input('notification', false);
        $shipped        = (bool)input('shipped', false);
        $completed      = (bool)input('completed', false);

        $data = ['tracking_url' => $trackingUrl, 'tracking_number' => $trackingNumber];

        if ($shipped) {
            $data['shipped_at'] = now();
        }
        if ($completed) {
            $state = OrderState::where('flag', OrderState::FLAG_COMPLETE)->first();
            if ($state) {
                $data['order_state_id'] = $state->id;
            }
        }

        $order = $this->updateOrder($data, false);

        $order->shippingNotification = $notification;

        Event::fire('mall.order.shipped', [$order]);

        return [
            '#shipped_at'  => $order->shipped_at ? $order->shipped_at->toFormattedDateString() : '-',
            '#order_state' => e($order->order_state->name),
        ];
    }

    public function onUpdateInvoiceNumber()
    {
        $invoiceNumber = input('invoiceNumber');

        $data = ['invoice_number' => $invoiceNumber];

        $this->updateOrder($data);
    }

    public function onDelete($recordId = null)
    {
        $order = Order::findOrFail($recordId);
        $order->delete();

        Flash::success(trans('offline.mall::lang.order.deleted'));

        return Backend::redirect('offline/mall/orders');
    }

    protected function updateOrder(array $attributes, bool $stateNotification = true)
    {
        $order = Order::findOrFail(input('id'));
        $order->forceFill($attributes);

        // When updating the shipping information we don't care about the state change notification.
        $order->stateNotification = $stateNotification;

        $order->save();
        Flash::success(trans('offline.mall::lang.order.updated'));

        return $order;
    }

    protected function paymentStatePartial($order)
    {
        return $this->makePartial('payment_state', ['order' => $order]);
    }
}

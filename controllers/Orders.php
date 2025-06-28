<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend;
use Backend\Behaviors\ImportExportController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Stats\OrdersStats;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;

class Orders extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        ListController::class,
        ImportExportController::class,
        RelationController::class,
    ];

    /**
     * The configuration file for the list controller implementation.
     * @var string
     */
    public $listConfig = 'config_list.yaml';

    /**
     * The configuration file for the import/export controller implementation.
     * @var string
     */
    public $importExportConfig = 'config_import_export.yaml';

    /**
     * The configuration file for the relation controller implementation.
     * @var string
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_orders',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-orders');
    }

    /**
     * Extend query done by the list controller implementation.
     * @param mixed $query
     * @return void
     */
    public function listExtendQuery($query)
    {
        $query->with('customer.user');
    }

    /**
     * Index view
     * @return void
     */
    public function index()
    {
        parent::index();
        $this->addCss('/plugins/offline/mall/assets/backend.css');
        $this->vars['stats'] = new OrdersStats();
        $this->vars['money'] = app(Money::class);
    }

    /**
     * List view
     * @return void
     */
    public function show()
    {
        $this->bodyClass = 'compact-container';
        $this->pageTitle = trans('offline.mall::lang.titles.orders.show');
        $this->addCss('/plugins/offline/mall/assets/backend.css');
        $this->vars['ordersList'] = Backend::url('offline/mall/orders');
        $this->vars['productUpdate'] = Backend::url('offline/mall/products/update');
        $this->vars['addressUpdate'] = Backend::url('offline/mall/addresses/update');
        $this->vars['customerPreview'] = Backend::url('rainlab/user/users/preview');

        $order = Order::with('products', 'order_state')->findOrFail($this->params[0]);

        $this->initRelation($order);

        $this->vars['order'] = $order;
        $this->vars['money'] = app(Money::class);
        $this->vars['orderStates'] = OrderState::orderBy('sort_order', 'ASC')->get();
        $this->vars['paymentState'] = $this->paymentStatePartial($order);

        // Notes Form
        $config = $this->makeConfigFromArray([
            'fields' => [
                'customer_notes' => [
                    'label' => 'offline.mall::lang.order.customer_notes',
                    'span' => 'full',
                    'type' => 'textarea',
                    'size' => 'tiny',
                ],
                'admin_notes' => [
                    'label' => 'offline.mall::lang.order.admin_notes',
                    'comment' => 'offline.mall::lang.order.admin_notes_comment',
                    'span' => 'full',
                    'type' => 'textarea',
                    'size' => 'tiny',
                ],
            ],
        ]);
        $config->model = $order;
        $config->arrayName = class_basename($config->model);

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);

        $this->vars['orderFormWidget'] = $widget;
    }

    public function onUpdateNotes()
    {
        $order = Order::findOrFail($this->params[0]);

        $order->customer_notes = post('Order[customer_notes]');
        $order->admin_notes = post('Order[admin_notes]');

        $order->save();

        Flash::success(trans('offline.mall::lang.order.notes_updated'));
    }

    /**
     * Ajax handler on change order state.
     * @return array
     */
    public function onChangeOrderState()
    {
        $orderState = OrderState::findOrFail(input('state'));
        $this->updateOrder(['order_state_id' => $orderState->id]);

        return [
            '#order_state' => $orderState->name,
        ];
    }

    /**
     * Ajax handler on change payment state.
     * @throws ValidationException
     * @return array
     */
    public function onChangePaymentState()
    {
        $order = Order::findOrFail(input('id'));
        $newState = input('state');

        $availableStatus = $order->payment_state::getAvailableTransitions();

        if (!in_array($newState, $availableStatus)) {
            throw new ValidationException([trans('offline.mall::lang.order.invalid_status')]);
        }

        $order->payment_state = $newState;
        $order->save();

        return [
            '#payment-state' => trans($newState::label()),
            '#payment-state-toggle' => $this->paymentStatePartial($order),
        ];
    }

    /**
     * Ajax handler on change tracking info.
     * @return array
     */
    public function onUpdateTrackingInfo()
    {
        $trackingNumber = input('trackingNumber');
        $trackingUrl = input('trackingUrl');
        $notification = (bool)input('notification', false);
        $shipped = (bool)input('shipped', false);
        $completed = (bool)input('completed', false);

        $data = [
            'tracking_url' => $trackingUrl,
            'tracking_number' => $trackingNumber,
        ];

        if ($shipped) {
            $data['shipped_at'] = now();
        }

        if ($completed) {
            $state = OrderState::where('flag', OrderState::FLAG_COMPLETE)->first();

            if ($state) {
                $data['order_state_id'] = $state->id;
            }
        }

        $order = $this->updateOrder($data, false, $notification);

        return [
            '#shipped_at' => $order->shipped_at ? $order->shipped_at->toFormattedDateString() : '-',
            '#order_state' => e($order->order_state->name),
        ];
    }

    /**
     * Ajax handler on update invoice number.
     * @return void
     */
    public function onUpdateInvoiceNumber()
    {
        $invoiceNumber = input('invoiceNumber');
        $data = ['invoice_number' => $invoiceNumber];
        $this->updateOrder($data);
    }

    /**
     * Download a PDF invoice.
     * @return mixed
     */
    public function invoice()
    {
        $id = $this->params[0];
        $order = Order::with(['customer', 'products'])->findOrFail($id);

        return $order->getPDFInvoice()->stream(sprintf('mall-order-%s.pdf', $id));
    }

    /**
     * Ajax handler on delete record.
     * @param null|mixed $recordId
     * @return mixed
     */
    public function onDelete($recordId = null)
    {
        $order = Order::findOrFail($recordId);
        $order->delete();
        Flash::success(trans('offline.mall::lang.order.deleted'));

        return Backend::redirect('offline/mall/orders');
    }

    /**
     * Update order handler.
     * @return mixed
     */
    protected function updateOrder(array $attributes, bool $stateNotification = true, bool $shippingNotification = false)
    {
        $order = Order::findOrFail(input('id'));
        $order->forceFill($attributes);

        // When updating the shipping information we don't care about the state change notification.
        $order->stateNotification = $stateNotification;
        $order->shippingNotification = $shippingNotification;

        $order->save();
        Flash::success(trans('offline.mall::lang.order.updated'));

        return $order;
    }

    /**
     * Render payment state partial.
     * @param mixed $order
     * @return mixed
     */
    protected function paymentStatePartial($order)
    {
        return $this->makePartial('payment_state', ['order' => $order]);
    }
}

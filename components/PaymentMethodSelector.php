<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Illuminate\Contracts\Encryption\DecryptException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentService;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Validator;

class PaymentMethodSelector extends ComponentBase
{
    use SetVars;
    use HashIds;

    public $cart;
    public $activeMethod;
    public $paymentData;
    public $methods;
    public $order;
    public $workingOnModel;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.paymentMethodSelector.details.name',
            'description' => 'offline.mall::lang.components.paymentMethodSelector.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        return $this->setData();
    }

    public function onSubmit()
    {
        $this->setData();
        $data = post('payment_data', []);

        // Create the payment gateway to trigger the validation.
        // If not all specified data is valid an exception is thrown here.
        $paymentMethod = PaymentMethod::findOrFail($this->workingOnModel->payment_method_id);
        $gateway       = app(PaymentGateway::class);
        $gateway->init($paymentMethod, $data);

        // If a order is present this is not a normal checkout flow but a
        // retry for a payment of an already existing order.
        if ($this->order) {
            $paymentService = new PaymentService(
                $gateway,
                $this->order,
                $this->page->page->fileName
            );

            return $paymentService->process();
        }

        // Just to prevent any data leakage we store credit card information encrypted to the session.
        session()->put('mall.payment_method.data', encrypt(json_encode($data)));

        return redirect()->to($this->getStepUrl('shipping'));
    }

    public function onChangeMethod()
    {
        $this->setData();

        $rules = [
            'id' => 'required|exists:offline_mall_payment_methods,id',
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $id = post('id');

        $this->workingOnModel->payment_method_id = $id;
        $this->workingOnModel->save();

        $this->setData();

        return [
            '.mall-payment-method-selector' => $this->renderPartial($this->alias . '::selector'),
        ];
    }

    protected function setData()
    {
        $user = Auth::getUser();
        $this->setVar('cart', Cart::byUser($user));
        $this->workingOnModel = $this->cart;

        if ($orderId = request()->get('order')) {
            $orderId = $this->decode($orderId);

            $order = Order::byCustomer($user->customer)->findOrFail($orderId);

            $this->order          = $order;
            $this->workingOnModel = $order;
        }

        $this->setVar('methods', PaymentMethod::orderBy('sort_order', 'ASC')->get());
        $this->setVar('activeMethod', $this->order->payment_method_id ?? $this->cart->payment_method_id);

        try {
            $paymentData = json_decode(decrypt(session()->get('mall.payment_method.data')), true);
        } catch (DecryptException $e) {
            $paymentData = [];
        }

        $this->setVar('paymentData', $paymentData);
    }

    protected function getStepUrl($step): string
    {
        return $this->controller->pageUrl($this->page->page->fileName, ['step' => $step]);
    }
}

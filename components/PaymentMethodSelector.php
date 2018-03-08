<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Illuminate\Contracts\Encryption\DecryptException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\PaymentMethod;
use Validator;

class PaymentMethodSelector extends ComponentBase
{
    use SetVars;

    public $cart;
    public $activeMethod;
    public $paymentData;
    public $methods;

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
        $this->setData();
    }

    public function onSubmit()
    {
        $this->setData();
        $data = post('payment_data', []);

        // Create the payment gateway to trigger the validation.
        // If not all specified data is valid an exception is thrown here.
        $gateway = app(PaymentGateway::class);
        $gateway->init($this->cart, $data);

        // Just to prevent any data leakage we store credit card information encrypted to the session.
        session()->put('mall.payment_method.data', encrypt(json_encode($data)));

        $url = $this->controller->pageUrl($this->page->page->fileName, ['step' => 'shipping']);

        return redirect()->to($url);
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
        $this->cart->setPaymentMethod($id);

        $this->setData();

        return [
            '.mall-payment-method-selector' => $this->renderPartial($this->alias . '::selector'),
        ];
    }

    protected function setData()
    {
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', PaymentMethod::orderBy('sort_order', 'ASC')->get());
        $this->setVar('activeMethod', $this->cart->payment_method_id);

        try {
            $paymentData = json_decode(decrypt(session()->get('mall.payment_method.data')), true);
        } catch (DecryptException $e) {
            $paymentData = [];
        }

        $this->setVar('paymentData', $paymentData);
    }
}

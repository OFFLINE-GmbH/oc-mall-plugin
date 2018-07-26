<?php namespace OFFLINE\Mall\Components;

use Auth;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\ShippingMethod;
use Validator;

class ShippingSelector extends MallComponent
{
    public $cart;
    public $methods;
    public $skipIfOnlyOneAvailable = true;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.shippingSelector.details.name',
            'description' => 'offline.mall::lang.components.shippingSelector.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'skipIfOnlyOneAvailable' => [
                'type'    => 'checkbox',
                'label'   => 'Skip if only one method is available',
                'default' => true,
            ],
        ];
    }

    public function onRun()
    {
        $this->setData();

        if ($this->shouldSkipStep()) {
            return $this->redirect();
        }
    }

    public function onSubmit()
    {
        return $this->redirect();
    }

    public function onChangeMethod()
    {
        $this->setData();

        $v = Validator::make(post(), [
            'id' => 'required|exists:offline_mall_shipping_methods,id',
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        if ( ! $this->methods || ! $this->methods->pluck('id')->contains(post('id'))) {
            throw new ValidationException(['id' => trans('offline.mall::lang.components.shippingSelector.errors.unavailable')]);
        }

        $this->cart->shipping_method_id = post('id');
        $this->cart->save();

        $this->setData();

        return [
            '.mall-shipping-selector' => $this->renderPartial($this->alias . '::selector'),
        ];
    }

    protected function setData()
    {
        $this->skipIfOnlyOneAvailable = (bool)$this->property('skipIfOnlyOneAvailable');
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', ShippingMethod::getAvailableByCart($this->cart));
    }

    protected function redirect()
    {
        $url = $this->controller->pageUrl($this->page->page->fileName, ['step' => 'confirm']);

        return redirect()->to($url);
    }

    protected function shouldSkipStep()
    {
        return $this->skipIfOnlyOneAvailable
            && $this->methods->count() === 1
            && request()->get('via') === 'payment';
    }
}

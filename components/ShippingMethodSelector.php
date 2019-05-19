<?php namespace OFFLINE\Mall\Components;

use Auth;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\ShippingMethod;
use Validator;

/**
 * The ShippingMethodSelector component displays available shipping
 * methods to the user.
 */
class ShippingMethodSelector extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var Cart
     */
    public $cart;
    /**
     * All available shipping methods.
     *
     * @var Collection
     */
    public $methods;
    /**
     * Redirect further in the checkout process if
     * only one shipping is available to choose from.
     *
     * @var bool
     */
    public $skipIfOnlyOneAvailable = true;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.shippingMethodSelector.details.name',
            'description' => 'offline.mall::lang.components.shippingMethodSelector.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
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

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $this->skipIfOnlyOneAvailable = (bool)$this->property('skipIfOnlyOneAvailable');
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', ShippingMethod::getAvailableByCart($this->cart));
    }

    /**
     * The component is executed.
     *
     * @return string
     */
    public function onRun()
    {
        $this->setData();

        if ($this->shouldSkipStep()) {
            return $this->redirect();
        }
    }

    /**
     * A shipping method has been selected.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function onSubmit()
    {
        $this->setData();

        return $this->redirect();
    }

    /**
     * The shipping method has been changed.
     *
     * @return array
     */
    public function onChangeMethod()
    {
        $this->setData();

        $v = Validator::make(post(), [
            'id' => 'required|exists:offline_mall_shipping_methods,id',
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $id = post('id');

        if ( ! $this->methods || ! $this->methods->pluck('id')->contains($id)) {
            throw new ValidationException([
                'id' => trans('offline.mall::lang.components.shippingMethodSelector.errors.unavailable'),
            ]);
        }

        $this->cart->shipping_method_id = $id;
        $this->cart->save();

        $this->setData();

        return [
            '.mall-shipping-selector' => $this->renderPartial($this->alias . '::selector'),
            'method'                  => ShippingMethod::find($id),
        ];
    }

    /**
     * Redirect to the next checkout step.
     *
     * @return \Illuminate\Http\RedirectResponse|array
     */
    protected function redirect()
    {
        $url = $this->controller->pageUrl($this->page->page->fileName, ['step' => 'confirm']);

        // If the analytics component is present return the datalayer partial that handles the redirect.
        if ( ! $this->shouldSkipStep() && $this->page->layout->hasComponent('enhancedEcommerceAnalytics')) {
            return [
                '#mall-datalayer' => $this->renderPartial($this->alias . '::datalayer', ['url' => $url]),
            ];
        }

        return redirect()->to($url);
    }

    /**
     * Whether or not to skip this checkout step.
     *
     * @return bool
     */
    protected function shouldSkipStep()
    {
        return $this->skipIfOnlyOneAvailable
            && $this->methods->count() === 1
            && request()->get('via') === 'payment';
    }
}

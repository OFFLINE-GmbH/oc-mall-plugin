<?php


namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\ShippingMethod;

trait ShippingMethods
{
    public function getAvailableShippingMethods()
    {
        return ShippingMethod::getAvailableByCart($this);
    }

    public function setShippingMethod(?ShippingMethod $method)
    {
        $this->shipping_method_id = $method ? $method->id : null;
        $this->save();
    }

    /**
     * Makes sure that the selected shipping method
     * can still be applied to this cart.
     */
    public function validateShippingMethod()
    {
        if ( ! $this->shipping_method_id) {
            return true;
        }

        $available = $this->getAvailableShippingMethods();
        if ($available->contains($this->shipping_method_id)) {
            return true;
        }

        if (count($available) > 0) {
            return $this->setShippingMethod($available->first());
        }

        return $this->setShippingMethod(null);
    }
}
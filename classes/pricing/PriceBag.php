<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing;

use Brick\Money\Money;
use October\Rain\Database\Model;
use OFFLINE\Mall\Classes\Pricing\Concerns\ApplyDiscounts;
use OFFLINE\Mall\Classes\Pricing\Concerns\PriceBagCreators;
use OFFLINE\Mall\Classes\Pricing\Records\DiscountRecord;
use OFFLINE\Mall\Classes\Pricing\Records\PaymentRecord;
use OFFLINE\Mall\Classes\Pricing\Records\ProductRecord;
use OFFLINE\Mall\Classes\Pricing\Records\ServiceRecord;
use OFFLINE\Mall\Classes\Pricing\Records\ShippingRecord;
use OFFLINE\Mall\Classes\Pricing\Values\AmountValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
use Whitecube\Price\Price;

class PriceBag
{
    use ApplyDiscounts;
    use PriceBagCreators;

    /**
     * Product Stack
     * @var int
     */
    public const TYPE_PRODUCT = 1;

    /**
     * Service Stack
     * @var int
     */
    public const TYPE_SERVICE = 2;

    /**
     * Shipping Stack
     * @var int
     */
    public const TYPE_SHIPPING = 3;

    /**
     * Payment Stack
     * @var int
     */
    public const TYPE_PAYMENT = 4;

    /**
     * Discount Stack
     * @var int
     */
    public const TYPE_DISCOUNT = 5;

    /**
     * Used currency for this bag.
     * @var string
     */
    protected string $currency;

    /**
     * Used currency model for this bag.
     * @var Currency
     */
    protected ?Currency $currencyModel = null;

    /**
     * $the main PriceBag Collection
     *
     * @var array<string,array>
     */
    protected array $map;

    /**
     * Create a new PriceBag.
     * @param null|string|Currency $currency
     */
    public function __construct(null|string|Currency $currency = null)
    {
        $currency ??= Currency::activeCurrency();
        $this->currency = is_string($currency) ? $currency : $currency->code;
        $this->currencyModel = $currency; 
        $this->map = [
            'products'  => [],
            'services'  => [],
            'shipping'  => [],
            'payment'   => [],
            'discounts' => [],
        ];
    }

    /**
     * Convert object into array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'currency'  => $this->currency,
            'records'   => [
                'products'  => array_map(fn ($item) => $item->toArray(), $this->map['products']),
                'services'  => array_map(fn ($item) => $item->toArray(), $this->map['services']),
                'shipping'  => array_map(fn ($item) => $item->toArray(), $this->map['shipping']),
                'payment'   => array_map(fn ($item) => $item->toArray(), $this->map['payment']),
                'discounts' => array_map(fn ($item) => $item->toArray(), $this->map['discounts']),
            ],
            'totals'    => [
                'exclusive' => strval($this->totalExclusive()),
                'vat'       => strval($this->totalVat()),
                'tax'       => strval($this->totalTax()),
                'discount'  => strval($this->totalDiscount()),
                'inclusive' => strval($this->totalInclusive()),
            ]
        ];
    }

    /**
     * Return used currency model.
     * @return null|Currency
     */
    public function getCurrency(): ?Currency
    {
        return $this->currencyModel ?? null;
    }

    /**
     * Return used currency code.
     * @return null|string
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currency ?? null;
    }

    /**
     * Add product to price bag.
     * @param string|Model $product
     * @param integer|float|string|Price $price
     * @param integer $amount
     * @param boolean $isInclusive
     * @return ProductRecord
     */
    public function addProduct(string|Model $product, int|float|string|Price $amount, int $units = 1, bool $isInclusive = false)
    {
        $record = new ProductRecord($this->currency, $amount, $units, $isInclusive);
        $record->setAssoc($this, $product);

        $this->map['products'][] = $record;
        return $record;
    }

    /**
     * Add service to price bag.
     * @param string|Model $service
     * @param integer|float|string|Price $price
     * @param integer $amount
     * @param boolean $isInclusive
     * @return ServiceRecord
     */
    public function addService(string|Model $service, int|float|string|Price $amount, int $units = 1, bool $isInclusive = false)
    {
        $record = new ServiceRecord($this->currency, $amount, $units, $isInclusive);
        $record->setAssoc($this, $service);

        $this->map['services'][] = $record;
        return $record;
    }

    /**
     * Add shipping method to price bag.
     * @param string|ShippingMethod $method
     * @param int|float|string|Price $amount
     * @param boolean $isInclusive
     * @return ShippingRecord
     */
    public function addShippingMethod(string|ShippingMethod $method, int|float|string|Price $amount, bool $isInclusive = false)
    {
        $record = new ShippingRecord($this->currency, $amount, $isInclusive);
        $record->setAssoc($this, $method);

        $this->map['shipping'][] = $record;
        return $record;
    }

    /**
     * Add payment method to price bag.
     * @param string|PaymentRecord $method
     * @param int|float|string|Price $amount
     * @param boolean $isInclusive
     * @return PaymentRecord
     */
    public function addPaymentMethod(string|PaymentMethod $method, int|float|string|Price $amount = null, bool $isInclusive = false)
    {
        $record = new PaymentRecord($this->currency, $amount, $isInclusive);
        $record->setAssoc($this, $method);

        $this->map['payment'][] = $record;
        return $record;
    }

    /**
     * Add discount to price bag.
     * @param string $type The type of discount, which should be applied to this bag:
     *               - 'products', product-specific discounts
     *               - 'services', service-specific discounts
     *               - 'shipping', shipping-costs discounts
     *               - 'payment', payment-costs discounts
     *               - 'total', gross-total discounts (ex. skonto / conto)
     * @param int|float|string|AmountValue|FactorValue|Price $amount
     * @param boolean $isFactor
     * @return DiscountRecord
     */
    public function addDiscount(string $type, int|float|string|AmountValue|FactorValue|Price $amount, bool $isFactor = false)
    {
        $record = new DiscountRecord($this->currency, $amount, $isFactor);
        $record->setAssoc($this, $type);

        $this->map['discounts'][] = $record;
        return $record;
    }

    /**
     * Return exclusive price value for all products (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function productsExclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['products'] AS $product) {
            $price->plus($product->exclusive()->exclusive());
        }

        return new PriceValue($price);
    }

    /**
     * Return sum of all discounts for all products combined.
     * @return null|Money
     */
    public function productsDiscount(): Money
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['products'] AS $product) {
            $price->plus($product->discount());
        }

        foreach ($this->map['discounts'] AS $discount) {
            // @todo
        }

        return $price->base();
    }

    /**
     * Return only vat, based on the original net-price minus discount for all products combined.
     * @param $detailed Wether to return total (false) or grouped by the different VAT-factors.
     * @return array|Money
     */
    public function productsVat(bool $detailed = false): array|Money
    {
        $results = [
            'total' => Money::ofMinor('0', $this->currency)
        ];

        foreach ($this->map['products'] AS $product) {
            $vat = $product->vat();

            if ($detailed) {
                if ($product->factor() > 0) {
                    if (!array_key_exists($product->factor(), $results)) {
                        $results[$product->factor()] = Money::ofMinor('0', $this->currency);
                    }
                    $results[$product->factor()] = $results[$product->factor()]->plus($vat);
                }
            }
            $results['total'] = $results['total']->plus($vat);
        }
        return $detailed ? $results : $results['total'];
    }

    /**
     * Return sum of all taxes (incl. vat) based on the original net-price minus discount.
     * @param $detailed Wether to return total (false) or grouped by the different factors.
     * @return array|Money
     */
    public function productsTax(bool $detailed = false): array|Money
    {
        $results = $this->productsVat(true);
        if ($detailed) {
            $results['taxes'] = Money::ofMinor('0', $this->currency);
        }

        foreach ($this->map['products'] AS $product) {
            $amount = $product->tax(true);

            if ($detailed) {
                $results['taxes'] = $results['taxes']->plus($amount);
            }
            $results['total'] = $results['total']->plus($amount);
        }

        return $detailed ? $results : $results['total'];
    }

    /**
     * Return detailed version of all taxes, based on the original net-price minus discount, of all 
     * products.
     * @param $detailed
     * @return array
     */
    public function productsTaxes(bool $detailed = false): array|Money
    {
        $results = [];

        foreach ($this->map['products'] AS $product) {
            $results[] = $product->taxes($detailed);
        }

        return $results;
    }

    /**
     * Return inclusive price value, containing discounts, vat and other taxes.
     * @return PriceValue
     */
    public function productsInclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['products'] AS $product) {
            $price->plus($product->inclusive()->inclusive());
        }

        return new PriceValue($price);
    }

    /**
     * Return sum of all products weight.
     * @return PriceValue
     */
    public function productsWeight(): int|float
    {
        $weight = 0;

        foreach ($this->map['products'] AS $product) {
            $weight += $product->weight();
        }

        return $weight;
    }

    /**
     * Return exclusive price value for all services (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function servicesExclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['services'] AS $service) {
            $price->plus($service->exclusive()->exclusive());
        }

        return new PriceValue($price);
    }

    /**
     * Return sum of all discounts for all services combined.
     * @return Money
     */
    public function servicesDiscount(): Money
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['services'] AS $service) {
            $price->plus($service->discount());
        }

        foreach ($this->map['discounts'] AS $discount) {
            // @todo
        }

        return $price->base();
    }

    /**
     * Return only vat, based on the original net-price minus discount for all services combined.
     * @param $detailed Wether to return total (false) or grouped by the different VAT-factors.
     * @return array|Money
     */
    public function servicesVat(bool $detailed = false): array|Money
    {
        $results = [
            'total' => Money::ofMinor('0', $this->currency)
        ];

        foreach ($this->map['services'] AS $service) {
            if ($detailed) {
                if ($service->factor() > 0) {
                    if (!array_key_exists($service->factor(), $results)) {
                        $results[$service->factor()] = Money::ofMinor('0', $this->currency);
                    }
                    $results[$service->factor()] = $results[$service->factor()]->plus($service->vat());
                }
            }
            $results['total'] = $results['total']->plus($service->vat());
        }

        return $detailed ? $results : $results['total'];
    }

    /**
     * Return sum of all taxes (incl. vat) based on the original net-price minus discount.
     * @param $detailed Wether to return total (false) or grouped by the different factors.
     * @return array|Money
     */
    public function servicesTax(bool $detailed = false): array|Money
    {
        $results = $this->servicesVat(true);
        if ($detailed) {
            $results['taxes'] = Money::ofMinor('0', $this->currency);
        }

        foreach ($this->map['services'] AS $service) {
            $amount = $service->tax(true);

            if ($detailed) {
                $results['taxes'] = $results['taxes']->plus($amount);
            }
            $results['total'] = $results['total']->plus($amount);
        }

        return $detailed ? $results : $results['total'];
    }

    /**
     * Return detailed version of all taxes, based on the original net-price minus discount, of all 
     * services.
     * @param $detailed
     * @return array
     */
    public function servicesTaxes(bool $detailed = false): array|Money
    {
        $results = [];

        foreach ($this->map['services'] AS $service) {
            $results[] = $service->taxes($detailed);
        }

        return $results;
    }

    /**
     * Return inclusive price value, containing discounts, vat and other taxes.
     * @return PriceValue
     */
    public function servicesInclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['services'] AS $service) {
            $price->plus($service->inclusive()->inclusive());
        }

        return new PriceValue($price);
    }
    
    /**
     * Return exclusive price value for all shipping (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function shippingExclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['shipping'] AS $shipping) {
            $price->plus($shipping->exclusive()->exclusive());
        }

        return new PriceValue($price);
    }

    /**
     * Return sum of all discounts for all shipping combined.
     * @return null|Money
     */
    public function shippingDiscount(): Money
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['shipping'] AS $shipping) {
            $price->plus($shipping->discount());
        }

        foreach ($this->map['discounts'] AS $discount) {
            // @todo
        }

        return $price->base();
    }

    /**
     * Return only vat, based on the original net-price minus discount for all shipping combined.
     * @param $detailed Wether to return total (false) or grouped by the different VAT-factors.
     * @return array|Money
     */
    public function shippingVat(bool $detailed = false): array|Money
    {
        $results = [
            'total' => Money::ofMinor('0', $this->currency)
        ];

        foreach ($this->map['shipping'] AS $shipping) {
            if ($detailed) {
                if ($shipping->factor()) {
                    if (!array_key_exists($shipping->factor(), $results)) {
                        $results[$shipping->factor()] = Money::ofMinor('0', $this->currency);
                    }
                    $results[$shipping->factor()] = $results[$shipping->factor()]->plus($shipping->vat());
                }
            }
            $results['total'] = $results['total']->plus($shipping->vat());
        }

        return $detailed ? $results : $results['total'];
    }

    /**
     * Return sum of all taxes (incl. vat) based on the original net-price minus discount.
     * @param $detailed Wether to return total (false) or grouped by the different factors.
     * @return array|Money
     */
    public function shippingTax(bool $detailed = false): array|Money
    {
        $results = $this->shippingVat(true);
        if ($detailed) {
            $results['taxes'] = Money::ofMinor('0', $this->currency);
        }

        foreach ($this->map['shipping'] AS $shipping) {
            $amount = $shipping->tax(true);

            if ($detailed) {
                $results['taxes'] = $results['taxes']->plus($amount);
            }
            $results['total'] = $results['total']->plus($amount);
        }

        return $detailed ? $results : $results['total'];
    }

    /**
     * Return detailed version of all taxes, based on the original net-price minus discount, of all 
     * services.
     * @param $detailed
     * @return array
     */
    public function shippingTaxes(bool $detailed = false): array|Money
    {
        $results = [];

        foreach ($this->map['shipping'] AS $shipping) {
            $results[] = $shipping->taxes($detailed);
        }

        return $results;
    }

    /**
     * Return inclusive price value, containing discounts, vat and other taxes.
     * @return PriceValue
     */
    public function shippingInclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        foreach ($this->map['shipping'] AS $shipping) {
            $price->plus($shipping->inclusive()->inclusive());
        }

        return new PriceValue($price);
    }

    /**
     * Return all applied Payment Fees.
     * @return Money[]
     */
    public function paymentFees(): array
    {
        // @todo 
        return [];
    }
    
    /**
     * Return all applied Payment Discounts.
     * @return Money[]
     */
    public function paymentDiscount(): array
    {
        // @todo 
        return [];
    }

    public function paymentTaxes(): mixed
    {
        // @todo 
        return [];
    }

    public function paymentTotal(): mixed
    {
        // @todo 
        return [];
    }

    /**
     * Return total price of all stacks (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function totalExclusive(): PriceValue
    {
        $price = Price::parse('0', $this->currency);

        $price->plus($this->productsExclusive()->exclusive());
        $price->plus($this->servicesExclusive()->exclusive());
        $price->plus($this->shippingExclusive()->exclusive());

        return new PriceValue($price);
    }

    /**
     * Return only vat, based on the original net-price minus discount for all stacks combined.
     * @return Money
     */
    public function totalVat(): Money
    {
        $price = Money::ofMinor('0', $this->currency);
        $price = $price->plus($this->productsVat());
        $price = $price->plus($this->servicesVat());
        $price = $price->plus($this->shippingVat());
        return $price;
    }

    /**
     * Return sum of all taxes (incl. vat) based on the original net-price minus discount.
     * @return Money
     */
    public function totalTax(): Money
    {
        $price = Money::ofMinor('0', $this->currency);
        $price = $price->plus($this->productsTax());
        $price = $price->plus($this->servicesTax());
        $price = $price->plus($this->shippingTax());
        return $price;
    }

    /**
     * Return detailed version of all taxes, based on the original net-price minus discount, of all 
     * products.
     * @param $detailed
     * @return array
     */
    public function totalTaxes(bool $detailed = false): array|Money
    {
        $results = array_merge(
            array_values($this->productsTaxes($detailed)),
            array_values($this->servicesTaxes($detailed)),
            array_values($this->shippingTaxes($detailed))
        );
        return $results;
    }

    /**
     * Return sum of all discounts for all stacks combined.
     * @return Money
     */
    public function totalDiscount(): Money
    {
        $price = Money::ofMinor('0', $this->currency);
        // @todo
        return $price;
    }

    /**
     * Return inclusive price value, containing discounts, vat and other taxes.
     * @return PriceValue
     */
    public function totalInclusive(): PriceValue
    {
        $this->applyDiscounts();
        $price = Price::parse('0', $this->currency);
        
        $price->plus($this->productsInclusive()->inclusive());
        $price->plus($this->servicesInclusive()->inclusive());
        $price->plus($this->shippingInclusive()->inclusive());
        //$this->paymentFees();
        //$this->paymentDiscounts();
        
        $this->revertDiscounts();
        return new PriceValue($price);
    }

    /**
     * Dump class and records.
     * @return void
     */
    public function dump()
    {
        $this->applyDiscounts();
        dump($this->toArray());
        $this->revertDiscounts();
    }

    /**
     * Dump class and records and die.
     * @return void
     */
    public function dd()
    {
        $this->dump();
        die();
    }
}

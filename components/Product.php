<?php

namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Flash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Queries\VariantByPropertyValuesQuery;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product as ProductModel;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\ReviewSettings;
use OFFLINE\Mall\Models\Variant;
use Request;
use Session;
use System\Classes\PluginManager;
use Validator;

/**
 * The Product component displays all information of a single Product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Product extends MallComponent
{
    use CustomFields;

    /**
     * The item to display.
     *
     * @var Product|Variant;
     */
    public $item;
    /**
     * The Product model belonging to the item.
     *
     * @var ProductModel;
     */
    public $product;
    /**
     * The Variants belonging to the ProductModel.
     *
     * @var Collection
     */
    public $variants;
    /**
     * All available PropertyValues of the Variant.
     *
     * @var Collection
     */
    public $variantPropertyValues;
    /**
     * Available Property models.
     *
     * Named "props" to prevent naming conflict with base class.
     *
     * @var Collection
     */
    public $props;
    /**
     * The Variant to display.
     *
     * @var Variant
     */
    public $variant;
    /**
     * Google Tag Manager dataLayer code.
     *
     * @var string
     */
    public $dataLayer;
    /**
     * Redirect to the new Product/Variant detail page when properties
     * are changed instead of only reloading the add to cart partial.
     *
     * @var boolean
     */
    public $redirectOnPropertyChange;
    /**
     * The ID of the Variant to display.
     * @var integer
     */
    protected $variantId;
    /**
     * Indicate's that the requested product has not been found.
     * @var bool
     */
    protected $isNotFound;
    /**
     * Show or hide reviews, defined in ReviewSettings.
     * @var bool
     */
    public $showReviews;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name' => 'offline.mall::lang.components.product.details.name',
            'description' => 'offline.mall::lang.components.product.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        $langPrefix = 'offline.mall::lang.components.product.properties.redirectOnPropertyChange';

        return [
            'product' => [
                'title' => 'offline.mall::lang.common.product',
                'default' => ':slug',
                'type' => 'dropdown',
            ],
            'variant' => [
                'title' => 'offline.mall::lang.common.variant',
                'default' => ':slug',
                'depends' => ['product'],
                'type' => 'dropdown',
            ],
            'redirectOnPropertyChange' => [
                'title' => $langPrefix . '.title',
                'description' => $langPrefix . '.description',
                'default' => 0,
                'type' => 'checkbox',
            ],
            'currentVariantReviewsOnly' => [
                'title' => 'offline.mall::lang.components.productReviews.properties.currentVariantReviewsOnly.title',
                'description' => 'offline.mall::lang.components.productReviews.properties.currentVariantReviewsOnly.description',
                'type' => 'checkbox',
                'default' => 0,
            ],
        ];
    }

    /**
     * Options array for the products dropdown.
     *
     * @return array
     */
    public function getProductOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::get()->pluck('name', 'id')->toArray();
    }

    /**
     * Options array for the variants dropdown.
     *
     * @return array
     */
    public function getVariantOptions()
    {
        $product = Request::input('product');
        if ( ! $product || $product === ':slug') {
            return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')];
        }

        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::find($product)->variants->pluck('name', 'id')->toArray();
    }

    /**
     * The component is executed.
     *
     * @return string|void
     */
    public function onRun()
    {
        if ($this->isNotFound) {
            return $this->controller->run('404');
        }

        $this->page->title = $this->item->meta_title ?? $this->item->name;
        $this->page->meta_description = $this->item->meta_description;
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        try {
            $this->setVar('item', $this->getItem());
            $this->setVar('variants', $this->getVariants());
        } catch (ModelNotFoundException $e) {
            $this->isNotFound = true;

            return;
        }

        if ( ! $this->product->categories) {
            $this->isNotFound = true;
            logger()->error(
                'A product without an existing category has been found.',
                ['id' => $this->item->id, 'name' => $this->item->name]
            );

            return;
        }

        $this->showReviews = (bool)ReviewSettings::get('enabled', false);
        $this->addComponent(
            ProductReviews::class,
            'productReviews',
            [
                'product' => $this->product->id,
                'variant' => optional($this->variant)->id,
                'currentVariantReviewsOnly' => $this->property('currentVariantReviewsOnly'),
            ]
        );

        $this->setVar('variantPropertyValues', $this->getPropertyValues());
        $this->setVar('props', $this->getProps());
        $this->setVar('dataLayer', $this->handleDataLayer());
        $this->setVar('redirectOnPropertyChange', (bool)$this->property('redirectOnPropertyChange'));
    }

    /**
     * Add a product to the cart.
     *
     * @return mixed
     * @throws ValidationException
     */
    public function onAddToCart()
    {
        $product = $this->getProduct();

        $variant = null;
        $values = $this->validateCustomFields(post('fields', []));

        if ($this->variantId !== null) {
            // In case a Variant is added we have to retrieve the model first by the selected props.
            $variant = $this->getVariantByPropertyValues(post('props'));
        }

        $quantity = (int)input('quantity', $product->quantity_default ?? 1);
        if ($quantity < 1) {
            throw new ValidationException(['quantity' => trans('offline.mall::lang.common.invalid_quantity')]);
        }

        // In case this product does not have any services attached, add it to the cart directly.
        if ($product->services->count() === 0) {
            return $this->addToCart($product, $quantity, $variant, $values);
        }

        // Temporarily store the current cart data to the session. We will re-fetch this data
        // when the product is definitely added to the cart.
        Session::put('mall.cart.add.variant', optional($variant)->id);
        Session::put('mall.cart.add.values', $values->toArray());
        Session::put('mall.cart.add.quantity', $quantity);

        // Display the services modal.
        return [
            '.mall-modal' => $this->renderPartial(
                $this->alias . '::servicemodal',
                [
                    'services' => $product->services,
                    'added' => false,
                ]
            ),
        ];
    }

    /**
     * Add a product to the cart with services.
     *
     * @return mixed
     * @throws ValidationException
     */
    public function onAddToCartWithServices()
    {
        $product = $this->getProduct();

        // Create validation rules for required services.
        $required = $product->services->where('pivot.required', true);
        $rules = $required->mapWithKeys(
            function ($service) {
                return [
                    'service.' . $service->id => 'required|min:1|array',
                    'service.' . $service->id . '.*' => 'required|in:' . $service->options->pluck('id')->implode(','),
                ];
            }
        );
        $messages = $required->mapWithKeys(
            function ($service) {
                return ['service.' . $service->id . '.*.required' => trans('offline.mall::frontend.services.required')];
            }
        );

        // Validate all required services are selected.
        $v = Validator::make(post(), $rules->toArray(), $messages->toArray());
        if ($v->fails()) {
            throw new ValidationException($v);
        }

        // Fetch the original cart data from the session.
        $variant = Variant::find(Session::pull('mall.cart.add.variant'));
        $quantity = Session::pull('mall.cart.add.quantity');
        $values = Collection::wrap(Session::pull('mall.cart.add.values', []));
        $values = $values->map(
            function ($attributes) {
                return CustomFieldValue::make($attributes);
            }
        );

        $serviceOptionIds = collect(post('service', []))->values()->flatten()->toArray();

        return $this->addToCart($product, $quantity, $variant, $values, $serviceOptionIds);
    }

    /**
     * The user changed a property of the product.
     *
     * Check the stock for the currentyl selected Variant and return
     * the information back to the user.
     *
     * @return array
     */
    public function onChangeProperty()
    {
        $values = post('values', []);
        $isInitial = (bool)post('initial', false);
        $variant = $this->getVariantByPropertyValues($values);

        $this->page['stock'] = $variant ? $variant->stock : 0;
        $this->page['item'] = $variant ?: $this->getProduct();

        if ($this->redirectOnPropertyChange && $isInitial === false) {
            $item = $this->page['item'];
            $slug = $item instanceof Variant ? $item->product->slug : $item->slug;

            return redirect()->to($this->getProductPageUrl($slug, $item));
        }

        return $this->stockCheckResponse();
    }

    /**
     * Check the stock for the currently selected item.
     *
     * @return array
     * @throws ValidationException
     */
    public function onCheckProductStock()
    {
        $slug = post('slug');
        if ( ! $slug) {
            throw new ValidationException(['Missing input data']);
        }

        $item = $this->getItem();

        $this->page['stock'] = $item ? $item->stock : 0;
        $this->page['item'] = $item;

        return $this->stockCheckResponse();
    }

    /**
     * Return the product's new price.
     *
     * @return array
     */
    public function onChangeConfiguration()
    {
        $fields = $this->mapToCustomFields(post('fields', []));
        $values = $this->mapToCustomFieldValues($fields);

        // If we are on a Variant screen make sure to get the
        // Variant by the current property value selection, not
        // by the url parameter.
        if ($this->param('variant')) {
            $propertyValues = post('props', []);
            $item = $this->getVariantByPropertyValues($propertyValues);
        } else {
            $item = $this->getItem();
        }

        // Remove the add to cart button in case the current configuration
        // does not return a product or variant.
        $return = ['.mall-product__add-to-cart' => ''];
        if ($item) {
            $priceData = $item->priceIncludingCustomFieldValues($values);
            $price = Price::fromArray($priceData);

            $partial = $this->renderPartial($this->alias . '::currentprice', ['price' => $price->string]);

            $return = ['.mall-product__current-price' => $partial];
        }

        return $return;
    }

    /**
     * Fetch the item to display.
     *
     * This can be either a Product or a Variant depending
     * on the given input values.
     *
     * @return ProductModel|Variant
     */
    protected function getItem()
    {
        $this->product = $this->getProduct();

        // If no Variant is specified as URL parameter the Product
        // model can be returned directly.
        if ($this->product->inventory_management_method !== 'variant') {
            return $this->product;
        }

        // Use the Variant that was configured via the property.
        $variantId = $this->property('variant');

        // If the property is set to `:slug`, we use the variant from the URL param.
        if ($variantId === ':slug') {
            $variantId = $this->decode($this->param('variant'));
            // If no URL param is present, let's use the first Variant of this Product.
            if ( ! $variantId) {
                $variantId = optional($this->product->variants->first())->id;
            }
            // If no Variants are available, simply display the Product itself.
            if ( ! $variantId) {
                return $this->product;
            }
        }
        $this->setVar('variantId', $variantId);

        $variantModel = Variant::published()->with(
            [
                'property_values.translations',
                'property_values.property.property_groups',
                'product_property_values.property.property_groups',
                'image_sets',
            ]
        );

        return $this->variant = $variantModel->where('product_id', $this->product->id)->findOrFail($this->variantId);
    }

    /**
     * Get the ProductModel.
     *
     * @param array|null $with
     *
     * @return ProductModel
     */
    public function getProduct(?array $with = null): ProductModel
    {
        if ($this->product) {
            return $this->product;
        }

        if ($with === null) {
            $with = [
                'variants',
                'variants.property_values.translations',
                'variants.image_sets',
                'image_sets',
                'downloads',
                'categories',
                'property_values.property.property_groups',
                'services.options',
                'taxes',
            ];
        }

        $product = $this->property('product');
        $model = ProductModel::published()->with($with);

        if ($product === ':slug') {
            $method = $this->rainlabTranslateInstalled() ? 'transWhere' : 'where';

            return $model->$method('slug', $this->param('slug'))->firstOrFail();
        }

        return $model->findOrFail($product);
    }

    /**
     * Get all Variants that belong to this ProductModel.
     *
     * @return Collection
     */
    protected function getVariants(): Collection
    {
        // Single Products won't have any Variants.
        if ($this->product->inventory_management_method === 'single' || ! $this->product->group_by_property_id) {
            return collect();
        }

        $variants = $this->product->variants->reject(
            function (Variant $variant) {
                // Only display "other" Variants, so remove the currently displayed.
                return $variant->id === $this->variantId;
            }
        )->groupBy(
            function (Variant $variant) {
                return $this->getGroupedPropertyValue($variant);
            }
        );

        if ($this->variant) {
            // Remove the property value of the currently viewed variant.
            $variants->pull($this->getGroupedPropertyValue($this->variant));
        }

        return $variants;
    }

    /**
     * Add a product to the cart and refresh all related partials.
     *
     * @param ProductModel    $product
     * @param                 $quantity
     * @param                 $variant
     * @param                 $values
     * @param array           $serviceOptions
     *
     * @return array|RedirectResponse
     * @throws ValidationException
     */
    protected function addToCart(ProductModel $product, $quantity, $variant, $values, array $serviceOptions = [])
    {
        $cart = Cart::byUser(Auth::getUser());

        $serviceOptions = array_filter($serviceOptions);

        try {
            $cart->addProduct($product, $quantity, $variant, $values, $serviceOptions);
        } catch (OutOfStockException $e) {
            throw new ValidationException(['quantity' => trans('offline.mall::lang.common.stock_limit_reached')]);
        }

        // If the redirect_to_cart option is set to true the user is redirected to the cart.
        if ((bool)GeneralSettings::get('redirect_to_cart', false) === true) {
            $cartPage = GeneralSettings::get('cart_page');

            return Redirect::to($this->controller->pageUrl($cartPage));
        }

        Flash::success(trans('offline.mall::frontend.cart.added'));

        return [
            'item' => $this->dataLayerArray($product, $variant),
            'currency' => optional(Currency::activeCurrency())->only('symbol', 'code', 'rate', 'decimals'),
            'quantity' => $quantity,
            'new_items_count' => optional($cart->products)->count() ?? 0,
            'new_items_quantity' => optional($cart->products)->sum('quantity') ?? 0,
            'added' => true,
        ];
    }

    /**
     * Get the PropertyValue this Variant is grouped by.
     *
     * @param Variant $variant
     *
     * @return mixed
     */
    protected function getGroupedPropertyValue(Variant $variant)
    {
        $property = $this->getGroupedProperty($variant);

        return \is_array($property->value) ? json_encode($property->value) : $property->value;
    }

    /**
     * Get the Property this Variant is grouped by.
     *
     * @param Variant $variant
     *
     * @return PropertyValue|object
     */
    protected function getGroupedProperty(Variant $variant)
    {
        if ( ! $variant->product->group_by_property_id) {
            return (object)['value' => 0];
        }

        return $variant->property_values->first(
            function (PropertyValue $value) use ($variant) {
                return $value->property_id === $variant->product->group_by_property_id;
            }
        );
    }

    /**
     * Get all Properties of this item.
     *
     * @return Collection
     */
    protected function getProps()
    {
        $valueMap = $this->getValueMap();
        if ($valueMap->count() < 1) {
            return $valueMap;
        }

        return $this->product->categories->flatMap->properties->map(
            function (Property $property) use ($valueMap) {
                $filteredValues = optional($valueMap->get($property->id))->reject(
                    function ($value) {
                        return $this->variant && $value->variant_id === null;
                    }
                );

                return (object)[
                    'property' => $property,
                    'values' => optional($filteredValues)->unique('value'),
                ];
            }
        )->filter(
            function ($collection) {
                if ($this->variant && $collection->property->pivot->use_for_variants != true) {
                    return false;
                }

                return $collection->values && $collection->values->count() > 0;
            }
        )->keyBy(
            function ($value) {
                return $value->property->id;
            }
        );
    }

    /**
     * Get a map of all PropertyValues.
     *
     * The key is the property_id, the value is the PropertyValue model.
     *
     * @return Collection
     */
    protected function getValueMap()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        $groupedValue = $this->getGroupedPropertyValue($this->variant);
        if ($groupedValue === null) {
            return collect([]);
        }

        return PropertyValue
            ::where('product_id', $this->product->id)
            ->with('translations')
            ->where('value', '<>', '')
            ->whereNotNull('value')
            ->when(
                $groupedValue > 0,
                function ($q) use ($groupedValue) {
                    $q->where('value', '<>', $groupedValue);
                }
            )
            ->get()
            ->groupBy('property_id');
    }

    /**
     * Find a Variant by a set of PropertyValue ids.
     *
     * @param $valueIds
     *
     * @return null
     */
    protected function getVariantByPropertyValues($valueIds)
    {
        $ids = collect($valueIds)->map(
            function ($id) {
                return $this->decode($id);
            }
        );

        $product = $this->getProduct([]);

        $value = (new VariantByPropertyValuesQuery($product, $ids))->query()->first();

        return $value ? $value->variant : null;
    }

    /**
     * Return all PropertyValues of the current Variant.
     *
     * @return Collection
     */
    protected function getPropertyValues()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        return $this->variant->property_values->keyBy('property_id');
    }

    /**
     * Return the currently available stock information back to the user.
     *
     * @return array
     */
    protected function stockCheckResponse(): array
    {
        // Make sure reviews are fetched correctly.
        $reviews = $this->controller->findComponentByName('productReviews');
        if ($reviews) {
            $reviews->onRun();
        }

        $data = [
            'stock' => $this->page['stock'],
            'item' => $this->page['item'],
        ];

        // Factor in currently selected custom field values in the displayed price
        $fields = $this->mapToCustomFields(post('props', []));
        $values = $this->mapToCustomFieldValues($fields);
        $priceData = $data['item']->priceIncludingCustomFieldValues($values);
        $data['price'] = Price::fromArray($priceData);

        return [
            '.mall-product__price' => $this->renderPartial($this->alias . '::price', $data),
            '.mall-product__info' => $this->renderPartial($this->alias . '::info', $data),
            '.mall-product__add-to-cart' => $this->renderPartial($this->alias . '::addtocart', $data),
        ];
    }

    /**
     * Generate the page url for a Product/Variant combination.
     *
     * @param              $slug
     * @param Variant|null $item
     *
     * @return string
     */
    private function getProductPageUrl($slug, ?Variant $item): string
    {
        return $this->controller->pageUrl(
            GeneralSettings::get('product_page'),
            [
                'slug' => $slug,
                'variant' => optional($item)->variantHashId,
            ]
        );
    }

    /**
     * Check if RainLab.Translate is available.
     *
     * @return bool
     */
    protected function rainlabTranslateInstalled(): bool
    {
        return PluginManager::instance()->exists('RainLab.Translate');
    }

    /**
     * Generate Google Tag Manager dataLayer code.
     */
    private function handleDataLayer()
    {
        if ( ! $this->page->layout->hasComponent('enhancedEcommerceAnalytics')) {
            return;
        }

        $dataLayer = [
            'ecommerce' => [
                'detail' => [
                    'products' => [$this->dataLayerArray()],
                ],
            ],
        ];

        return json_encode($dataLayer);
    }

    /**
     * Return the dataLayer representation of an item.
     *
     * @param null $product
     * @param null $variant
     *
     * @return array
     */
    private function dataLayerArray($product = null, $variant = null)
    {
        $product = $product ?? $this->product;
        $variant = $variant ?? $this->variant;

        $item = $variant ?? $product;

        return [
            'id' => $item->prefixedId,
            'name' => $product->name,
            'price' => $item->price()->decimal,
            'brand' => optional($item->brand)->name,
            'category' => optional(optional($item->categories)->first())->name,
            'variant' => optional($variant)->name,
        ];
    }
}

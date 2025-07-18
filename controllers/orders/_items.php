<style>
    .text-right {
        text-align: right;
    }
</style>
<table class="table data">
    <thead>
    <tr>
        <th>
            <span>
                <?= e(trans('offline.mall::lang.product.name')) ?>
            </span>
        </th>
        <th class="text-right">
            <span>
                <?= e(trans('offline.mall::lang.order.quantity')) ?>
            </span>
        </th>
        <th class="text-right">
            <span>
                <?= e(trans('offline.mall::lang.product.price')) ?>
            </span>
        </th>
        <th class="text-right">
            <span>
                <?= e(trans('offline.mall::lang.order.total')) ?>
            </span>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order['products'] as $item): ?>
    <tr>
        <td>
            <?= $this->fireViewEvent('mall.backend.orders.extendItemDetails', [$item, $order]); ?>
            <a href="<?= $productUpdate . '/' . e($item['product_id']); ?>">
                <?= e($item['name']) ?><br/>
            </a>
            <?= $item['variant_name'] ?>
            <div class="attributes">
                <?php if ($item['custom_field_values']): ?>
                    <?php foreach ($item['custom_field_values'] as $field): ?>
                        <?= e($field['custom_field']['name']) ?>: <?= $field['display_value'] ?><br/>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($item->service_options): ?>
                <?php foreach ($item->service_options as $option): ?>
                    <strong><?= e(trans('offline.mall::lang.common.service')) ?></strong>:
                    <?= e($option['name']) ?><br/>
                <?php endforeach; ?>
            <?php endif; ?>
        </td>
        <td class="text-right"><?= e($item['quantity']) ?></td>
        <td class="text-right">
            <?= e($item->pricePostTaxes()) ?>
            <?php if ($item->service_options): ?>
            <?php foreach ($item->service_options as $option): ?>
            <div class="order-product-service" title="<?= e($option['name']) ?>">
                <?= e($option['price_formatted']); ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </td>
        <td class="text-right"><?= e($item->totalPostTaxes()) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="orderfooter separator separator-bottom">
        <td colspan="3">
            <span>
                <?= e(trans('offline.mall::lang.order.subtotal')) ?>
            </span>
        </td>
        <td class="text-right">
            <span>
                <?= e($order->totalProductPostTaxes()) ?>
            </span>
        </td>
    </tr>
    <?php if ($order['discounts']): ?>
    <?php foreach ($order['discounts'] as $entry): ?>
    <tr class="orderdiscount">
        <td colspan="3">
            <span>
                <?= e(trans('offline.mall::lang.common.discount')) ?>:
                <?= e($entry['discount']['name']) ?>
            </span>
        </td>
        <td class="text-right">
            <span>
                <?= e($entry['savings_formatted']) ?>
            </span>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($order['shipping']): ?>
    <tr class="orderfooter separator">
        <td colspan="3">
            <span>
                <?= e(trans('offline.mall::lang.common.shipping')) ?>:
                <?php if (!empty($order['shipping']['method'])): ?>
                    <?= e($order['shipping']['method']['name']) ?>
                <?php endif; ?>
                <?php if ($order['shipping']['appliedDiscount']): ?>
                (<?= e($order['shipping']['appliedDiscount']['discount']['name']) ?>,
                <?= e($money->format($order['shipping']['appliedDiscount']['savings'])) ?>)
                <?php endif; ?>
            </span>
        </td>
        <td class="text-right">
            <span>
                <?= e($order->totalShippingPostTaxes()); ?>
            </span>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($order['payment']): ?>
    <tr class="orderfooter separator">
        <td colspan="3">
            <span>
                <?= e(trans('offline.mall::lang.common.payment')) ?>:
                <?php if (!empty($order['payment']['method'])): ?>
                    <?= e($order['payment']['method']['name']) ?>
                <?php endif; ?>
            </span>
        </td>
        <td class="text-right">
            <span>
                <?php $currency = OFFLINE\Mall\Models\Currency::find(array_get($order->currency, 'id', 0)); ?>
                <?= e($money->format($order['payment']['total'], null, $currency)) ?>
            </span>
        </td>
    </tr>
    <?php endif; ?>
    <tr class="orderfooter-bottomline">
        <td colspan="3">
            <span>
                <?= e(trans('offline.mall::lang.order.grand_total')) ?>
            </span>
        </td>
        <td class="text-right"><span><?= e($order->totalPostTaxes()) ?></span></td>
    </tr>
    <?php if ($order['taxes']): ?>
        <?php foreach ($order['taxes'] as $tax): ?>
        <tr class="orderfooter orderfooter-taxes separator">
            <td colspan="3">
                <span>
                    <?= e($tax['tax']['name'] ?? '') ?> (<?= e($tax['tax']['percentage']) ?> %)
                </span>
            </td>
            <td class="text-right">
                <span>
                    <?= e($tax['total_formatted']); ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

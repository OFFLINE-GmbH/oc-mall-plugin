<div class="layout responsive-sidebar">
    <div class="layout-cell">

        <div class="layout">
            <div class="control-breadcrumb breadcrumb-flush">
                <ul>
                    <li>
                        <a href="<?= $ordersList ?>">
                            <?= e(trans('offline.mall::lang.common.orders')) ?>
                        </a>
                    </li>
                    <li><?= e($order['invoice_number']) ?></li>
                </ul>
            </div>

            <div class="layout-row">
                <div class="padded-container layout">
                    <div class="layout">
                        <div class="layout-row">
                            <div class="scoreboard">
                                <div data-control="toolbar">
                                    <?= $this->makePartial('detail_scoreboard') ?>
                                </div>
                            </div>
                            <div class="control-toolbar">
                                <div class="toolbar-item toolbar-primary">
                                    <div data-control="toolbar">
                                        <?= $this->makePartial('toolbar') ?>
                                    </div>
                                </div>
                            </div>
                            <?= $this->makePartial('tracking_modal') ?>
                            <?= $this->makePartial('invoice_number_modal') ?>

                            <div class="control-tabs primary-tabs" data-control="tab">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a href="#order">
                                            <?= e(trans('offline.mall::lang.common.products')) ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#payments">
                                            <?= e(trans('offline.mall::lang.common.payments')) ?>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div style="margin-left: -20px; margin-right: -20px; margin-top: -21px;">
                                            <div class="control-list">
                                                <?= $this->makePartial('items') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane">
                                        <div class="form-group">
                                            <?= $this->relationRender('payment_logs', ['readOnly' => true]); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-buttons">
                            <div class="loading-indicator-container">
                                <button
                                    type="button"
                                    class="oc-icon-trash-o btn-icon danger pull-right"
                                    data-request="onDelete"
                                    data-load-indicator="<?= e(trans('offline.mall::lang.order.deleting')) ?>"
                                    data-request-confirm="<?= e(trans('offline.mall::lang.order.delete_confirm')) ?>"
                                >
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layout-cell w-300 form-sidebar control-scrollpanel">
        <div class="layout-relative">
            <div class="layout-absolute">
                <div class="sidebar-content">
                    <?= $this->makePartial('address', [
                        'address' => $order['shipping_address'],
                        'heading' => e(trans('offline.mall::lang.order.shipping_address')),
                    ]) ?>

                    <?php if (!$order['shipping_address_same_as_billing']): ?>
                        <?= $this->makePartial('address', [
                            'address' => $order['billing_address'],
                            'heading' => e(trans('offline.mall::lang.order.billing_address')),
                        ]) ?>
                    <?php else: ?>
                        <div class="sidebar-box sidebar-box--with-icon">
                            <i class="icon icon-info-circle"></i>
                            <?= e(trans('offline.mall::lang.order.shipping_address_is_same_as_billing')); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    $customOutputs = Event::fire('mall.backend.order.sidebar', [$order]);

                        foreach (array_filter($customOutputs) as $output) {
                            echo $output;
                        }
                        ?>

                    <?php if (config('offline.mall::features.order_notes')): ?>

                        <h5><?= e(trans('offline.mall::lang.order.notes_section')) ?></h5>

                        <form action="#" data-request="onUpdateNotes" data-request-validate data-request-flash>
                            <?= $orderFormWidget->render() ?>

                            <button data-attach-loading class="btn btn-primary w-100">
                                <?= e(trans('offline.mall::lang.order.update_notes')) ?>
                            </button>
                        </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
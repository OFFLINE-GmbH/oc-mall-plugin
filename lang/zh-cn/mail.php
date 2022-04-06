<?php return [
    'common' => [
        'hello' => '你好 :firstname',
        'view_order_status_online' => '在线查看订单状态',
        'view_order_in_backend' => '在商店后台查看订单',
        'order_details' => '订单详细信息',
    ],
    'payment' => [
        'refunded' => [
            'subject' => '您的付款已退还',
            'message' => '我们刚刚退还了您订单的付款**#:number**.',
            'duration' => '请注意，您可能需要几天时间才能收到资金。',
        ],
        'paid' => [
            'subject' => '谢谢您的付款',
            'message' => '我们刚刚收到您的订单付款**#:number**.',
            'process_order' => '我们现在将开始进一步处理订单。',
        ],
        'failed' => [
            'subject' => '您的订单付款失败',
            'message' => '订单 **#:number** 的付款失败',
            'payment_advice' => '请登录您的帐户并再次尝试支付订单。',
            'support' => '如果您继续遇到付款问题，请联系我们。',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => '帐单地址',
            'billing_and_shipping' => '帐单和送货地址',
            'shipping_address' => '送货地址',
            'has_been_paid_on' => '订单已支付',
            'currently_pending' => '此订单的付款目前处于待处理状态。',
            'track_shipping_status' => '您可以使用以下信息跟踪订单的运输状态：',
        ],
        'state_changed' => [
            'subject' => '您的订单状态已更改',
            'message' => '我们只是想让您知道您的订单 **#:number** 已更新为新状态：**:state**',
        ],
        'shipped' => [
            'subject' => '您的订单已发货',
            'message' => '您的订单 **#:number** 已发货。',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => '欢迎来到我们的商店, :firstname',
            'confirm_mail' => '欢迎来到我们的商店！请单击下面的按钮以确认您的电子邮件地址。',
            'message' => '欢迎来到我们的商店！您可以使用您的电子邮件地址 **:email** 登录并立即开始购物。',
            'possibilities' => '您的用户帐户使您能够跟踪未结订单和过去的订单。',
            'button' => [
                'confirm' => '请确认您的电邮地址',
                'visit_store' => '访问我们的商店',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => '确认订单#:number',
            'thanks_for_order' => '非常感谢您的订单。我们收到以下信息',
            'check_order_status' => '您可以通过访问我们商店的帐户部分来查看您的订单状态',
        ],
        'failed' => [
            'subject' => '订单 #:number 的结帐错误',
            'problem_message' => '我们非常抱歉在您的结帐过程中出现问题。我们将调查问题并与您联系以提供更多信息。',
            'check_order_status' => '要查看您的订单状态，您可以随时登录我们的商店。',
            'payment_id' => '付款 ID',
            'error' => '错误信息',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => '新订单#:number',
            'order_placed' => '在您的商店中下了以下订单：',
        ],
        'checkout_failed' => [
            'subject' => '结帐失败#:number',
            'not_processed' => '无法正确处理以下订单。您可能必须联系客户。',
            'error_details' => '错误详情',
        ],
        'payment_paid' => [
            'subject' => '订单付款#:number成功',
            'message' => '此订单先前失败的付款在随后的尝试中成功。',
        ],
    ],
];

<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Error details",
            "not_processed" => "The following order could not be processed correctly. It is possible that you have to contact the customer.",
            "payment_id" => "",
            "subject" => "Checkout failed #:number"
        ],
        "checkout_succeeded" => [
            "order_placed" => "The following order was placed in your store:",
            "subject" => "New order #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "To check the status of your order you may log in to our store at any time.",
            "error" => "Error message",
            "payment_id" => "Payment ID",
            "problem_message" => "We are very sorry that there was a problem during your checkout process. We will look into the problem and contact you with further information.",
            "subject" => "Checkout error for order #:number"
        ],
        "succeeded" => [
            "check_order_status" => "You can check the status of your order by visiting the account section of our store.",
            "subject" => "Confirmation for order #:number",
            "thanks_for_order" => "Thank you very much for your order. We received the following information"
        ]
    ],
    "common" => [
        "hello" => "Hello :firstname",
        "order_details" => "Order details",
        "view_order_in_backend" => "View order in store backend",
        "view_order_status_online" => "View order status online"
    ],
    "customer" => [
        "created" => [
            "button" => ["confirm" => "Confirm your e-mail address", "visit_store" => "Visit our store"],
            "confirm_mail" => "Welcome to our store! Please click on the button below to confirm your e-mail address.",
            "message" => "Welcome to our store! You can log in using your e-mail address **:email** and start shopping immediately.",
            "possibilities" => "Your user account enables you to keep track of open and past orders.",
            "subject" => "Welcome to our store, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Billing address",
            "billing_and_shipping" => "Billing and shipping address",
            "currently_pending" => "The payment for this order is currently pending.",
            "has_been_paid_on" => "The order has been paid on",
            "shipping_address" => "Shipping address",
            "track_shipping_status" => "You can track the shipping status of your order with the following information:"
        ],
        "shipped" => [
            "message" => "Your order **#:number** has been shipped.",
            "subject" => "Your order has been shipped"
        ],
        "state_changed" => [
            "message" => "We just wanted to let you know that your order **#:number** was updated to the new status: **:state**",
            "subject" => "The status of your order changed"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "We just wanted to let you know that the payment for order **#:number** has failed",
            "payment_advice" => "Please login to your account and try again to pay the order.",
            "subject" => "The payment for your order has failed",
            "support" => "If you continue to experience problems with payments please contact us."
        ],
        "paid" => [
            "message" => "We just received a payment for your order **#:number**.",
            "process_order" => "We will now begin to further process the order.",
            "subject" => "Thank you for your payment"
        ],
        "refunded" => [
            "duration" => "Please be aware that it may take multiple days until you receive your funds.",
            "message" => "We just refunded the payment for your order **#:number**.",
            "subject" => "Your payment was refunded"
        ]
    ]
];

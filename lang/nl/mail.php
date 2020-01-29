<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "",
            "not_processed" => "",
            "payment_id" => "",
            "subject" => ""
        ],
        "checkout_succeeded" => ["order_placed" => "", "subject" => ""]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "",
            "error" => "",
            "payment_id" => "",
            "problem_message" => "",
            "subject" => ""
        ],
        "succeeded" => ["check_order_status" => "", "subject" => "", "thanks_for_order" => ""]
    ],
    "common" => [
        "hello" => "",
        "order_details" => "",
        "view_order_in_backend" => "",
        "view_order_status_online" => ""
    ],
    "customer" => [
        "created" => [
            "button" => ["confirm" => "", "visit_store" => ""],
            "confirm_mail" => "",
            "message" => "",
            "possibilities" => "",
            "subject" => ""
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "",
            "billing_and_shipping" => "",
            "currently_pending" => "",
            "has_been_paid_on" => "",
            "shipping_address" => "",
            "track_shipping_status" => ""
        ],
        "shipped" => ["message" => "", "subject" => ""],
        "state_changed" => ["message" => "", "subject" => ""]
    ],
    "payment" => [
        "failed" => ["message" => "", "payment_advice" => "", "subject" => "", "support" => ""],
        "paid" => ["message" => "", "process_order" => "", "subject" => ""],
        "refunded" => ["duration" => "", "message" => "", "subject" => ""]
    ]
];

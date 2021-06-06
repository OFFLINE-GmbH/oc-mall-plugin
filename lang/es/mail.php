<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Detalles del problema",
            "not_processed" => "El siguiente pedido no pudo ser procesado correctamente. Por favor valida la información y contacta al cliente a la brevedad.",
            "payment_id" => "",
            "subject" => "Falló la creación del pedido #:number"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Este pedido acaba de ser creado en la tienda:",
            "subject" => "Pedido #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Para revisar el estado de tu pedido puedes iniciar sesión en la tienda en cualquier momento.",
            "error" => "Mensaje de error",
            "payment_id" => "ID del Pago",
            "problem_message" => "Lamentamos informarte que hubo un problema durante la creación de tu pedido. Revisaremos la situación y te contactaremos a la brevedad.",
            "subject" => "Hubo un error al crear tu pedido (#:number)"
        ],
        "succeeded" => [
            "check_order_status" => "Puedes revisar el estado de tu pedido ingresando a tu perfil dentro de nuestra tienda.",
            "subject" => "Confirmación de pedido #:number",
            "thanks_for_order" => "Hemos recibido tu pedido. Esta es la información registrada"
        ]
    ],
    "common" => [
        "hello" => "Hola :firstname",
        "order_details" => "Detalles del pedido",
        "view_order_in_backend" => "Ver pedido en el administrador de la tienda",
        "view_order_status_online" => "Ver estado del pedido en línea"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Confirma tu correo electrónico",
                "visit_store" => "Visita nuestra tienda"
            ],
            "confirm_mail" => "Gracias por registrarte en nuestra tienda. Por favor, haz click en el botón de abajo para confirmar tu dirección de correo electrónico.",
            "message" => "Gracias por regisrarte en nuestra tienda. Puedes iniciar sesión usando tu dirección de correo **:email** y comenzar a comprar inmediatamente.",
            "possibilities" => "Tu cuenta de usuario te permitirá revisar pedidos anteriores y abiertos actualmente, además de gestionar tu información personal.",
            "subject" => "Te damos la bienvenida :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Dirección de facturación",
            "billing_and_shipping" => "Dirección de facturación y despacho",
            "currently_pending" => "El pago de este pedido se encuentra pendiente.",
            "has_been_paid_on" => "El pedido ha sido pagado a través de",
            "shipping_address" => "Dirección de despacho",
            "track_shipping_status" => "Puedes ver el estado del despacho  de tu pedido con la siguient información:"
        ],
        "shipped" => [
            "message" => "Tu pedido **#:number** ha sido enviado a la dirección de despacho ingresada.",
            "subject" => "Tu pedido ha sido despachado"
        ],
        "state_changed" => [
            "message" => "Queríamos contarte que tu pedido **#:number** ha sido actualizado a: **:state**",
            "subject" => "El estado de tu pedido ha cambiado"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Queríamos informarte que intantamos cursar el pago para el pedido **#:number** pero el proceso falló.",
            "payment_advice" => "Por favor inicia sesión nuevamente en la tienda e intenta cursar el pago nuevamente.",
            "subject" => "El pago de tu pedido ha fallado",
            "support" => "Si encontraras más problemas, por favor, no dudes en contactarnos."
        ],
        "paid" => [
            "message" => "Acabamos de recibir el pago pago asociado a tu pedido **#:number**.",
            "process_order" => "Comenzaremos a procesar tu pedido inmediatamente.",
            "subject" => "Gracias por tu compra"
        ],
        "refunded" => [
            "duration" => "Si bien ya hemos realizado el reembolso, podrían pasar algunos días hasta que los fondos aparezcan en tu cuenta.",
            "message" => "Acabamos de reembolsar el pago realizado al pedido **#:number**.",
            "subject" => "Tu pedido fue reembolsado"
        ]
    ]
];

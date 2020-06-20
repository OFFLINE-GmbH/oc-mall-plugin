<?php 
return [
    'common'   => [
        'hello'                    => 'Hola :firstname',
        'view_order_status_online' => 'Ver estado del pedido en línea',
        'view_order_in_backend'    => 'Ver pedido en el administrador de la tienda',
        'order_details'            => 'Detalles del pedido',
    ],
    'payment'  => [
        'refunded' => [
            'subject'  => 'Tu pedido fue reembolsado',
            'message'  => 'Acabamos de reembolsar el pago realizado al pedido **#:number**.',
            'duration' => 'Si bien ya hemos realizado el reembolso, podrían pasar algunos días hasta que los fondos aparezcan en tu cuenta.',
        ],
        'paid'     => [
            'subject'       => 'Gracias por tu compra',
            'message'       => 'Acabamos de recibir el pago pago asociado a tu pedido **#:number**.',
            'process_order' => 'Comenzaremos a procesar tu pedido inmediatamente.',
        ],
        'failed'   => [
            'subject'        => 'El pago de tu pedido ha fallado',
            'message'        => 'Queríamos informarte que intantamos cursar el pago para el pedido **#:number** pero el proceso falló.',
            'payment_advice' => 'Por favor inicia sesión nuevamente en la tienda e intenta cursar el pago nuevamente.',
            'support'        => 'Si encontraras más problemas, por favor, no dudes en contactarnos.',
        ],
    ],
    'order'    => [
        'partials'      => [
            'billing_address'       => 'Dirección de facturación',
            'billing_and_shipping'  => 'Dirección de facturación y despacho',
            'shipping_address'      => 'Dirección de despacho',
            'has_been_paid_on'      => 'El pedido ha sido pagado a través de',
            'currently_pending'     => 'El pago de este pedido se encuentra pendiente.',
            'track_shipping_status' => 'Puedes ver el estado del despacho  de tu pedido con la siguient información:',
        ],
        'state_changed' => [
            'subject' => 'El estado de tu pedido ha cambiado',
            'message' => 'Queríamos contarte que tu pedido **#:number** ha sido actualizado a: **:state**',
        ],
        'shipped'       => [
            'subject' => 'Tu pedido ha sido despachado',
            'message' => 'Tu pedido **#:number** ha sido enviado a la dirección de despacho ingresada.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject'       => 'Te damos la bienvenida :firstname',
            'confirm_mail'  => 'Gracias por registrarte en nuestra tienda. Por favor, haz click en el botón de abajo para confirmar tu dirección de correo electrónico.',
            'message'       => 'Gracias por regisrarte en nuestra tienda. Puedes iniciar sesión usando tu dirección de correo **:email** y comenzar a comprar inmediatamente.',
            'possibilities' => 'Tu cuenta de usuario te permitirá revisar pedidos anteriores y abiertos actualmente, además de gestionar tu información personal.',
            'button'        => [
                'confirm'     => 'Confirma tu correo electrónico',
                'visit_store' => 'Visita nuestra tienda',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject'            => 'Confirmación de pedido #:number',
            'thanks_for_order'   => 'Hemos recibido tu pedido. Esta es la información registrada',
            'check_order_status' => 'Puedes revisar el estado de tu pedido ingresando a tu perfil dentro de nuestra tienda.',
        ],
        'failed'    => [
            'subject'            => 'Hubo un error al crear tu pedido (#:number)',
            'problem_message'    => 'Lamentamos informarte que hubo un problema durante la creación de tu pedido. Revisaremos la situación y te contactaremos a la brevedad.',
            'check_order_status' => 'Para revisar el estado de tu pedido puedes iniciar sesión en la tienda en cualquier momento.',
            'payment_id'         => 'ID del Pago',
            'error'              => 'Mensaje de error',
        ],
    ],
    'admin'    => [
        'checkout_succeeded' => [
            'subject'      => 'Pedido #:number',
            'order_placed' => 'Este pedido acaba de ser creado en la tienda:',
        ],
        'checkout_failed'    => [
            'subject'       => 'Falló la creación del pedido #:number',
            'not_processed' => 'El siguiente pedido no pudo ser procesado correctamente. Por favor valida la información y contacta al cliente a la brevedad.',
            'error_details' => 'Detalles del problema',
        ],
    ],
];

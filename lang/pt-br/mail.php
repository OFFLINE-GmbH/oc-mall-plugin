<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Detalhes do erro",
            "not_processed" => "Esse pedido não foi processado corretamente. Entre em contato com o cliente para informa-lo.",
            "payment_id" => "",
            "subject" => "Falha no pagamento #:number"
        ],
        "checkout_succeeded" => ["order_placed" => "Novo pedido na loja:", "subject" => "Novo Pedido #:number"]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Você pode verificar o status do pedido visitando a sua conta dentro da nossa loja.",
            "error" => "Mensagem de erro",
            "payment_id" => "Código do pagamento",
            "problem_message" => "Pedimos desculpas mas houve um problema durante o seu pedido. Nós entraremos em contato para maiores informações.",
            "subject" => "Falha no pedido #:number"
        ],
        "succeeded" => [
            "check_order_status" => "Você pode verificar o status do pedido visitando a sua conta dentro da nossa loja.",
            "subject" => "Confirmação de pedido #:number",
            "thanks_for_order" => "Obrigado pelo seu pedido. Nós recebemos as seguintes informações"
        ]
    ],
    "common" => [
        "hello" => "Olá :firstname",
        "order_details" => "Detalhes do pedido",
        "view_order_in_backend" => "Ver pedido no painel da loja",
        "view_order_status_online" => "Veja o status do seu pedido"
    ],
    "customer" => [
        "created" => [
            "button" => ["confirm" => "Confirmar endereço de email", "visit_store" => "Visitar nossa loja"],
            "confirm_mail" => "Bem vindo a nossa loja! Click no botão abaixo para confirmar seu email.",
            "message" => "Bem vindo a nossa loja! Você pode entrar usando o email **:email** e começar a comprar.",
            "possibilities" => "Seu conta permite ver todos os pedidos feitos.",
            "subject" => "Bem vindo a nossa loja, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Endereço de cobrança",
            "billing_and_shipping" => "Endereço de cobrança e entrega",
            "currently_pending" => "O pagamento para este pedido está pendente.",
            "has_been_paid_on" => "O pedido foi pago em",
            "shipping_address" => "Endereço de entrega",
            "track_shipping_status" => "Você pode verificar o status da entrega com as informações abaixo:"
        ],
        "shipped" => [
            "message" => "Seu pedido **#:number** foi enviado.",
            "subject" => "Seu pedido foi enviado"
        ],
        "state_changed" => [
            "message" => "O seu pedido **#:number** foi atualizado para o status: **:state**",
            "subject" => "O status do seu pedido mudou!"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "O pagamento do seu pedido **#:number** falhou",
            "payment_advice" => "Entre na sua conta e tente pagar o pedido novamente.",
            "subject" => "O pagamento do seu pedido falhou",
            "support" => "Se o problema continuar entre em contato conosco."
        ],
        "paid" => [
            "message" => "Nós acabamos de receber o pagamento do seu pedido **#:number**.",
            "process_order" => "Nós agora estamos preparando seu pedido para a próxima etapa.",
            "subject" => "Obrigado pelo seu pagamento"
        ],
        "refunded" => [
            "duration" => "Por favor, aguarde alguns dias para receber o valor devolvido.",
            "message" => "Nós acabamos de devolver o pagamento do seu pedido **#:number**.",
            "subject" => "Seu pagamento foi devolvido"
        ]
    ]
];

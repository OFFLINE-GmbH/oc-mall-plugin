<?php return [
    'common' => [
        'hello' => 'Olá :firstname',
        'view_order_status_online' => 'Veja o status do seu pedido',
        'view_order_in_backend' => 'Ver pedido no painel da loja',
        'order_details' => 'Detalhes do pedido',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Seu pagamento foi devolvido',
            'message' => 'Nós acabamos de devolver o pagamento do seu pedido **#:number**.',
            'duration' => 'Por favor, aguarde alguns dias para receber o valor devolvido.',
        ],
        'paid' => [
            'subject' => 'Obrigado pelo seu pagamento',
            'message' => 'Nós acabamos de receber o pagamento do seu pedido **#:number**.',
            'process_order' => 'Nós agora estamos preparando seu pedido para a próxima etapa.',
        ],
        'failed' => [
            'subject' => 'O pagamento do seu pedido falhou',
            'message' => 'O pagamento do seu pedido **#:number** falhou',
            'payment_advice' => 'Entre na sua conta e tente pagar o pedido novamente.',
            'support' => 'Se o problema continuar entre em contato conosco.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Endereço de cobrança',
            'billing_and_shipping' => 'Endereço de cobrança e entrega',
            'shipping_address' => 'Endereço de entrega',
            'has_been_paid_on' => 'O pedido foi pago em',
            'currently_pending' => 'O pagamento para este pedido está pendente.',
            'track_shipping_status' => 'Você pode verificar o status da entrega com as informações abaixo:',
        ],
        'state_changed' => [
            'subject' => 'O status do seu pedido mudou!',
            'message' => 'O seu pedido **#:number** foi atualizado para o status: **:state**',
        ],
        'shipped' => [
            'subject' => 'Seu pedido foi enviado',
            'message' => 'Seu pedido **#:number** foi enviado.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Bem vindo a nossa loja, :firstname',
            'confirm_mail' => 'Bem vindo a nossa loja! Click no botão abaixo para confirmar seu email.',
            'message' => 'Bem vindo a nossa loja! Você pode entrar usando o email **:email** e começar a comprar.',
            'possibilities' => 'Seu conta permite ver todos os pedidos feitos.',
            'button' => [
                'confirm' => 'Confirmar endereço de email',
                'visit_store' => 'Visitar nossa loja',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Confirmação de pedido #:number',
            'thanks_for_order' => 'Obrigado pelo seu pedido. Nós recebemos as seguintes informações',
            'check_order_status' => 'Você pode verificar o status do pedido visitando a sua conta dentro da nossa loja.',
        ],
        'failed' => [
            'subject' => 'Falha no pedido #:number',
            'problem_message' => 'Pedimos desculpas mas houve um problema durante o seu pedido. Nós entraremos em contato para maiores informações.',
            'check_order_status' => 'Você pode verificar o status do pedido visitando a sua conta dentro da nossa loja.',
            'payment_id' => 'Código do pagamento',
            'error' => 'Mensagem de erro',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Novo Pedido #:number',
            'order_placed' => 'Novo pedido na loja:',
        ],
        'checkout_failed' => [
            'subject' => 'Falha no pagamento #:number',
            'not_processed' => 'Esse pedido não foi processado corretamente. Entre em contato com o cliente para informa-lo.',
            'error_details' => 'Detalhes do erro',
        ],
    ],
];

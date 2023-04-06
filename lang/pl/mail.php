<?php return [
    'common' => [
        'hello' => 'Hello :Imię',
        'view_order_status_online' => 'Wyświetl status zamówienia online',
        'view_order_in_backend' => 'Wyświetl zamówienie w zapleczu sklepu',
        'order_details' => 'Szczegóły zamówienia',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Twoja płatność została zwrócona',
            'message' => 'Właśnie dokonaliśmy zwrotu płatności za Twoje zamawienie **#:number**.',
            'duration' => 'Proszę miej na uwadze, że zwrot płatności może potrwać do kilku dni.',
        ],
        'paid' => [
            'subject' => 'Dziękujemy za dokonanie płatności',
            'message' => 'Właśnie otrzymaliśmy płatność za Twoje zamówienie **#:number**.',
            'process_order' => 'Rozpoczęliśmy dalszą realizację zamówienia.',
        ],
        'failed' => [
            'subject' => 'Płatność za Twoje zamówienie nie powiodła się',
            'message' => 'Chcemy tylko poinformować, że płatność za Twoje zamówienie **#:number** nie powiodła się',
            'payment_advice' => 'Zaloguj się do swojego konta i spróbuj ponownie opłacić zamówienie.',
            'support' => 'Jeśli nadal pojawiają się problemy z płatnością skontaktuj się z nami.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Adres płatności',
            'billing_and_shipping' => 'Adres płatności i dostawy',
            'shipping_address' => 'Adres dostawy',
            'has_been_paid_on' => 'Zamówienie zostało opłacone',
            'currently_pending' => 'Twoje zamówienie oczekuje na realizację płatności.',
            'track_shipping_status' => 'Możesz śledzić status zamówienia z poniższymi informacjami:',
        ],
        'state_changed' => [
            'subject' => 'Zmienił się status zamówienia',
            'message' => 'Informujemy, że status Twojego zamówienia **#:number** został zaktualizowany na: **:state**',
        ],
        'shipped' => [
            'subject' => 'Twoje zamówienie zostało wysłane',
            'message' => 'Twoje zamówienie **#:number** zostało wysłaned.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Witaj w sklepie, :firstname',
            'confirm_mail' => 'Witaj w naszym sklepie! Kliknij w przycisk poniżej żeby potwierdzić adres e-mail.',
            'message' => 'Witaj w naszym sklepie! Możesz się zalogować używając adresu e-mail **:email** i rozpocząć swoje zakupy.',
            'possibilities' => 'Twoje konto klienta pozwala Ci śledzić bieżące i poprzednie zamówienia.',
            'button' => [
                'confirm' => 'Potwierdź adres e-mail',
                'visit_store' => 'Odwiedź nasz sklep',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Potwierdzenie zamówienia #:number',
            'thanks_for_order' => 'Dziękujemy za Twoje zamówienie. Otrzymaliśmy poniższe informacje',
            'check_order_status' => 'You can check the status of your order by visiting the account section of our store.',
        ],
        'failed' => [
            'subject' => 'Checkout error for order #:number',
            'problem_message' => 'We are very sorry that there was a problem during your checkout process. We will look into the problem and contact you with further information.',
            'check_order_status' => 'To check the status of your order you may log in to our store at any time.',
            'payment_id' => 'Payment ID',
            'error' => 'Error message',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'New order #:number',
            'order_placed' => 'The following order was placed in your store:',
        ],
        'checkout_failed' => [
            'subject' => 'Checkout failed #:number',
            'not_processed' => 'The following order could not be processed correctly. It is possible that you have to contact the customer.',
            'error_details' => 'Error details',
        ],
        'payment_paid' => [
            'subject' => 'Payment for order #:number successful',
            'message' => 'The previously failed payment for this order succeeded on a subsequent attempt.',
        ],
    ],
];

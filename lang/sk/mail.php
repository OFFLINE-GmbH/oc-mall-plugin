<?php return [
    'common' => [
        'hello' => 'Dobrý deň :firstname',
        'view_order_status_online' => 'Zobraziť stav objednávky online',
        'view_order_in_backend' => 'Zobraziť objednávku v backende obchodu',
        'order_details' => 'Podrobnosti objednávky',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Vaša platba bola vrátená',
            'message' => 'Vrátili sme platbu Vašej objednávky číslo **#:number**.',
            'duration' => 'Nezabudnite, že môže trvať niekoľko dní, než Vám budú peniaze pripísané späť na účet.',
        ],
        'paid' => [
            'subject' => 'Ďakujeme za Vašu platbu',
            'message' => 'Práve sme dostali platbu za Vašu objednávku **#:number**.',
            'process_order' => 'Objednávku budeme ďalej spracovávať.',
        ],
        'failed' => [
            'subject' => 'Platba za objednávku se nepodarila',
            'message' => 'Chceli sme Vás informovať, že platba za objednávku **#:number** sa nepodarila.',
            'payment_advice' => 'Prihláste sa prosím do svojo účtu a skúste objednávku znovu zaplatiť.',
            'support' => 'Pokiaľ budete mať aj naďalej problémy s platbami, kontaktujte nás.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Fakturačná addresa',
            'billing_and_shipping' => 'Fakturačná a doručovacia addresa',
            'shipping_address' => 'Doručovacia addresa',
            'has_been_paid_on' => 'Objednávka bola zaplatená',
            'currently_pending' => 'Čakáme na potvrdenie platby za túto objednávku.',
            'track_shipping_status' => 'Doručovanie objednávky môžete sledova s týmito informáciami:',
        ],
        'state_changed' => [
            'subject' => 'Stav Vašej objednávky sa zmenil',
            'message' => 'Chceli sme Vás informovať, že Vaša objednávka **#:number** bola aktualizovaná na nový stav: **:state**',
        ],
        'shipped' => [
            'subject' => 'Vaša objednávka bola odoslaná',
            'message' => 'Vaša objednávka **#:number** bola odoslaná.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Vitajte v našom obchode :firstname',
            'confirm_mail' => 'Vitajte v našom obchode! Kliknite na tlačítko nižšie a potvrďte svoju e-mailovú adresu.',
            'message' => 'Vitajte v našom obchode! Môžete sa prihlásiť pomocou svojej e-mailovej adresy **:e-mail** a okamžite začať nakupovať.',
            'possibilities' => 'Váš užívatelský účet umožňuje sledovať históriu objednávok.',
            'button' => [
                'confirm' => 'Potvrďte svoju emailovú adresu',
                'visit_store' => 'Navštívte náš obchod',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Potvrdenie objednávky #:number',
            'thanks_for_order' => 'Ďakujeme za Vašu objednávku. Dostali sme nasledujúce informácie',
            'check_order_status' => 'Stav objednávky môžete skontrolovať v sekcií účtu v našom obchode.',
        ],
        'failed' => [
            'subject' => 'Chyba pri objednávke #:number',
            'problem_message' => 'Je nám veľmi ľúto, že behom procesu platby došlo k problému. Budeme Vás kontaktovať s ďalšími informáciami.',
            'check_order_status' => 'Ak chcete skontrolovať stav svojej objednávky, môžete sa kedykoľvek prihlásiť do nášho obchodu.',
            'payment_id' => 'ID platby',
            'error' => 'Chybové hlásenie',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Nová objednávka #:number',
            'order_placed' => 'Vo vašom obchode bola zadaná nasledujúca objednávka:',
        ],
        'checkout_failed' => [
            'subject' => 'Objednávka se nepodarila #:number',
            'not_processed' => 'Nasledujúcu objednávku sa nepodarilo správne spracovať. Je možné, že musíte kontaktovat zákazníka.',
            'error_details' => 'Detaily chyby',
        ],
        'payment_paid' => [
            'subject' => 'Platba za objednávku #:number bola úspešná',
            'message' => 'Predchádzajúca neúspešná platba za túto objednávku bola úspešne v nasledujúcom pokuse.',
        ],
    ],
];


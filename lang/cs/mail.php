<?php return [
    'common'   => [
        'hello'                    => 'Dobrý den :firstname',
        'view_order_status_online' => 'Zobrazit stav objednávky online',
        'view_order_in_backend'    => 'Zobrazit objednávku v backendu obchodu',
        'order_details'            => 'Podrobnosti objednávky',
    ],
    'payment'  => [
        'refunded' => [
            'subject'  => 'Vaše platba byla vrácena',
            'message'  => 'Vrátili jsme platbu Vaši objednávky číslo **#:number**.',
            'duration' => 'Nezapomeňte, že může trvat několik dní, než obdržíte Vaše peníze.',
        ],
        'paid'     => [
            'subject'       => 'Děkujeme za Vaši platbu',
            'message'       => 'Právě jsme obdrželi platbu za vaši objednávku **#:number**.',
            'process_order' => 'Nyní začneme objednávku dále zpracovávat.',
        ],
        'failed'   => [
            'subject'        => 'Platba za objednávku se nezdařila',
            'message'        => 'Chtěli jsme Vás informovat, že platba za objednávku **#:number** se nezdařila.',
            'payment_advice' => 'Přihlaste se prosím ke svému účtu a zkuste znovu zaplatit objednávku.',
            'support'        => 'Pokud budete mít i nadále problémy s platbami, kontaktujte nás.',
        ],
    ],
    'order'    => [
        'partials'      => [
            'billing_address'       => 'Fakturační adresa',
            'billing_and_shipping'  => 'Fakturační a dodací adresa',
            'shipping_address'      => 'Dodací adresa',
            'has_been_paid_on'      => 'Objednávka byla zaplacena',
            'currently_pending'     => 'Platba za tuto objednávku momentálně čeká na vyřízení.',
            'track_shipping_status' => 'Stav zásilky vaší objednávky můžete sledovat pomocí následujících informací:',
        ],
        'state_changed' => [
            'subject' => 'Stav vaší objednávky se změnil',
            'message' => 'Chtěli jsme vás informovat, že vaše objednávka **#:number** byla aktualizována na nový stav: **:state**',
        ],
        'shipped'       => [
            'subject' => 'Vaše objednávka byla odeslána',
            'message' => 'Vaše objednávka **#:number** byla odeslána.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject'       => 'Vítejte v našem obchodě :firstname',
            'confirm_mail'  => 'Vítejte v našem obchodě! Klikněte na tlačítko níže a potvrďte svou e-mailovou adresu.',
            'message'       => 'Vítejte v našem obchodě! Můžete se přihlásit pomocí své e-mailové adresy **:e-mail** a okamžitě začít nakupovat.',
            'possibilities' => 'Váš uživatelský účet umožňuje sledovat současné a minulé objednávky.',
            'button'        => [
                'confirm'     => 'Potvrďte svou emailovou adresu',
                'visit_store' => 'Navštivte náš obchod',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject'            => 'Potvrzení objednávky #:number',
            'thanks_for_order'   => 'Děkujeme za vaši objednávku. Obdrželi jsme následující informace',
            'check_order_status' => 'Stav objednávky můžete zkontrolovat v sekci účtu v našem obchodě.',
        ],
        'failed'    => [
            'subject'            => 'Chyba při objednávce #:number',
            'problem_message'    => 'Je nám velmi líto, že během procesu platby došlo k problému. Budeme se zabývat problémem a kontaktovat vás s dalšími informacemi.',
            'check_order_status' => 'Chcete-li zkontrolovat stav své objednávky, můžete se kdykoli přihlásit do našeho obchodu.',
            'payment_id'         => 'ID platby',
            'error'              => 'Chybové hlášení',
        ],
    ],
    'admin'    => [
        'checkout_succeeded' => [
            'subject'      => 'Nová objednávka #:number',
            'order_placed' => 'Ve vašem obchodě byla zadána následující objednávka:',
        ],
        'checkout_failed'    => [
            'subject'       => 'Objednávka se nezdařila #:number',
            'not_processed' => 'Následující objednávku nelze správně zpracovat. Je možné, že musíte kontaktovat zákazníka.',
            'error_details' => 'Detaily chyby',
        ],
    ],
];

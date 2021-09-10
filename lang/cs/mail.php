<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Detaily chyby",
            "not_processed" => "Následující objednávku nelze správně zpracovat. Je možné, že musíte kontaktovat zákazníka.",
            "payment_id" => "",
            "subject" => "Objednávka se nezdařila #:number"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Ve vašem obchodě byla zadána následující objednávka:",
            "subject" => "Nová objednávka #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Chcete-li zkontrolovat stav své objednávky, můžete se kdykoli přihlásit do našeho obchodu.",
            "error" => "Chybové hlášení",
            "payment_id" => "ID platby",
            "problem_message" => "Je nám velmi líto, že během procesu platby došlo k problému. Budeme se zabývat problémem a kontaktovat vás s dalšími informacemi.",
            "subject" => "Chyba při objednávce #:number"
        ],
        "succeeded" => [
            "check_order_status" => "Stav objednávky můžete zkontrolovat v sekci účtu v našem obchodě.",
            "subject" => "Potvrzení objednávky #:number",
            "thanks_for_order" => "Děkujeme za vaši objednávku. Obdrželi jsme následující informace"
        ]
    ],
    "common" => [
        "hello" => "Dobrý den :firstname",
        "order_details" => "Podrobnosti objednávky",
        "view_order_in_backend" => "Zobrazit objednávku v backendu obchodu",
        "view_order_status_online" => "Zobrazit stav objednávky online"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Potvrďte svou emailovou adresu",
                "visit_store" => "Navštivte náš obchod"
            ],
            "confirm_mail" => "Vítejte v našem obchodě! Klikněte na tlačítko níže a potvrďte svou e-mailovou adresu.",
            "message" => "Vítejte v našem obchodě! Můžete se přihlásit pomocí své e-mailové adresy **:e-mail** a okamžitě začít nakupovat.",
            "possibilities" => "Váš uživatelský účet umožňuje sledovat současné a minulé objednávky.",
            "subject" => "Vítejte v našem obchodě :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Fakturační adresa",
            "billing_and_shipping" => "Fakturační a dodací adresa",
            "currently_pending" => "Platba za tuto objednávku momentálně čeká na vyřízení.",
            "has_been_paid_on" => "Objednávka byla zaplacena",
            "shipping_address" => "Dodací adresa",
            "track_shipping_status" => "Stav zásilky vaší objednávky můžete sledovat pomocí následujících informací:"
        ],
        "shipped" => [
            "message" => "Vaše objednávka **#:number** byla odeslána.",
            "subject" => "Vaše objednávka byla odeslána"
        ],
        "state_changed" => [
            "message" => "Chtěli jsme vás informovat, že vaše objednávka **#:number** byla aktualizována na nový stav: **:state**",
            "subject" => "Stav vaší objednávky se změnil"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Chtěli jsme Vás informovat, že platba za objednávku **#:number** se nezdařila.",
            "payment_advice" => "Přihlaste se prosím ke svému účtu a zkuste znovu zaplatit objednávku.",
            "subject" => "Platba za objednávku se nezdařila",
            "support" => "Pokud budete mít i nadále problémy s platbami, kontaktujte nás."
        ],
        "paid" => [
            "message" => "Právě jsme obdrželi platbu za vaši objednávku **#:number**.",
            "process_order" => "Nyní začneme objednávku dále zpracovávat.",
            "subject" => "Děkujeme za Vaši platbu"
        ],
        "refunded" => [
            "duration" => "Nezapomeňte, že může trvat několik dní, než obdržíte Vaše peníze.",
            "message" => "Vrátili jsme platbu Vaši objednávky číslo **#:number**.",
            "subject" => "Vaše platba byla vrácena"
        ]
    ]
];

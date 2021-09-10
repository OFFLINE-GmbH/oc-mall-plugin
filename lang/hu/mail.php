<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Hiba részletei",
            "not_processed" => "A következő rendelést nem lehetett feldolgozni. Lehetséges, hogy fel kell venni a kapcsolatot a vevővel.",
            "payment_id" => "",
            "subject" => "Sikertelen fizetés (#:number)"
        ],
        "checkout_succeeded" => [
            "order_placed" => "A következő rendelés lett leadva:",
            "subject" => "Új rendelés #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "A rendelésed állapotának megtekintéséhez jelentkezz be webáruházunkba.",
            "error" => "Hiba üzenet",
            "payment_id" => "Fizetés azonosítója",
            "problem_message" => "Sajnálatos módon a fizetési folyamat közben hiba történt. Felül fogjuk vizsgálni az esetet és mihamarabb visszajelzünk további információval.",
            "subject" => "Fizetési hiba rendelésnél (#:number)"
        ],
        "succeeded" => [
            "check_order_status" => "Rendelésed állapotát megtekintheted weboldalunk felhasználói felületén.",
            "subject" => "Rendelés megerősítés (#:number)",
            "thanks_for_order" => "Köszönjük a rendelésed. A következő információt kaptuk meg"
        ]
    ],
    "common" => [
        "hello" => "Helló :firstname",
        "order_details" => "Rendelés részletei",
        "view_order_in_backend" => "Rendelés megtekintése backend-en",
        "view_order_status_online" => "Rendelés állapotának megtekintése"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "E-mail cím megerősítése",
                "visit_store" => "Látogass el webáruházunkba"
            ],
            "confirm_mail" => "Üdvözlünk az webáruházunkban! Kérjük kattints az alábbi gombra e-mail címed megerősítéséhez.",
            "message" => "Üdvözlünk az webáruházunkban! Bejelentkezéshez használhatod az e-mail címed (**:email**) és megkezdheted a vásárlást.",
            "possibilities" => "Felhasználói fiókoddal nyomon követheted a jelenlegi és múltbeli rendeléseid.",
            "subject" => "Üdvözlünk az webáruházunkban, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Számlázási cím",
            "billing_and_shipping" => "Számlázási és szállítási cím",
            "currently_pending" => "A rendeléshez tartozó fizetés folyamatban van.",
            "has_been_paid_on" => "Rendelés fizetésének dátuma",
            "shipping_address" => "Szállítási cím",
            "track_shipping_status" => "A következő információval követheted rendelésed szállítási állapotát:"
        ],
        "shipped" => [
            "message" => "**#:number** számú rendelésed ki lett szállítva.",
            "subject" => "Rendelésed ki lett szállítva"
        ],
        "state_changed" => [
            "message" => "Tudomásodra szeretnénk hozni, hogy **#:number** számú rendelésed állapota frissítésre került: **:state**",
            "subject" => "Rendelésed állapota megváltozott"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Tudatodra szeretnénk hozni, hogy **#:number** számú rendelésdhez tartozó fizetés sikertelen volt",
            "payment_advice" => "Kérjük jelentkezz be fiókodba és próbálkozz újra.",
            "subject" => "Rendelésedhez tartozó fizetés sikertelen volt",
            "support" => "Ha továbbra is problémák merülnek fel, lépj kapcsolatba velünk."
        ],
        "paid" => [
            "message" => "**#:number** számú rendeléshez tartozó fizetésed megkaptuk.",
            "process_order" => "Rendelésed további feldolgozását ezennel megkezdjük.",
            "subject" => "Köszönjük a fizetésed"
        ],
        "refunded" => [
            "duration" => "Kérjük vedd figyelembe, hogy a viszatérített összeg megérkezése pár napig eltarthat.",
            "message" => "**#:number** számú rendeléshez tartozó fizetésed vissza lett térítve.",
            "subject" => "Fizetésed vissza lett térítve"
        ]
    ]
];

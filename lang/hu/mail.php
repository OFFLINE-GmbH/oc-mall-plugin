<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "Hiba üzenet",
            "error_details" => "Hiba részletei",
            "not_processed" => "A következő sorrendet nem sikerült megfelelően feldolgozni. Lehetséges, hogy kapcsolatba kell lépnie az ügyféllel.",
            "payment_id" => "Fizetési azonosító",
            "subject" => "A fizetés nem sikerült #: szám"
        ],
        "checkout_succeeded" => [
            "order_placed" => "A következő megrendelés történt az üzletben:",
            "subject" => "Új rendelési szám: szám"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "A rendelés állapotának ellenőrzéséhez bármikor bejelentkezhet üzletünkbe.",
            "error" => "Hiba üzenet",
            "payment_id" => "Fizetési azonosító",
            "problem_message" => "Nagyon sajnáljuk, hogy probléma merült fel a fizetési folyamat során. Megvizsgáljuk a problémát, és további információval kapcsolatba lépünk Önnel.",
            "subject" => "Pénztár hiba a rendelési számhoz::"
        ],
        "succeeded" => [
            "check_order_status" => "Megrendelésének állapotát az üzlet fiókrészletén ellenőrizheti.",
            "subject" => "Megerősítés a rendelési számhoz: szám",
            "thanks_for_order" => "Nagyon köszönöm a megrendelését. A következő információkat kaptuk meg"
        ]
    ],
    "common" => [
        "hello" => "Helló: keresztnév",
        "order_details" => "Megrendelés részletei",
        "view_order_in_backend" => "Megrendelés megtekintése az áruház háttérrendszerében",
        "view_order_status_online" => "Megrendelés állapotának megtekintése online"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Erősítse meg az e-mail címét",
                "visit_store" => "Látogasson el üzletünkbe"
            ],
            "confirm_mail" => "Üdvözöljük üzletünkben! Kattintson az alábbi gombra az e-mail cím megerősítéséhez.",
            "message" => "Üdvözöljük üzletünkben! Bejelentkezhet az e-mail címével **: e-mail **, és azonnal elkezdhet vásárolni.",
            "possibilities" => "Felhasználói fiókja lehetővé teszi a nyitott és a korábbi megrendelések nyomon követését.",
            "subject" => "Üdvözöljük üzletünkben: keresztnév"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Számlázási cím",
            "billing_and_shipping" => "Számlázási és szállítási cím",
            "currently_pending" => "A rendelés befizetése jelenleg függőben van.",
            "has_been_paid_on" => "A megrendelés kifizetésre került",
            "shipping_address" => "Szállítási cím",
            "track_shipping_status" => "A következő információkkal nyomon tudja követni a rendelés szállítási állapotát:"
        ],
        "shipped" => [
            "message" => "Megrendelése ** #: száma ** szállítva.",
            "subject" => "Megrendelését kiszállítottuk"
        ],
        "state_changed" => [
            "message" => "Csak azt akartuk tudatni, hogy megrendelése ** #: száma ** frissült az új állapotba: **: állapot **",
            "subject" => "Megrendelésének állapota megváltozott"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Csak azt akartuk tudatni, hogy a ** #: szám ** megrendelés kifizetése sikertelen",
            "payment_advice" => "Kérjük, jelentkezzen be fiókjába, és próbálja újra fizetni a megrendelést.",
            "subject" => "A megrendelés kifizetése sikertelen",
            "support" => "Ha továbbra is problémái vannak a fizetésekkel, kérjük, vegye fel velünk a kapcsolatot."
        ],
        "paid" => [
            "message" => "Most kaptuk megfizetését a megrendelésért ** #: szám **.",
            "process_order" => "Most megkezdjük a rendelés további feldolgozását.",
            "subject" => "Köszönjük fizetését"
        ],
        "refunded" => [
            "duration" => "Felhívjuk figyelmét, hogy több napot is igénybe vehet, amíg megkapja a pénzt.",
            "message" => "Most visszatérítettük a megrendelés kifizetését ** #: szám **.",
            "subject" => "Befizetését visszatérítettük"
        ]
    ]
];

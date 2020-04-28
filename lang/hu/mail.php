<?php

return [
    'common' => [
        'hello' => 'Helló :firstname',
        'view_order_status_online' => 'Rendelés állapotának megtekintése',
        'view_order_in_backend' => 'Rendelés megtekintése backend-en',
        'order_details' => 'Rendelés részletei',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Fizetésed vissza lett térítve',
            'message' => '**#:number** számú rendeléshez tartozó fizetésed vissza lett térítve.',
            'duration' => 'Kérjük vedd figyelembe, hogy a viszatérített összeg megérkezése pár napig eltarthat.',
        ],
        'paid' => [
            'subject' => 'Köszönjük a fizetésed',
            'message' => '**#:number** számú rendeléshez tartozó fizetésed megkaptuk.',
            'process_order' => 'Rendelésed további feldolgozását ezennel megkezdjük.',
        ],
        'failed' => [
            'subject' => 'Rendelésedhez tartozó fizetés sikertelen volt',
            'message' => 'Tudatodra szeretnénk hozni, hogy **#:number** számú rendelésdhez tartozó fizetés sikertelen volt',
            'payment_advice' => 'Kérjük jelentkezz be fiókodba és próbálkozz újra.',
            'support' => 'Ha továbbra is problémák merülnek fel, lépj kapcsolatba velünk.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Számlázási cím',
            'billing_and_shipping' => 'Számlázási és szállítási cím',
            'shipping_address' => 'Szállítási cím',
            'has_been_paid_on' => 'Rendelés fizetésének dátuma',
            'currently_pending' => 'A rendeléshez tartozó fizetés folyamatban van.',
            'track_shipping_status' => 'A következő információval követheted rendelésed szállítási állapotát:',
        ],
        'state_changed' => [
            'subject' => 'Rendelésed állapota megváltozott',
            'message' => 'Tudomásodra szeretnénk hozni, hogy **#:number** számú rendelésed állapota frissítésre került: **:state**',
        ],
        'shipped' => [
            'subject' => 'Rendelésed ki lett szállítva',
            'message' => '**#:number** számú rendelésed ki lett szállítva.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Üdvözlünk az webáruházunkban, :firstname',
            'confirm_mail' => 'Üdvözlünk az webáruházunkban! Kérjük kattints az alábbi gombra e-mail címed megerősítéséhez.',
            'message' => 'Üdvözlünk az webáruházunkban! Bejelentkezéshez használhatod az e-mail címed (**:email**) és megkezdheted a vásárlást.',
            'possibilities' => 'Felhasználói fiókoddal nyomon követheted a jelenlegi és múltbeli rendeléseid.',
            'button' => [
                'confirm' => 'E-mail cím megerősítése',
                'visit_store' => 'Látogass el webáruházunkba',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Rendelés megerősítés (#:number)',
            'thanks_for_order' => 'Köszönjük a rendelésed. A következő információt kaptuk meg',
            'check_order_status' => 'Rendelésed állapotát megtekintheted weboldalunk felhasználói felületén.',
        ],
        'failed' => [
            'subject' => 'Fizetési hiba rendelésnél (#:number)',
            'problem_message' => 'Sajnálatos módon a fizetési folyamat közben hiba történt. Felül fogjuk vizsgálni az esetet és mihamarabb visszajelzünk további információval.',
            'check_order_status' => 'A rendelésed állapotának megtekintéséhez jelentkezz be webáruházunkba.',
            'payment_id' => 'Fizetés azonosítója',
            'error' => 'Hiba üzenet',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Új rendelés #:number',
            'order_placed' => 'A következő rendelés lett leadva:',
        ],
        'checkout_failed' => [
            'subject' => 'Sikertelen fizetés (#:number)',
            'not_processed' => 'A következő rendelést nem lehetett feldolgozni. Lehetséges, hogy fel kell venni a kapcsolatot a vevővel.',
            'error_details' => 'Hiba részletei',
        ],
    ],
];

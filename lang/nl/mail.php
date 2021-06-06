<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Foutbericht",
            "not_processed" => "Volgende bestelling kon niet verwerkt worden. Gelieve indien nodig de klant te contacteren.",
            "payment_id" => "",
            "subject" => "Probleem tijdens afrekenen (bestelling #:number)"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Volgende bestelling werd zonet geplaatst:",
            "subject" => "Nieuwe bestelling #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Om de status van je bestelling op te volgen kun je je inloggen op de site.",
            "error" => "Foutbericht",
            "payment_id" => "ID van betaling",
            "problem_message" => "Er heeft zich een probleem voorgedaan tijdens het afrekenen. Gelieve ons te contacteren met alle foutmeldingen en je bestelnummer, dan proberen wij het zo spoedig mogelijk recht te zetten.",
            "subject" => "Probleem tijdens het afrekenen (bestelling #:number)"
        ],
        "succeeded" => [
            "check_order_status" => "Je kunt je bestelling opvolgen via \"mijn account\" op de website.",
            "subject" => "Bevestiging van bestelling #:number",
            "thanks_for_order" => "Bedankt voor je bestelling. We ontvingen onderstaande informatie:"
        ]
    ],
    "common" => [
        "hello" => "Hallo :firstname",
        "order_details" => "Details van bestelling",
        "view_order_in_backend" => "Bestelling in het besteloverzicht bekijken",
        "view_order_status_online" => "Status van bestelling bekijken"
    ],
    "customer" => [
        "created" => [
            "button" => ["confirm" => "E-mailadres bevestigen", "visit_store" => "Winkel bezoeken"],
            "confirm_mail" => "Welkom! Bevestig je e-mailadres met onderstaande knop.",
            "message" => "Welkom! Je kan inloggen met je e-mailadres **:email** om meteen online te winkelen.",
            "possibilities" => "Je account geeft je een overzicht van je bestellingen.",
            "subject" => "Welkom, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Facturatieadres",
            "billing_and_shipping" => "Facturatie- en verzendingsadres",
            "currently_pending" => "De betaling is nog niet voltooid.",
            "has_been_paid_on" => "De bestelling werd betaald op",
            "shipping_address" => "Verzendingsadres",
            "track_shipping_status" => "Je kunt de verzendingsstatus van je bestelling opvolgen via:"
        ],
        "shipped" => [
            "message" => "Je bestelling met nummer **#:number** is verzonden.",
            "subject" => "Je bestelling is verzonden"
        ],
        "state_changed" => [
            "message" => "Je bestelling met nummer **#:number** werd aangepast naar: **:state**",
            "subject" => "De status van je bestelling werd aangepast"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "De betaling voor je bestelling **#:number** is mislukt.",
            "payment_advice" => "Log je in in je account om de betaling opnieuw te proberen.",
            "subject" => "Betaling mislukt",
            "support" => "Contacteer ons mocht het toch blijven mislopen."
        ],
        "paid" => [
            "message" => "We hebben je betaling voor bestelling **#:number** goed ontvangen.",
            "process_order" => "We gaan aan de slag om je bestelling te verwerken.",
            "subject" => "Bedankt voor je betaling"
        ],
        "refunded" => [
            "duration" => "Het kan enkele dagen duren tot je de terugbetaling ontvangen hebt.",
            "message" => "We hebben je bestelling **#:number** terugbetaald.",
            "subject" => "Je bestelling werd terugbetaald"
        ]
    ]
];

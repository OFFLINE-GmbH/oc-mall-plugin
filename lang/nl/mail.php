<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "foutmelding",
            "error_details" => "Fout details",
            "not_processed" => "De volgende bestelling kan niet correct worden verwerkt. Het is mogelijk dat u contact moet opnemen met de klant.",
            "payment_id" => "Betalings-ID",
            "subject" => "Afrekenen mislukt #: nummer"
        ],
        "checkout_succeeded" => [
            "order_placed" => "De volgende bestelling is in uw winkel geplaatst:",
            "subject" => "Nieuw order #: nummer"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Om de status van uw bestelling te controleren, kunt u op elk gewenst moment inloggen in onze winkel.",
            "error" => "foutmelding",
            "payment_id" => "Betalings-ID",
            "problem_message" => "Het spijt ons zeer dat er een probleem is opgetreden tijdens het afrekenen. We zullen het probleem onderzoeken en contact met u opnemen voor meer informatie.",
            "subject" => "Checkout-fout voor bestelnummer: nummer"
        ],
        "succeeded" => [
            "check_order_status" => "U kunt de status van uw bestelling controleren door naar het accountgedeelte van onze winkel te gaan.",
            "subject" => "Bevestiging voor bestelnummer: nummer",
            "thanks_for_order" => "Hartelijk dank voor uw bestelling. We hebben de volgende informatie ontvangen"
        ]
    ],
    "common" => [
        "hello" => "Hallo: voornaam",
        "order_details" => "Bestel Details",
        "view_order_in_backend" => "Bestelling in backend van winkel bekijken",
        "view_order_status_online" => "Bekijk bestelstatus online"
    ],
    "customer" => [
        "created" => [
            "button" => ["confirm" => "Bevestig je e-mailadres", "visit_store" => "Bezoek onze winkel"],
            "confirm_mail" => "Welkom in onze winkel! Klik op onderstaande knop om uw e-mailadres te bevestigen.",
            "message" => "Welkom in onze winkel! U kunt inloggen met uw e-mailadres **: e-mail ** en meteen beginnen met winkelen.",
            "possibilities" => "Met uw gebruikersaccount kunt u openstaande en eerdere bestellingen bijhouden.",
            "subject" => "Welkom in onze winkel,: voornaam"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Facturatie adres",
            "billing_and_shipping" => "Factuur- en verzendadres",
            "currently_pending" => "De betaling voor deze bestelling is momenteel in behandeling.",
            "has_been_paid_on" => "De bestelling is betaald",
            "shipping_address" => "Verzendingsadres",
            "track_shipping_status" => "U kunt de verzendstatus van uw bestelling volgen met de volgende informatie:"
        ],
        "shipped" => [
            "message" => "Uw bestelling ** #: nummer ** is verzonden.",
            "subject" => "Je bestelling is verzonden"
        ],
        "state_changed" => [
            "message" => "We wilden u alleen laten weten dat uw bestelling ** #: nummer ** is bijgewerkt naar de nieuwe status: **: staat **",
            "subject" => "De status van uw bestelling is gewijzigd"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "We wilden u alleen laten weten dat de betaling voor bestelling ** #: nummer ** is mislukt",
            "payment_advice" => "Log in op uw account en probeer opnieuw om de bestelling te betalen.",
            "subject" => "De betaling voor uw bestelling is mislukt",
            "support" => "Neem contact met ons op als u problemen blijft ondervinden met betalingen."
        ],
        "paid" => [
            "message" => "We hebben zojuist een betaling ontvangen voor uw bestelling ** #: nummer **.",
            "process_order" => "We zullen nu beginnen met het verder verwerken van de bestelling.",
            "subject" => "Bedankt voor uw betaling"
        ],
        "refunded" => [
            "duration" => "Houd er rekening mee dat het meerdere dagen kan duren voordat u uw geld ontvangt.",
            "message" => "We hebben zojuist de betaling voor uw bestelling teruggestort ** #: nummer **.",
            "subject" => "Uw betaling is terugbetaald"
        ]
    ]
];

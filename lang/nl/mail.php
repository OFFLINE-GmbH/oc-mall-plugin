<?php return [
    'common' => [
        'hello' => 'Hallo :firstname',
        'view_order_status_online' => 'Status van bestelling bekijken',
        'view_order_in_backend' => 'Bestelling in het besteloverzicht bekijken',
        'order_details' => 'Details van bestelling',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Je bestelling werd terugbetaald',
            'message' => 'We hebben je bestelling **#:number** terugbetaald.',
            'duration' => 'Het kan enkele dagen duren tot je de terugbetaling ontvangen hebt.',
        ],
        'paid' => [
            'subject' => 'Bedankt voor je betaling',
            'message' => 'We hebben je betaling voor bestelling **#:number** goed ontvangen.',
            'process_order' => 'We gaan aan de slag om je bestelling te verwerken.',
        ],
        'failed' => [
            'subject' => 'Betaling mislukt',
            'message' => 'De betaling voor je bestelling **#:number** is mislukt.',
            'payment_advice' => 'Log je in in je account om de betaling opnieuw te proberen.',
            'support' => 'Contacteer ons mocht het toch blijven mislopen.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Facturatieadres',
            'billing_and_shipping' => 'Facturatie- en verzendingsadres',
            'shipping_address' => 'Verzendingsadres',
            'has_been_paid_on' => 'De bestelling werd betaald op',
            'currently_pending' => 'De betaling is nog niet voltooid.',
            'track_shipping_status' => 'Je kunt de verzendingsstatus van je bestelling opvolgen via:',
        ],
        'state_changed' => [
            'subject' => 'De status van je bestelling werd aangepast',
            'message' => 'Je bestelling met nummer **#:number** werd aangepast naar: **:state**',
        ],
        'shipped' => [
            'subject' => 'Je bestelling is verzonden',
            'message' => 'Je bestelling met nummer **#:number** is verzonden.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Welkom, :firstname',
            'confirm_mail' => 'Welkom! Bevestig je e-mailadres met onderstaande knop.',
            'message' => 'Welkom! Je kan inloggen met je e-mailadres **:email** om meteen online te winkelen.',
            'possibilities' => 'Je account geeft je een overzicht van je bestellingen.',
            'button' => [
                'confirm' => 'E-mailadres bevestigen',
                'visit_store' => 'Winkel bezoeken',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Bevestiging van bestelling #:number',
            'thanks_for_order' => 'Bedankt voor je bestelling. We ontvingen onderstaande informatie:',
            'check_order_status' => 'Je kunt je bestelling opvolgen via "mijn account" op de website.',
        ],
        'failed' => [
            'subject' => 'Probleem tijdens het afrekenen (bestelling #:number)',
            'problem_message' => 'Er heeft zich een probleem voorgedaan tijdens het afrekenen. Gelieve ons te contacteren met alle foutmeldingen en je bestelnummer, dan proberen wij het zo spoedig mogelijk recht te zetten.',
            'check_order_status' => 'Om de status van je bestelling op te volgen kun je je inloggen op de site.',
            'payment_id' => 'ID van betaling',
            'error' => 'Foutbericht',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Nieuwe bestelling #:number',
            'order_placed' => 'Volgende bestelling werd zonet geplaatst:',
        ],
        'checkout_failed' => [
            'subject' => 'Probleem tijdens afrekenen (bestelling #:number)',
            'not_processed' => 'Volgende bestelling kon niet verwerkt worden. Gelieve indien nodig de klant te contacteren.',
            'error_details' => 'Foutbericht',
        ],
    ],
];

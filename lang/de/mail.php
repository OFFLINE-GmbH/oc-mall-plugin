<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "Fehlermeldung",
            "error_details" => "Fehler-Details",
            "not_processed" => "Die folgende Bestellung konnte leider nicht richtig verarbeitet werden. Möglicherweise müssen Sie mit dem Kunden kontakt aufnehmen.",
            "payment_id" => "Zahlungs-ID",
            "subject" => "Bestellung fehlgeschlagen #:number"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Die folgende Bestellung wurde im Shop ausgelöst:",
            "subject" => "Neue Bestellung #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Um den aktuellen Status Ihrer Bestellen zu prüfen, können Sie sich jederzeit mit Ihrem Benutzerkonto anmelden.",
            "error" => "Fehlermeldung",
            "payment_id" => "Zahlungs ID",
            "problem_message" => "Leider ist ein Problem während der Bearbeitung Ihrer Bestellung aufgetreten. Wir schauen uns das Problem an und melden uns bei Ihnen mit weiteren Informationen. Wir entschuldigen uns für die Unannehmlichkeiten.",
            "subject" => "Fehler bei der Bestellung #:number"
        ],
        "succeeded" => [
            "check_order_status" => "Den aktuellen Status Ihrer Bestellung können Sie jederzeit in Ihrem Benutzerkonto prüfen.",
            "subject" => "Bestätigung für Bestellung #:number",
            "thanks_for_order" => "Vielen Dank für Ihre Bestellung. Wir haben folgende Informationen erhalten"
        ]
    ],
    "common" => [
        "hello" => "Guten Tag :firstname",
        "order_details" => "Details zur Bestellung",
        "view_order_in_backend" => "Bestellung im Shop-Backend ansehen",
        "view_order_status_online" => "Bestell-Status online ansehen"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Bestätigen Sie Ihre E-Mail",
                "visit_store" => "Besuchen Sie unseren Online-Shop"
            ],
            "confirm_mail" => "Willkommen! Bitte klicken Sie unten auf den Button, um Ihre E-Mail zu bestätigen.",
            "message" => "Willkommen! Sie können sich ab sofort mit Ihrer E-Mail **:email** einloggen und mit dem Shopping beginnen.",
            "possibilities" => "In Ihrem Benutzerkonto können Sie offene oder vergangene Bestellungen einfach verwalten.",
            "subject" => "Herzlich Willkommen, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Rechnungsadresse",
            "billing_and_shipping" => "Rechnungs- und Lieferadresse",
            "currently_pending" => "Die Zahlung für diese Bestellung ist momentan noch ausstehend.",
            "has_been_paid_on" => "Ihre Bestellung wurde bezahlt am",
            "shipping_address" => "Lieferadresse",
            "track_shipping_status" => "Sie können die Lieferung Ihrer Bestellung mit den folgenden Informationen verfolgen:"
        ],
        "shipped" => [
            "message" => "Ihre Bestellung **#:number** wurde soeben versendet.",
            "subject" => "Ihre Bestellung ist unterwegs"
        ],
        "state_changed" => [
            "message" => "Wir möchten Sie darüber informieren, dass Ihre Bestellung **#:number** soeben einen neuen Status erhalten hat: **:state**",
            "subject" => "Der Status Ihrer Bestellung hat sich geändert"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Wir möchten Sie darüber informieren, dass die Zahlung für die Bestellung **#:number** fehlgeschlagen ist",
            "payment_advice" => "Bitte loggen Sie sich erneut ein und versuchen Sie, die Zahlung erneut auszuführen.",
            "subject" => "Die Zahlung für Ihre Bestellung ist fehlgeschlagen",
            "support" => "Bitte kontaktieren Sie uns, sollten Sie weiterhin Probleme mit der Zahlung haben."
        ],
        "paid" => [
            "message" => "Wir haben soeben eine Zahlung für die Bestellung **#:number** erhalten.",
            "process_order" => "Ihre Bestellung wir nun weiter verarbeitet.",
            "subject" => "Vielen Dank für Ihre Zahlung"
        ],
        "refunded" => [
            "duration" => "Die Rückerstattung kann einige Tage in Anspruch nehmen.",
            "message" => "Wir haben Ihre Zahlung für die Bestellung **#:number** soeben zurückerstattet",
            "subject" => "Ihre Zahlung würde zurückerstattet"
        ]
    ]
];

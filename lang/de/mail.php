<?php return [
    'common' => [
        'hello' => 'Guten Tag :firstname',
        'view_order_status_online' => 'Bestell-Status online ansehen',
        'view_order_in_backend' => 'Bestellung im Shop-Backend ansehen',
        'order_details' => 'Details zur Bestellung',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Ihre Zahlung würde zurückerstattet',
            'message' => 'Wir haben Ihre Zahlung für die Bestellung **#:number** soeben zurückerstattet',
            'duration' => 'Die Rückerstattung kann einige Tage in Anspruch nehmen.',
        ],
        'paid' => [
            'subject' => 'Vielen Dank für Ihre Zahlung',
            'message' => 'Wir haben soeben eine Zahlung für die Bestellung **#:number** erhalten.',
            'process_order' => 'Ihre Bestellung wir nun weiter verarbeitet.',
        ],
        'failed' => [
            'subject' => 'Die Zahlung für Ihre Bestellung ist fehlgeschlagen',
            'message' => 'Wir möchten Sie darüber informieren, dass die Zahlung für die Bestellung **#:number** fehlgeschlagen ist',
            'payment_advice' => 'Bitte loggen Sie sich erneut ein und versuchen Sie, die Zahlung erneut auszuführen.',
            'support' => 'Bitte kontaktieren Sie uns, sollten Sie weiterhin Probleme mit der Zahlung haben.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Rechnungsadresse',
            'billing_and_shipping' => 'Rechnungs- und Lieferadresse',
            'shipping_address' => 'Lieferadresse',
            'has_been_paid_on' => 'Ihre Bestellung wurde bezahlt am',
            'currently_pending' => 'Die Zahlung für diese Bestellung ist momentan noch ausstehend.',
            'track_shipping_status' => 'Sie können die Lieferung Ihrer Bestellung mit den folgenden Informationen verfolgen:',
        ],
        'state_changed' => [
            'subject' => 'Der Status Ihrer Bestellung hat sich geändert',
            'message' => 'Wir möchten Sie darüber informieren, dass Ihre Bestellung **#:number** soeben einen neuen Status erhalten hat: **:state**',
        ],
        'shipped' => [
            'subject' => 'Ihre Bestellung ist unterwegs',
            'message' => 'Ihre Bestellung **#:number** wurde soeben versendet.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Herzlich Willkommen, :firstname',
            'confirm_mail' => 'Willkommen! Bitte klicken Sie unten auf den Button, um Ihre E-Mail zu bestätigen.',
            'message' => 'Willkommen! Sie können sich ab sofort mit Ihrer E-Mail **:email** einloggen und mit dem Shopping beginnen.',
            'possibilities' => 'In Ihrem Benutzerkonto können Sie offene oder vergangene Bestellungen einfach verwalten.',
            'button' => [
                'confirm' => 'Bestätigen Sie Ihre E-Mail',
                'visit_store' => 'Besuchen Sie unseren Online-Shop',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Bestätigung für Bestellung #:number',
            'thanks_for_order' => 'Vielen Dank für Ihre Bestellung. Wir haben folgende Informationen erhalten',
            'check_order_status' => 'Den aktuellen Status Ihrer Bestellung können Sie jederzeit in Ihrem Benutzerkonto prüfen.',
        ],
        'failed' => [
            'subject' => 'Fehler bei der Bestellung #:number',
            'problem_message' => 'Leider ist ein Problem während der Bearbeitung Ihrer Bestellung aufgetreten. Wir schauen uns das Problem an und melden uns bei Ihnen mit weiteren Informationen. Wir entschuldigen uns für die Unannehmlichkeiten.',
            'check_order_status' => 'Um den aktuellen Status Ihrer Bestellen zu prüfen, können Sie sich jederzeit mit Ihrem Benutzerkonto anmelden.',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Neue Bestellung #:number',
            'order_placed' => 'Die folgende Bestellung wurde im Shop ausgelöst:',
        ],
        'checkout_failed' => [
            'subject' => 'Bestellung fehlgeschlagen #:number',
            'not_processed' => 'Die folgende Bestellung konnte leider nicht richtig verarbeitet werden. Möglicherweise müssen Sie mit dem Kunden kontakt aufnehmen.',
            'error_details' => 'Fehler-Details',
            'payment_id' => 'Zahlungs-ID',
            'error' => 'Fehlermeldung',
        ],
    ],
];

<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "Messaggio di errore",
            "error_details" => "Dettagli circa l'errore",
            "not_processed" => "Il seguente ordine non può essere elaborato correttamente. È possibile che tu debba contattare il cliente.",
            "payment_id" => "ID pagamento",
            "subject" => "Checkout non riuscito #: numero"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Nel tuo negozio è stato effettuato il seguente ordine:",
            "subject" => "Nuovo ordine n .: numero"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Per verificare lo stato del tuo ordine puoi accedere al nostro negozio in qualsiasi momento.",
            "error" => "Messaggio di errore",
            "payment_id" => "ID pagamento",
            "problem_message" => "Siamo spiacenti che si sia verificato un problema durante la procedura di pagamento. Esamineremo il problema e ti contatteremo per ulteriori informazioni.",
            "subject" => "Errore di checkout per numero ordine: numero"
        ],
        "succeeded" => [
            "check_order_status" => "Puoi controllare lo stato del tuo ordine visitando la sezione account del nostro negozio.",
            "subject" => "Conferma per numero ordine: numero",
            "thanks_for_order" => "Grazie mille per il tuo ordine. Abbiamo ricevuto le seguenti informazioni"
        ]
    ],
    "common" => [
        "hello" => "Ciao: nome",
        "order_details" => "Dettagli dell'ordine",
        "view_order_in_backend" => "Visualizza l'ordine nel backend del negozio",
        "view_order_status_online" => "Visualizza lo stato dell'ordine online"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Conferma il tuo indirizzo email",
                "visit_store" => "Visita il nostro negozio"
            ],
            "confirm_mail" => "Benvenuto nel nostro negozio! Fare clic sul pulsante in basso per confermare il proprio indirizzo e-mail.",
            "message" => "Benvenuto nel nostro negozio! Puoi accedere utilizzando il tuo indirizzo e-mail **: e-mail ** e iniziare subito a fare acquisti.",
            "possibilities" => "Il tuo account utente ti consente di tenere traccia degli ordini aperti e passati.",
            "subject" => "Benvenuti nel nostro negozio: nome"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Indirizzo Di Fatturazione",
            "billing_and_shipping" => "Indirizzo di fatturazione e spedizione",
            "currently_pending" => "Il pagamento per questo ordine è attualmente in sospeso.",
            "has_been_paid_on" => "L'ordine è stato pagato",
            "shipping_address" => "Indirizzo di spedizione",
            "track_shipping_status" => "Puoi monitorare lo stato di spedizione del tuo ordine con le seguenti informazioni:"
        ],
        "shipped" => [
            "message" => "Il tuo ordine ** #: numero ** è stato spedito.",
            "subject" => "il tuo ordine è stato spedito"
        ],
        "state_changed" => [
            "message" => "Volevamo solo farti sapere che il tuo ordine ** #: numero ** è stato aggiornato al nuovo stato: **: stato **",
            "subject" => "Lo stato del tuo ordine è cambiato"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Volevamo solo farti sapere che il pagamento per l'ordine ** #: numero ** non è riuscito",
            "payment_advice" => "Accedi al tuo account e riprova a pagare l'ordine.",
            "subject" => "Il pagamento per il tuo ordine non è riuscito",
            "support" => "Se continui a riscontrare problemi con i pagamenti, ti preghiamo di contattarci."
        ],
        "paid" => [
            "message" => "Abbiamo appena ricevuto un pagamento per il tuo ordine ** #: numero **.",
            "process_order" => "Ora inizieremo a elaborare ulteriormente l'ordine.",
            "subject" => "grazie per il tuo pagamento"
        ],
        "refunded" => [
            "duration" => "Tieni presente che potrebbero essere necessari più giorni prima di ricevere i fondi.",
            "message" => "Abbiamo appena rimborsato il pagamento per il tuo ordine ** #: numero **.",
            "subject" => "Il tuo pagamento è stato rimborsato"
        ]
    ]
];

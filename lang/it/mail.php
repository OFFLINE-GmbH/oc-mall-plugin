<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "",
            "error_details" => "Dettagli dell'errore",
            "not_processed" => "Il seguente ordine non può essere elaborato correttamente. È possibile che si debba contattare il cliente.",
            "payment_id" => "",
            "subject" => "Pagamento fallito #:number"
        ],
        "checkout_succeeded" => [
            "order_placed" => "Il seguente ordine è stato effettuato nel vostro negozio:",
            "subject" => "Nuovo ordine #:number"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Per controllare lo stato del suo ordine può accedere al nostro negozio in qualsiasi momento.",
            "error" => "Messaggio di errore",
            "payment_id" => "ID di pagamento",
            "problem_message" => "Siamo molto spiacenti che ci sia stato un problema durante il processo di checkout. Esamineremo il problema e ti contatteremo per ulteriori informazioni.",
            "subject" => "Errore di checkout per l'ordine #:number"
        ],
        "succeeded" => [
            "check_order_status" => "Puoi controllare lo stato del tuo ordine visitando la sezione account del nostro negozio.",
            "subject" => "Conferma per l'ordine #:number",
            "thanks_for_order" => "Grazie mille per il vostro ordine. Abbiamo ricevuto le seguenti informazioni"
        ]
    ],
    "common" => [
        "hello" => "Ciao :firstname",
        "order_details" => "Dettagli dell'ordine",
        "view_order_in_backend" => "Visualizza l'ordine nel backend del negozio",
        "view_order_status_online" => "Visualizza lo stato dell'ordine online"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "CONFERMA IL TUO INDIRIZZO E-MAIL",
                "visit_store" => "Visita il nostro negozio"
            ],
            "confirm_mail" => "Benvenuto nel nostro negozio! Per favore, clicca sul pulsante qui sotto per confermare il tuo indirizzo e-mail.",
            "message" => "Benvenuto nel nostro negozio! Puoi accedere utilizzando il tuo indirizzo e-mail **:email** e iniziare a fare acquisti immediatamente.",
            "possibilities" => "Il tuo account utente ti permette di tenere traccia degli ordini aperti e passati.",
            "subject" => "Benvenuto nel nostro negozio, :firstname"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Indirizzo Di Fatturazione",
            "billing_and_shipping" => "Indirizzo di fatturazione e spedizione",
            "currently_pending" => "Il pagamento di questo ordine è attualmente in sospeso.",
            "has_been_paid_on" => "L'ordine è stato pagato il",
            "shipping_address" => "Indirizzo di spedizione",
            "track_shipping_status" => "Può seguire lo stato della spedizione del suo ordine con le seguenti informazioni:"
        ],
        "shipped" => [
            "message" => "Il tuo ordine **#:number** è stato spedito.",
            "subject" => "il tuo ordine è stato spedito"
        ],
        "state_changed" => [
            "message" => "Volevamo solo farti sapere che il tuo ordine **#:number** è stato aggiornato al nuovo stato: **:state**",
            "subject" => "Lo stato del tuo ordine è cambiato"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Volevamo solo farti sapere che il pagamento per l'ordine **#:number** non è riuscito",
            "payment_advice" => "Per favore, accedi al tuo account e riprova a pagare l'ordine.",
            "subject" => "Il pagamento del suo ordine non è riuscito",
            "support" => "Se continuate ad avere problemi con i pagamenti, contattateci."
        ],
        "paid" => [
            "message" => "Abbiamo appena ricevuto un pagamento per il tuo ordine **#:number**.",
            "process_order" => "Ora cominceremo ad elaborare ulteriormente l'ordine.",
            "subject" => "Grazie per il vostro pagamento"
        ],
        "refunded" => [
            "duration" => "Si prega di essere consapevoli del fatto che potrebbero essere necessari diversi giorni prima di ricevere i vostri fondi.",
            "message" => "Abbiamo appena rimborsato il pagamento per il tuo ordine **#:number**.",
            "subject" => "Il tuo pagamento è stato rimborsato"
        ]
    ]
];

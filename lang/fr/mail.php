<?php
return [
    "admin" => [
        "checkout_failed" => [
            "error" => "Message d'erreur",
            "error_details" => "Détails de l'erreur",
            "not_processed" => "La commande suivante n'a pas pu être traitée correctement. Il est possible que vous deviez contacter le client.",
            "payment_id" => "ID de paiement",
            "subject" => "Échec de la commande #: numéro"
        ],
        "checkout_succeeded" => [
            "order_placed" => "La commande suivante a été placée dans votre magasin:",
            "subject" => "Nouvelle commande n °: numéro"
        ]
    ],
    "checkout" => [
        "failed" => [
            "check_order_status" => "Pour vérifier l'état de votre commande, vous pouvez vous connecter à notre magasin à tout moment.",
            "error" => "Message d'erreur",
            "payment_id" => "ID de paiement",
            "problem_message" => "Nous sommes désolés qu'il y ait eu un problème lors de votre commande. Nous étudierons le problème et vous contacterons pour plus d'informations.",
            "subject" => "Erreur de paiement pour la commande n °: numéro"
        ],
        "succeeded" => [
            "check_order_status" => "Vous pouvez vérifier l'état de votre commande en visitant la section compte de notre boutique.",
            "subject" => "Confirmation de la commande n °: numéro",
            "thanks_for_order" => "Merci beaucoup pour votre commande. Nous avons reçu les informations suivantes"
        ]
    ],
    "common" => [
        "hello" => "Bonjour: prénom",
        "order_details" => "Détails de la commande",
        "view_order_in_backend" => "Afficher la commande en magasin",
        "view_order_status_online" => "Afficher l'état de la commande en ligne"
    ],
    "customer" => [
        "created" => [
            "button" => [
                "confirm" => "Confirmez votre adresse email",
                "visit_store" => "Visitez notre magasin"
            ],
            "confirm_mail" => "Bienvenue dans notre magasin! Veuillez cliquer sur le bouton ci-dessous pour confirmer votre adresse e-mail.",
            "message" => "Bienvenue dans notre magasin! Vous pouvez vous connecter en utilisant votre adresse e-mail **: e-mail ** et commencer à magasiner immédiatement.",
            "possibilities" => "Votre compte utilisateur vous permet de suivre les commandes en cours et passées.",
            "subject" => "Bienvenue dans notre magasin,: prénom"
        ]
    ],
    "order" => [
        "partials" => [
            "billing_address" => "Adresse de facturation",
            "billing_and_shipping" => "Adresse de facturation et d'expédition",
            "currently_pending" => "Le paiement de cette commande est actuellement en attente.",
            "has_been_paid_on" => "La commande a été payée le",
            "shipping_address" => "Adresse de livraison",
            "track_shipping_status" => "Vous pouvez suivre l'état d'expédition de votre commande avec les informations suivantes:"
        ],
        "shipped" => [
            "message" => "Votre commande ** #: numéro ** a été expédiée.",
            "subject" => "Votre commande a été expédiée"
        ],
        "state_changed" => [
            "message" => "Nous voulions simplement vous informer que votre commande ** #: numéro ** a été mise à jour avec le nouveau statut: **: état **",
            "subject" => "Le statut de votre commande a changé"
        ]
    ],
    "payment" => [
        "failed" => [
            "message" => "Nous voulions simplement vous informer que le paiement de la commande ** #: numéro ** a échoué",
            "payment_advice" => "Veuillez vous connecter à votre compte et réessayer de payer la commande.",
            "subject" => "Le paiement de votre commande a échoué",
            "support" => "Si vous continuez à rencontrer des problèmes de paiement, veuillez nous contacter."
        ],
        "paid" => [
            "message" => "Nous venons de recevoir un paiement pour votre commande ** #: numéro **.",
            "process_order" => "Nous allons maintenant commencer à traiter la commande.",
            "subject" => "Merci pour votre paiement"
        ],
        "refunded" => [
            "duration" => "Veuillez noter que cela peut prendre plusieurs jours avant de recevoir vos fonds.",
            "message" => "Nous venons de rembourser le paiement de votre commande ** #: numéro **.",
            "subject" => "Votre paiement a été remboursé"
        ]
    ]
];

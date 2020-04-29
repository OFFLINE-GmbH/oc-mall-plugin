<?php return [
    'common' => [
        'hello' => 'Bonjour :firstname',
        'view_order_status_online' => 'Consulter l\'état de la commande en ligne',
        'view_order_in_backend' => 'Voir la commande dans le backend du boutique',
        'order_details' => 'Détails de la commande',
    ],
    'payment' => [
        'refunded' => [
            'subject' => 'Votre paiement a été remboursé',
            'message' => 'Nous venons de rembourser le paiement de votre commande **n°:number**.',
            'duration' => 'Sachez qu\'il peut s\'écouler plusieurs jours avant que vous ne receviez vos fonds.',
        ],
        'paid' => [
            'subject' => 'Merci pour votre paiement',
            'message' => 'Nous venons de recevoir un paiement pour votre commande **n°:number**.',
            'process_order' => 'Nous allons maintenant commencer à poursuivre le traitement de la commande.',
        ],
        'failed' => [
            'subject' => 'Le paiement de votre commande a échoué',
            'message' => 'Nous voulions juste vous faire savoir que le paiement de la commande **n°:number** a échoué',
            'payment_advice' => 'Veuillez vous connecter à votre compte et essayer à nouveau de payer la commande.',
            'support' => 'Si vous continuez à rencontrer des problèmes de paiement, veuillez nous contacter.',
        ],
    ],
    'order' => [
        'partials' => [
            'billing_address' => 'Adresse de facturation',
            'billing_and_shipping' => 'Adresse de facturation et de livraison',
            'shipping_address' => 'Adresse de livraison',
            'has_been_paid_on' => 'La commande a été payée le',
            'currently_pending' => 'Le paiement de cette commande est actuellement en cours.',
            'track_shipping_status' => 'Vous pouvez suivre l\'état d\'expédition de votre commande grâce aux informations suivantes:',
        ],
        'state_changed' => [
            'subject' => 'Le statut de votre commande a changé',
            'message' => 'Nous voulions juste vous faire savoir que votre commande **#:numéro** a été mise à jour avec le nouveau statut: **:state**',
        ],
        'shipped' => [
            'subject' => 'Votre commande a été expédiée',
            'message' => 'Votre commande **n°:number** a été expédiée.',
        ],
    ],
    'customer' => [
        'created' => [
            'subject' => 'Bienvenue sur notre boutique, :firstname',
            'confirm_mail' => 'Bienvenue sur notre boutique ! Veuillez cliquer sur le bouton ci-dessous pour confirmer votre adresse électronique.',
            'message' => 'Bienvenue sur notre boutique ! Vous pouvez vous connecter en utilisant votre adresse électronique **:email** et commencer vos achats immédiatement.',
            'possibilities' => 'Votre compte d\'utilisateur vous permet de suivre les commandes en cours et passées.',
            'button' => [
                'confirm' => 'Confirmez votre adresse électronique',
                'visit_store' => 'Visitez notre boutique',
            ],
        ],
    ],
    'checkout' => [
        'succeeded' => [
            'subject' => 'Confirmation de la commande n°:number',
            'thanks_for_order' => 'Merci beaucoup pour votre commande. Nous avons reçu les informations suivantes',
            'check_order_status' => 'Vous pouvez vérifier l\'état de votre commande en visitant la section compte de notre boutique.',
        ],
        'failed' => [
            'subject' => 'Erreur de paiement pour la commande #:number',
            'problem_message' => 'Nous sommes vraiment désolés qu\'il y ait eu un problème lors de votre passage à la caisse. Nous examinerons le problème et vous contacterons pour de plus amples informations.',
            'check_order_status' => 'Pour vérifier l\'état de votre commande, vous pouvez vous connecter à notre boutique à tout moment.',
            'payment_id' => 'Identification du paiement',
            'error' => 'Message d\'erreur',
        ],
    ],
    'admin' => [
        'checkout_succeeded' => [
            'subject' => 'Nouvelle commande #:number',
            'order_placed' => 'La commande suivante a été passée dans votre boutique:',
        ],
        'checkout_failed' => [
            'subject' => 'Échec au paiement #:number',
            'not_processed' => 'La commande suivante n\'a pas pu être traitée correctement. Il est possible que vous deviez contacter le client.',
            'error_details' => 'Détails de l\'erreur',
        ],
    ],
];

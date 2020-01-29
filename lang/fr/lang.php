<?php
return [
    "category" => [
        "code" => "Code",
        "code_comment" => "Ce code peut être utilisé pour identifier cette catégorie dans vos partials frontend.",
        "inherit_property_groups" => "Hériter des propriétés de la catégorie parente",
        "inherit_property_groups_comment" => "Utiliser les groupes de propriétés de la catégorie parente de cette catégorie",
        "inherit_review_categories" => "",
        "inherit_review_categories_comment" => "",
        "name" => "Nom",
        "no_parent" => "Aucun parent",
        "parent" => "Parent"
    ],
    "common" => [
        "accessories" => "Accessoires",
        "accessory" => "Champs personnalisés",
        "action_required" => "Action Requise!",
        "add_value" => "Ajouter valeur",
        "address" => "Adresse",
        "addresses" => "Adresses",
        "allowed" => "Permis",
        "api_error" => "Impossible d'enregistrer la réduction. Erreur lors de l'envoi des modifications à l'API MALL.",
        "approved_at" => "",
        "attachments" => "Images/Téléchargements",
        "brand" => "Marque",
        "brands" => "Marques",
        "cart" => "Panier",
        "catalogue" => "Catalogue",
        "categories" => "Catégories",
        "category" => "Catégorie",
        "caution" => "Mise en garde",
        "checked" => "Coché",
        "code" => "Code",
        "code_comment" => "Ce code peut être utilisé pour identifier cet enregistrement par programme",
        "color" => "Couleur",
        "conditions" => "Conditions",
        "created_at" => "Créé le",
        "custom_fields" => "",
        "customer_group" => "Groupe client",
        "customer_groups" => "Groupes client",
        "customers" => "Clients",
        "data" => "Data",
        "deleted_at" => "Supprimé le",
        "discount" => "Remise",
        "discount_percentage" => "Remise (%)",
        "discounts" => "Remises",
        "display_name" => "Afficher Nom",
        "dont_group" => "-- Do not group",
        "downloads" => "Téléchargements",
        "export_orders" => "Export commandes",
        "failed" => "Échoué",
        "feeds" => "",
        "fees" => "Frais",
        "general" => "Generale",
        "group_name" => "Nom de groupe",
        "hide_published" => "Cache publié",
        "id" => "ID",
        "image" => "Image",
        "image_set" => "Image set",
        "images" => "Images",
        "includes_tax" => "Taxes incluses",
        "invalid_quantity" => "La quantité spécifiée n'est pas valide",
        "inventory" => "Inventaire",
        "logo" => "Logo",
        "main_image" => "Image principale",
        "message" => "Message",
        "meta_description" => "Meta description",
        "meta_keywords" => "Meta keywords",
        "meta_title" => "Meta title",
        "name" => "Nom",
        "no" => "Non",
        "none" => "-- Aucun",
        "not_allowed" => "Interdit",
        "not_in_use" => "Option non utilisée",
        "notification" => "Notification",
        "notifications" => "Notifications",
        "old_price" => "Ancien prix",
        "option" => "Option",
        "options" => "Options",
        "order_states" => "Etat de commande",
        "orders" => "Commandes",
        "out_of_stock" => "Ce produit est en rupture de stock.",
        "out_of_stock_short" => "Rupture de stock",
        "payment" => "Paiement",
        "payment_gateway" => "Passerelle de paiement",
        "payment_method" => "Mode de paiement",
        "payment_methods" => "Méthodes de payement",
        "payment_provider" => "Fournisseur de paiement",
        "payments" => "Paiements",
        "price_missing" => "Entrez au moins un prix pour la devise par défaut",
        "product" => "Produit",
        "product_or_variant" => "Produit/Variante",
        "products" => "Produits",
        "properties" => "Propriétés",
        "properties_links" => "Propriétés/Liens",
        "property" => "Propriété",
        "property_group" => "Propriété groupe",
        "property_groups" => "Propriété groupes",
        "rates" => "Taux",
        "reference" => "Référence",
        "reorder" => "Réorganiser les entrées",
        "review" => "",
        "review_categories" => "",
        "review_category" => "",
        "reviews" => "",
        "saved_changes" => "Changements sauvegardés avec succès",
        "select_file" => "Choisir Fichier",
        "select_image" => "Choisir image",
        "select_placeholder" => "-- Choisissez s'il vous plaît",
        "seo" => "SEO",
        "service" => "",
        "services" => "",
        "session_id" => "Session ID",
        "shipping" => "Livraison",
        "shipping_methods" => "Méthodes de livraison",
        "shop" => "Boutique",
        "since_begin" => "Depuis début",
        "slug" => "URL",
        "slug_unique" => "L'URL doit être unique",
        "sort_order" => "Ordre de tri",
        "sorting_updated" => "L'ordre de tri a été mis à jour",
        "stock_limit_reached" => "Vous ne pouvez plus ajouter d'articles de ce produit à votre panier puisque la limite de stock est atteinte.",
        "successful" => "Réussi",
        "taxes" => "Taxes",
        "title" => "",
        "unchecked" => "Décoché",
        "unit" => "Unité",
        "updated_at" => "Mis à jour le",
        "use_backend_defaults" => "Utiliser les valeurs par défaut configurées dans les paramètres du backend",
        "value" => "Valeur",
        "variant" => "Variante",
        "variants" => "Variantes",
        "version" => "",
        "website" => "Website",
        "weekly" => "Hebdomadaire",
        "yes" => "Oui"
    ],
    "components" => [
        "addressForm" => [
            "details" => [
                "description" => "Affiche un formulaire pour modifier l'adresse d'un utilisateur",
                "name" => "Formulaire d'adresse"
            ],
            "properties" => [
                "address" => ["title" => "Adresse"],
                "redirect" => ["title" => "Redirection (après sauvegarde)"],
                "set" => ["title" => "Utilisez cette adresse comme"]
            ],
            "redirects" => ["checkout" => "Page de paiement"],
            "set" => ["billing" => "Adresse de facturation", "shipping" => "Adresse de livraison"]
        ],
        "addressList" => [
            "details" => [
                "description" => "Affiche une liste de toutes les adresses d'utilisateurs enregistrés",
                "name" => "Liste d'adresses"
            ],
            "errors" => [
                "address_not_found" => "L'adresse demandée est introuvable",
                "cannot_delete_last_address" => "Vous ne pouvez pas supprimer votre dernière adresse"
            ],
            "messages" => ["address_deleted" => "Adresse supprimée"]
        ],
        "addressSelector" => [
            "details" => [
                "description" => "Affiche une liste de toutes les adresses d'utilisateurs existantes",
                "name" => "Sélecteur d'adresse"
            ]
        ],
        "cart" => [
            "details" => ["description" => "Affiche le panier", "name" => "Panier"],
            "properties" => [
                "showDiscountApplier" => ["title" => "Afficher l'applicateur de remise"],
                "showProceedToCheckoutButton" => ["title" => ""],
                "showShipping" => ["title" => ""],
                "showTaxes" => ["title" => "Voir taxes"]
            ]
        ],
        "cartSummary" => [
            "details" => [
                "description" => "Affiche le nombre de produits dans et la valeur totale du panier",
                "name" => "Résumé du panier"
            ],
            "properties" => [
                "showItemCount" => [
                    "description" => "Affiche le nombre d'articles dans le panier",
                    "title" => "Afficher le nombre de produits"
                ],
                "showTotalPrice" => [
                    "description" => "Affiche la valeur totale de tous les articles du panier.",
                    "title" => "Afficher la valeur totale"
                ]
            ]
        ],
        "categories" => [
            "by_slug" => "Utilisez la catégorie dans l'URL en tant que parent",
            "details" => ["description" => "Liste des catégories disponibles", "name" => "Catégories"],
            "no_parent" => "Afficher toutes les catégories",
            "properties" => [
                "categoryPage" => [
                    "description" => "Des liens indiqueront cette page. Si rien n'est entré, les paramètres par défaut des paramètres du backend seront utilisés.",
                    "title" => "Page de catégorie"
                ],
                "categorySlug" => [
                    "description" => "Utilisez ce paramètre pour charger la catégorie parente à partir de l'URL",
                    "title" => "Paramètre de slug de catégorie"
                ],
                "parent" => [
                    "description" => "Afficher uniquement les catégories enfants de cette catégorie",
                    "title" => "Catégorie de départ"
                ]
            ]
        ],
        "category" => [
            "by_slug" => "Utilisez la catégorie dans l'URL en tant que parent",
            "no_parent" => "Afficher toutes les catégories",
            "properties" => [
                "categoryPage" => [
                    "description" => "Des liens pointeront vers cette page. Si rien n'est spécifié, les paramètres par défaut des paramètres du backend seront utilisés.",
                    "title" => "Page de catégorie"
                ],
                "categorySlug" => [
                    "description" => "Utilisez ce paramètre pour charger la catégorie parente à partir de l'URL.",
                    "title" => "Paramètre de slug de catégorie"
                ],
                "parent" => [
                    "description" => "Afficher uniquement les catégories enfants de cette catégorie",
                    "title" => "Catégorie de départ"
                ]
            ]
        ],
        "checkout" => [
            "details" => ["description" => "Gère le processus de paiement", "name" => "Paiement"],
            "errors" => [
                "missing_settings" => "Veuillez sélectionner un mode de paiement et d'expédition."
            ]
        ],
        "currencyPicker" => [
            "details" => [
                "description" => "Affiche un sélecteur pour sélectionner la devise de la boutique actuellement active",
                "name" => "Sélecteur de devise"
            ]
        ],
        "customerDashboard" => [
            "details" => [
                "description" => "Affiche un lien permettant au client de se connecter et de modifier les paramètres de son compte",
                "name" => "Tableau de bord client"
            ],
            "properties" => [
                "customerDashboardLabel" => [
                    "description" => "Texte du lien pour la page du compte client",
                    "title" => "Etiquette du tableau de bord client"
                ],
                "logoutLabel" => [
                    "description" => "Texte du lien pour le lien de déconnexion",
                    "title" => "Étiquette de déconnexion"
                ]
            ]
        ],
        "customerProfile" => [
            "details" => [
                "description" => "Affiche un formulaire de modification de profil client.",
                "name" => "Profil client"
            ]
        ],
        "dependencies" => [
            "details" => [
                "description" => "Inclut toutes les dépendances nécessaires",
                "name" => "Dépendances Frontend"
            ]
        ],
        "discountApplier" => [
            "details" => [
                "description" => "Affiche un champ de saisie du code promo",
                "name" => "Saisie du code promo"
            ],
            "discount_applied" => "Remise appliquée avec succès!"
        ],
        "enhancedEcommerceAnalytics" => ["details" => ["description" => "", "name" => ""]],
        "myAccount" => [
            "details" => [
                "description" => "Affiche différents formulaires où un utilisateur peut voir et éditer son profil",
                "name" => "Compte utilisateur"
            ],
            "pages" => ["addresses" => "Adresses", "orders" => "Ordres", "profile" => "Profil"],
            "properties" => ["page" => ["title" => "Sous-page active"]]
        ],
        "ordersList" => [
            "details" => [
                "description" => "Affiche une liste de toutes les commandes client",
                "name" => "Liste des commandes"
            ]
        ],
        "paymentMethodSelector" => [
            "details" => [
                "description" => "Affiche une liste de tous les modes de paiement disponibles",
                "name" => "Sélecteur de méthode de paiement"
            ],
            "errors" => [
                "unavailable" => "Le mode de paiement sélectionné n'est pas disponible pour votre commande."
            ]
        ],
        "product" => [
            "added_to_cart" => "Produit ajouté avec succès",
            "details" => [
                "description" => "Affiche les détails d'un produit",
                "name" => "Détails du produit"
            ],
            "properties" => ["redirectOnPropertyChange" => ["description" => "", "title" => ""]]
        ],
        "productReviews" => [
            "details" => ["description" => "", "name" => ""],
            "properties" => [
                "currentVariantReviewsOnly" => ["description" => "", "title" => ""],
                "perPage" => ["title" => ""]
            ]
        ],
        "products" => [
            "details" => ["description" => "Affiche une liste de produits", "name" => "Produits"],
            "properties" => [
                "filter" => [
                    "description" => "Filtre forcé pour ce composant",
                    "title" => "Filtrer la chaîne (string)"
                ],
                "filter_component" => [
                    "description" => "Alias du composant ProductsFilter qui filtre ce composant Products",
                    "title" => "Filtre alias de composant"
                ],
                "include_children" => [
                    "description" => "Afficher tous les produits des catégories enfants également",
                    "title" => "Inclure les enfants"
                ],
                "include_variants" => [
                    "description" => "Ne pas montrer les produits simples mais toutes les variantes de produits disponibles",
                    "title" => "Afficher les variantes d'article"
                ],
                "no_category_filter" => "Ne pas filtrer par catégorie",
                "paginate" => [
                    "description" => "Paginer le résultat (afficher plus d'une page)",
                    "title" => "Pagination"
                ],
                "per_page" => ["description" => "Combien de produits à afficher par page", "title" => "Par page"],
                "set_page_title" => [
                    "description" => "Utiliser le nom de la catégorie comme titre de la page",
                    "title" => "Définir le titre de la page"
                ],
                "sort" => [
                    "description" => "Ceci remplace la préférence de tri de l'utilisateur",
                    "title" => "Trier"
                ],
                "use_url" => "Utiliser le slug de catégorie depuis l'URL"
            ]
        ],
        "productsFilter" => [
            "details" => [
                "description" => "Filtre les produits d'une catégorie",
                "name" => "Filtre de produits"
            ],
            "properties" => [
                "includeChildren" => [
                    "description" => "Inclure également les propriétés et les filtres des produits dans les catégories enfants",
                    "title" => "Inclure les enfants"
                ],
                "includeSliderAssets" => [
                    "description" => "Inclure toutes les dépendances de noUI Slider via cdnjs",
                    "title" => "Inclure noUI Slider"
                ],
                "includeVariants" => [
                    "description" => "Afficher les filtres pour les propriétés de variantes",
                    "title" => "Inclure les variantes"
                ],
                "showBrandFilter" => ["title" => "Afficher le filtre de la marque"],
                "showOnSaleFilter" => ["title" => "Afficher le filtre en vente"],
                "showPriceFilter" => ["title" => "Afficher le filtre de prix"],
                "sortOrder" => ["description" => "Ordre de tri initial", "title" => "Ordre de tri"]
            ],
            "sortOrder" => [
                "bestseller" => "Best-seller",
                "latest" => "Le plus récent",
                "manual" => "Manuel",
                "name" => "",
                "oldest" => "Le plus ancien",
                "priceHigh" => "Prix le plus élevé",
                "priceLow" => "Prix le plus bas",
                "random" => "Au hasard",
                "ratings" => ""
            ]
        ],
        "shippingMethodSelector" => [
            "details" => [
                "description" => "Affiche une liste de toutes les méthodes d'expédition disponibles",
                "name" => "Sélecteur d'expédition"
            ],
            "errors" => [
                "unavailable" => "La méthode d'expédition sélectionnée n'est pas disponible pour votre commande."
            ]
        ],
        "signup" => [
            "details" => [
                "description" => "Affiche un formulaire d'inscription et de connexion",
                "name" => "S'inscrire"
            ],
            "errors" => [
                "city" => ["required" => "Veuillez entrez votre a ville."],
                "country_id" => [
                    "exists" => "Le pays fourni n'est pas valide.",
                    "required" => "Choisissez un pays."
                ],
                "email" => [
                    "email" => "Cette adresse email est invalide.",
                    "non_existing_user" => "Un utilisateur avec cette adresse e-mail est déjà enregistré. Utiliser la fonction de réinitialisation du mot de passe.",
                    "required" => "Veuillez entrer une adresse courriel.",
                    "unique" => "Un utilisateur avec cette adresse email est déjà enregistré."
                ],
                "firstname" => ["required" => "Veuillez entrer votre nom de famille."],
                "lastname" => ["required" => "Veuillez entrez votre prénom s'il vous plait."],
                "lines" => ["required" => "Veuillez entrez votre adresse."],
                "login" => [
                    "between" => "S'il vous plaît, mettez une adresse email valide.",
                    "email" => "S'il vous plaît, mettez une adresse email valide.",
                    "required" => "Veuillez entrer une adresse courriel."
                ],
                "not_activated" => "Votre compte doit être activé avant de pouvoir vous connecter.",
                "password" => [
                    "max" => "Le mot de passe fourni est trop long.",
                    "min" => "Le mot de passe fourni est trop court. Veuillez entrer au moins 8 caractères.",
                    "required" => "S'il vous plait entrez votre mot de passe."
                ],
                "password_repeat" => [
                    "required" => "Veuillez répéter votre mot de passe.",
                    "same" => "Votre confirmation de mot de passe ne correspond pas à votre mot de passe entré."
                ],
                "state_id" => [
                    "exists" => "La valeur sélectionnée n'est pas valide.",
                    "required" => "Choisissez un état"
                ],
                "terms_accepted" => ["required" => ""],
                "unknown_user" => "Les informations d'identification que vous avez entrées ne sont pas valides.",
                "user_is_guest" => "Vous essayez de vous connecter avec un compte invité.",
                "zip" => ["required" => "Veuillez entrez votre code postal."]
            ],
            "properties" => ["redirect" => ["name" => "Rediriger après la connexion"]]
        ],
        "wishlistButton" => [
            "details" => ["description" => "", "name" => ""],
            "properties" => [
                "product" => ["description" => "", "name" => ""],
                "variant" => ["description" => "", "name" => ""]
            ]
        ],
        "wishlists" => [
            "details" => ["description" => "", "name" => ""],
            "properties" => ["showShipping" => ["description" => "", "name" => ""]]
        ]
    ],
    "currency_settings" => [
        "currencies" => "Entrez uniquement les codes de devise officiels à 3 caractères.",
        "currency_code" => "Code de devise",
        "currency_decimals" => "Num. décimales",
        "currency_format" => "Format",
        "currency_rate" => "Taux",
        "currency_symbol" => "Symbole",
        "description" => "Configurez vos devises",
        "is_default" => "Par défaut",
        "label" => "Monnaies",
        "unknown" => ""
    ],
    "custom_field_options" => [
        "add" => "Ajouter option",
        "attributes" => "Attributs",
        "checkbox" => "Checkbox",
        "color" => "Couleur",
        "dropdown" => "Dropdown",
        "float" => "Float",
        "image" => "Image",
        "integer" => "Integer",
        "name" => "Nom",
        "option" => "Option",
        "price" => "Prix",
        "richeditor" => "Richtext",
        "text" => "Textfield",
        "textarea" => "Multi-line textfield"
    ],
    "custom_fields" => [
        "is_not_required" => "N'est pas requis",
        "is_required" => "Est requis",
        "name" => "Champ Nom",
        "options" => "Options",
        "required" => "Requis",
        "required_comment" => "Ce champ est obligatoire pour passer une commande",
        "type" => "Champ type"
    ],
    "customer_group" => [
        "code_comment" => "Ce code peut être utilisé pour identifier ce groupe par programmation",
        "discount_comment" => "Donnez à ce groupe de clients une remise spécifique en% sur tout votre catalogue"
    ],
    "discounts" => [
        "amount" => "Montant fixé",
        "code" => "Code de réduction",
        "code_comment" => "",
        "expires" => "Expire",
        "max_number_of_usages" => "Nombre maximum d'utilisations",
        "name" => "Nom",
        "number_of_usages" => "Nombre d'utilisations",
        "rate" => "Taux (%)",
        "section_trigger" => "Quand cette réduction est-elle applicable ?",
        "section_type" => "Que fait cette réduction ?",
        "shipping_description" => "Nom du mode de livraison alternatif",
        "shipping_guaranteed_days_to_delivery" => "Jours garantis de livraison",
        "shipping_price" => "Prix du mode de livraison alternatif",
        "total_to_reach" => "Total de commande minimal pour que la remise soit valide",
        "trigger" => "Valide si",
        "triggers" => [
            "code" => "Le code de réduction est entré",
            "product" => "Un produit spécifique est présent dans le panier",
            "total" => "Le total de la commande est atteint"
        ],
        "type" => "Type de remise",
        "types" => [
            "fixed_amount" => "Montant fixé",
            "rate" => "Taux",
            "shipping" => "Envoi alternatif"
        ],
        "valid_from" => "",
        "validation" => [
            "duplicate" => "Vous ne pouvez utiliser le même code promotionnel qu'une seule fois.",
            "empty" => "Entrez un code promo.",
            "expired" => "Ce code promo a expiré.",
            "not_found" => "Ce code promo n'est pas valide.",
            "shipping" => "Vous ne pouvez appliquer qu'un seul code promotionnel qui réduit vos frais d'expédition.",
            "usage_limit_reached" => "Ce code promotionnel a été appliqué plusieurs fois et n’est donc plus valide."
        ]
    ],
    "feed_settings" => [
        "description" => "",
        "google_merchant_enabled" => "",
        "google_merchant_enabled_comment" => "",
        "google_merchant_url" => "",
        "google_merchant_url_locale" => ""
    ],
    "general_settings" => [
        "account_page" => "Page de compte",
        "account_page_comment" => "Le composant myAccount doit être présent sur cette page",
        "address_page" => "Page adresse",
        "address_page_comment" => "Le composant de formulaire d'adresse doit être présent sur cette page",
        "admin_email" => "Email de l'administrateur",
        "admin_email_comment" => "Les notifications de l'administrateur seront envoyées à cette adresse",
        "base" => "Paramètres de base",
        "cart_page" => "Page panier",
        "cart_page_comment" => "Le composant cart doit être présent sur cette page",
        "category" => "Mall: Générale",
        "category_orders" => "Mall: Commandes",
        "category_page" => "Page de catégorie pour la liste des produits",
        "category_page_comment" => "Ajouter le composant \"produits\" à cette page.",
        "category_payments" => "Mall: Paiements",
        "checkout_page" => "Page de paiement",
        "checkout_page_comment" => "Le composant de paiement doit être présent sur cette page",
        "click_and_collect" => [
            "percent" => "",
            "percent_comment" => "",
            "use_state" => "",
            "use_state_comment" => ""
        ],
        "customizations" => "Personnalisations",
        "customizations_comment" => "Personnaliser les fonctionnalités de votre boutique",
        "description" => "Réglages généraux",
        "group_search_results_by_product" => "",
        "group_search_results_by_product_comment" => "",
        "index_driver" => "Index driver",
        "index_driver_comment" => "Si votre base de données prend en charge JSON, utilisez le pilote de base de données.",
        "index_driver_database" => "Database (seulement pour MySQL 5.7+ or MariaDB 10.2+)",
        "index_driver_filesystem" => "Filesystem",
        "index_driver_hint" => "Si vous changez cette option, assurez-vous de lancer \"php artisan mall: reindex\" sur la ligne de commande pour réindexer vos produits.!",
        "label" => "Configuration",
        "links" => "CMS pages",
        "links_comment" => "Choisissez les pages utilisées pour afficher vos produits",
        "order_number_start" => "Numéro de première commande",
        "order_number_start_comment" => "Identifiant initial de la première commande",
        "product_page" => "Fiche produit",
        "product_page_comment" => "C'est ici que les détails du produit sont affichés",
        "redirect_to_cart" => "Rediriger vers le panier",
        "redirect_to_cart_comment" => "Rediriger vers le panier après que l'utilisateur a ajouté un produit",
        "use_state" => "",
        "use_state_comment" => ""
    ],
    "image_sets" => [
        "create_new" => "Créer un nouvel ensemble",
        "is_main_set" => "Est le principal",
        "is_main_set_comment" => "Est principal"
    ],
    "menu_items" => [
        "all_categories" => "Toutes les catégories",
        "all_products" => "Tous les produits",
        "all_variants" => "Toutes les variantes",
        "single_category" => "Catégorie"
    ],
    "notification_settings" => [
        "description" => "Configurer les notifications du magasin",
        "label" => "Notifications"
    ],
    "notifications" => [
        "enabled" => "Activée",
        "enabled_comment" => "Cette notification est activée",
        "template" => "Template mail"
    ],
    "order" => [
        "adjusted_amount" => "Montant ajusté",
        "billing_address" => "Adresse de facturation",
        "card_holder_name" => "Titulaire de la carte",
        "card_type" => "Type de Panier",
        "change_order_status" => "Changer le statut de la commande",
        "change_payment_status" => "Changer le statut de paiement",
        "completion_date" => "Completé le",
        "creation_date" => "Créé le",
        "credit_card" => "Carte de crédit",
        "credit_card_last4_digits" => "4 derniers chiffres",
        "currency" => "Devise",
        "custom_fields" => "Champs personnalisés",
        "customer" => "Client",
        "data" => "Données de commande",
        "delete_confirm" => "Voulez-vous vraiment supprimer cette commande?",
        "deleted" => "Commande supprimée avec succès",
        "deleting" => "Supprimer une commande...",
        "download_invoice" => "",
        "email" => "Email",
        "grand_total" => "TOTAL",
        "invalid_status" => "Le statut sélectionné n'existe pas.",
        "invoice_number" => "# Facture",
        "items" => "Articles",
        "items_total" => "Total des articles",
        "lang" => "Language",
        "modal" => ["cancel" => "Annuler", "update" => "Mettre à jour information"],
        "modification_date" => "Modifié le",
        "not_shipped" => "En cours",
        "notes" => "Notes",
        "order_number" => "# Commande",
        "payment_gateway_used" => "Mode de paiement",
        "payment_hash" => "Paiement hash",
        "payment_method" => "Mode de paiement",
        "payment_states" => [
            "failed_state" => "Paiement échoué",
            "paid_state" => "Payé",
            "pending_state" => "Paiement en attente",
            "refunded_state" => "Paiement remboursé"
        ],
        "payment_status" => "Status du paiement",
        "payment_transaction_id" => "ID de la transaction de paiement",
        "quantity" => "Quantité",
        "rebate_amount" => "Montant du rabais",
        "refunds_amount" => "Montant des remboursements",
        "shipped" => "Expédié",
        "shipping_address" => "Adresse de livraison",
        "shipping_address_is_same_as_billing" => "L'adresse de livraison est la même que l'adresse de facturation",
        "shipping_address_same_as_billing" => "Adresse de livraison identique à celle de facturation",
        "shipping_enabled" => "Expédition activée",
        "shipping_fees" => "Frais de port",
        "shipping_method" => "Méthode d'envoi",
        "shipping_pending" => "En attente de livraison",
        "shipping_provider" => "Fournisseur d'expédition",
        "status" => "Statut",
        "subtotal" => "Sous total",
        "tax_provider" => "Taxe Fournisseur",
        "taxable_total" => "Total taxable",
        "taxes_total" => "Taxes total",
        "total" => "Total",
        "total_rebate_rate" => "Remise totale",
        "total_revenue" => "Revenu total",
        "total_weight" => "Poids total",
        "tracking_completed" => "Marquer la commande comme terminée",
        "tracking_completed_comment" => "La commande sera marquée comme complète",
        "tracking_notification" => "Envoyer une notification",
        "tracking_notification_comment" => "Une notification contenant les informations de suivi sera envoyée au client.",
        "tracking_number" => "Numéro de suivi",
        "tracking_shipped" => "Marquer la commande comme expédiée",
        "tracking_shipped_comment" => "La commande sera marquée comme expédiée",
        "tracking_url" => "URL de suivi",
        "update_invoice_number" => "Définir le numéro de facture",
        "update_shipping_state" => "Mettre à jour l'état d'expédition",
        "updated" => "Mise à jour de la commande réussie",
        "will_be_paid_later" => "Sera payé plus tard"
    ],
    "order_state_settings" => ["description" => "Configurer les états de commande"],
    "order_states" => [
        "color" => "Couleur",
        "description" => "Description",
        "flag" => "Drapeau spécial",
        "flags" => [
            "cancelled" => "Définir l'état de la commande comme \"annulé\"",
            "complete" => "Définir l'état de la commande comme \"terminé\"",
            "new" => "Définir l'état de la commande comme \"nouveau\""
        ],
        "name" => "Nom"
    ],
    "order_status" => [
        "cancelled" => "Annulé",
        "delivered" => "Livré",
        "disputed" => "Contesté",
        "pending" => "En attendant",
        "processed" => "Traité",
        "shipped" => "Expédié"
    ],
    "payment_gateway_settings" => [
        "description" => "Configurez vos moyens de paiement",
        "label" => "Moyens de paiement",
        "paypal" => [
            "client_id" => "PayPal Client ID",
            "secret" => "PayPal Secret",
            "test_mode" => "Test mode",
            "test_mode_comment" => "Exécutez tous les paiements dans le bac à sable PayPal."
        ],
        "postfinance" => [
            "hashing_method" => "",
            "hashing_method_comment" => "",
            "pspid" => "",
            "sha_in" => "",
            "sha_in_comment" => "",
            "sha_out" => "",
            "sha_out_comment" => "",
            "test_mode" => "",
            "test_mode_comment" => ""
        ],
        "stripe" => [
            "api_key" => "Stripe API Key",
            "api_key_comment" => "Vous pouvez trouver cette clé dans votre tableau de bord Stripe",
            "publishable_key" => "Stripe Publishable Key",
            "publishable_key_comment" => "Vous pouvez trouver cette clé dans votre tableau de bord Stripe"
        ]
    ],
    "payment_log" => [
        "code_comment" => "Ce code a été renvoyé par le fournisseur de paiement",
        "data_comment" => "Ces données ont été renvoyées par le fournisseur de paiement",
        "failed_only" => "Échoué Seulement",
        "message_comment" => "Ce message a été renvoyé par le fournisseur de paiement",
        "order_data_comment" => "Toutes les données de commande pour ce paiement",
        "payment_data" => "Données de paiement"
    ],
    "payment_method" => [
        "fee_label" => "Étiquette des frais",
        "fee_label_comment" => "Ce texte sera affiché au client lors de la commande.",
        "fee_percentage" => "Frais de pourcentage",
        "fee_percentage_comment" => "Le pourcentage du total à ajouter au total de la commande",
        "instructions" => "Instructions de paiement",
        "instructions_comment" => "La syntaxe Twig est prise en charge. Utilisez {{order}} pour accéder aux informations de commande correspondantes, le cas échéant.",
        "pdf_partial" => "",
        "pdf_partial_comment" => "",
        "pdf_partial_none" => "",
        "price" => "Frais fixes",
        "price_comment" => "Le montant à ajouter au total de la commande"
    ],
    "payment_method_settings" => ["description" => "Gérer les moyens de paiement"],
    "payment_status" => [
        "cancelled" => "Annulé",
        "charged_back" => "Rechargé",
        "deferred" => "Différé",
        "expired" => "Expiré",
        "failed" => "Échoué",
        "open" => "En cours",
        "paid" => "Payé",
        "paid_deferred" => "Payé différé",
        "paiddeferred" => "Payé différé",
        "paidout" => "Payé",
        "pending" => "en attendant",
        "refunded" => "Remboursé"
    ],
    "permissions" => [
        "manage_categories" => "Peut gérer des categories",
        "manage_customer_addresses" => "Peut gérer des customer addresses",
        "manage_customer_groups" => "Peut gérer des customer groups",
        "manage_discounts" => "Peut gérer des discounts",
        "manage_feeds" => "",
        "manage_notifications" => "Peut gérer des notifications",
        "manage_order_states" => "Peut gérer des order states",
        "manage_orders" => "Peut gérer des orders",
        "manage_payment_log" => "Peut gérer des payment log",
        "manage_price_categories" => "Peut gérer des price categories",
        "manage_products" => "Peut gérer des produits",
        "manage_properties" => "",
        "manage_reviews" => "",
        "manage_services" => "",
        "manage_shipping_methods" => "Peut gérer des shipping methods",
        "manage_taxes" => "Peut gérer des taxes",
        "manage_wishlists" => "",
        "settings" => [
            "manage_currency" => "",
            "manage_general" => "",
            "manage_payment_gateways" => "",
            "manage_payment_methods" => ""
        ]
    ],
    "plugin" => ["description" => "E-commerce solution pour October CMS", "name" => "Mall"],
    "price_category_settings" => [
        "description" => "Configurer des catégories de prix supplémentaires",
        "label" => "Catégories de prix"
    ],
    "product" => [
        "add_currency" => "Ajouter devise",
        "additional_descriptions" => "Descriptions supplémentaires",
        "additional_properties" => "Propriétés supplémentaires",
        "allow_out_of_stock_purchases" => "Autoriser les achats en rupture de stock",
        "allow_out_of_stock_purchases_comment" => "Ce produit peut être commandé même s'il est en rupture de stock",
        "currency" => "Devise",
        "description" => "Description",
        "description_short" => "Courte description",
        "details" => "Détails",
        "duplicate_currency" => "Vous avez entré plusieurs prix pour la même devise",
        "embed_code" => "",
        "embed_title" => "",
        "embeds" => "",
        "filter_virtual" => "",
        "general" => "Général",
        "group_by_property" => "Attribut pour groupement de variantes",
        "gtin" => "",
        "height" => "Height (mm)",
        "inventory_management_method" => "Méthode de gestion des stocks",
        "is_not_taxable" => "Ne pas utiliser de taxe",
        "is_taxable" => "Utiliser les taxes",
        "is_virtual" => "",
        "is_virtual_comment" => "",
        "length" => "Length (mm)",
        "link_target" => "Cible URL",
        "link_title" => "Titre",
        "links" => "Liens",
        "missing_category" => "Le produit n'a pas de catégorie associée. Veuillez sélectionner une catégorie ci-dessous pour modifier ce produit..",
        "mpn" => "",
        "name" => "Nom produit",
        "not_published" => "Non publié",
        "price" => "Prix",
        "price_includes_tax" => "Le prix inclu les taxes",
        "price_includes_tax_comment" => "Le prix défini comprend toutes les taxes",
        "price_table_modal" => [
            "currency_dropdown" => "Devise: ",
            "label" => "Prix et stock",
            "title" => "Aperçu des prix et des stocks",
            "trigger" => "Modifier les valeurs de stock et de prix"
        ],
        "product_file" => "",
        "product_file_version" => "",
        "product_files" => "",
        "product_files_section_comment" => "",
        "properties" => "Propriétés",
        "property_title" => "Titre",
        "property_value" => "Valeur",
        "published" => "Publié",
        "published_comment" => "Ce produit est visible sur le site",
        "published_short" => "Publ.",
        "quantity_default" => "Defaut quantité",
        "quantity_max" => "Maximum quantité",
        "quantity_min" => "Minimum quantité",
        "shippable" => "Livrable",
        "shippable_comment" => "Ce produit peut être expédié",
        "stackable" => "Stack in cart",
        "stackable_comment" => "Si ce produit est ajouté au panier plusieurs fois, n'afficher qu'une entrée (augmenter la quantité)",
        "stock" => "Stock",
        "taxable" => "Taxable",
        "taxable_comment" => "Calculer les taxes sur ce produit",
        "user_defined_id" => "ID produit",
        "variant_support_header" => "Variantes non prises en charge",
        "variant_support_text" => "La catégorie sélectionnée n'a pas de propriétés de variante définies. Veuillez basculer la méthode de gestion des stocks sur \"Article\" ou sélectionner une autre catégorie..",
        "weight" => "Weight (g)",
        "width" => "Width (mm)"
    ],
    "product_file" => [
        "display_name_comment" => "",
        "download_count" => "",
        "errors" => ["expired" => "", "invalid" => "", "not_found" => "", "too_many_attempts" => ""],
        "expires_after_days" => "",
        "expires_after_days_comment" => "",
        "file" => "",
        "hint" => ["info_link" => "", "info_text" => "", "intro" => ""],
        "max_download_count" => "",
        "max_download_count_comment" => "",
        "session_required" => "",
        "session_required_comment" => "",
        "version_comment" => ""
    ],
    "products" => ["variants_comment" => "Créer différentes variantes du même produit"],
    "properties" => [
        "filter_type" => "Type de filtre",
        "filter_types" => ["none" => "Sans filtre", "range" => "Intervalle", "set" => "Ensemble"],
        "use_for_variants" => "Utiliser pour variantes",
        "use_for_variants_comment" => "Cette propriété est différente pour différentes variantes de ce produit"
    ],
    "review_settings" => [
        "allow_anonymous" => "",
        "allow_anonymous_comment" => "",
        "description" => "",
        "enabled" => "",
        "enabled_comment" => "",
        "moderated" => "",
        "moderated_comment" => ""
    ],
    "reviews" => [
        "anonymous" => "",
        "approve_next" => "",
        "cons" => "",
        "no_more" => "",
        "only_unapproved" => "",
        "pros" => "",
        "rating" => "",
        "review" => "",
        "title" => ""
    ],
    "services" => ["option" => "", "options" => "", "required" => "", "required_comment" => ""],
    "shipping_method" => [
        "available_above_total" => "Disponible si le total est supérieur ou égal à",
        "available_below_total" => "Disponible si le total est inférieur à",
        "countries" => "Disponible pour l'expédition dans ces pays",
        "countries_comment" => "Si aucun pays n'est sélectionné, cette méthode est disponible dans le monde entier.",
        "guaranteed_delivery_days" => "Livraison garantie en jours",
        "not_required_description" => "",
        "not_required_name" => ""
    ],
    "shipping_method_rates" => [
        "from_weight" => "À partir de (poids en grammes)",
        "to_weight" => "Jusqu'À (Poids en grammes)"
    ],
    "shipping_method_settings" => ["description" => "Gérer les méthodes d'expédition"],
    "tax" => [
        "countries" => "Appliquer la taxe uniquement lors de l'expédition dans ces pays",
        "countries_comment" => "Si aucun pays n'est sélectionné, la taxe est appliquée dans le monde entier.",
        "is_default" => "",
        "is_default_comment" => "",
        "percentage" => "Pourcentage"
    ],
    "tax_settings" => ["description" => "Gérer les taxes"],
    "titles" => [
        "brands" => ["create" => "Créer marque", "edit" => "Modifier marque"],
        "categories" => [
            "create" => "Créer catégorie",
            "preview" => "Aperçu catégorie",
            "update" => "Modifier catégorie"
        ],
        "custom_field_options" => ["edit" => "Modifier les options de champ"],
        "customer_groups" => ["create" => "Créer groupe", "update" => "Modifier groupe"],
        "discounts" => [
            "create" => "Créer une réduction",
            "preview" => "Aperçu de la réduction",
            "update" => "Modifier la réduction"
        ],
        "notifications" => ["update" => "Notification de mise à jour"],
        "order_states" => [
            "create" => "Créer statut",
            "edit" => "Modifier statut",
            "reorder" => "Reorder status"
        ],
        "orders" => ["export" => "Exporter commandes", "show" => "Détails commande"],
        "payment_methods" => [
            "create" => "Créer un mode de paiement",
            "edit" => "Modifier le mode de paiement",
            "reorder" => "Réorganiser"
        ],
        "products" => [
            "create" => "Créer produit",
            "preview" => "Aperçu produit",
            "update" => "Modifier produit"
        ],
        "properties" => ["create" => "Créer des propriétés", "edit" => "Editer les propriétés"],
        "property_groups" => ["create" => "Créer groupe", "edit" => "Modifier groupe"],
        "reviews" => ["create" => "", "update" => ""],
        "services" => ["create" => "", "update" => ""],
        "shipping_methods" => [
            "create" => "Créer un mode d'expédition",
            "preview" => "Prévisualiser la méthode d'expédition",
            "update" => "Modifier le mode d'expédition"
        ],
        "taxes" => ["create" => "Créer taxe", "update" => "Modifier taxe"]
    ],
    "variant" => ["method" => ["single" => "Article", "variant" => "Article variantes"]]
];

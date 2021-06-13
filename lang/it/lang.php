<?php
return [
    "category" => [
        "code" => "Codice",
        "code_comment" => "Questo codice può essere usato per identificare questa categoria nei tuoi parziali del frontend.",
        "google_product_category_id" => "ID della categoria di prodotto di Google",
        "google_product_category_id_comment" => "Utilizzato per l'integrazione di Google Merchant, vedi https://support.google.com/merchants/answer/6324436",
        "inherit_property_groups" => "Eredita le proprietà dalla categoria superiore",
        "inherit_property_groups_comment" => "Usa i gruppi di proprietà della categoria superiore",
        "inherit_review_categories" => "Eredita le categorie di revisione della categoria padre",
        "inherit_review_categories_comment" => "Usa le categorie di revisione della categoria madre di questa categoria",
        "name" => "Nome",
        "no_parent" => "Nessuna categoria superiore",
        "parent" => "Sottocategoria di"
    ],
    "common" => [
        "accessories" => "Accessori",
        "accessory" => "Accessorio",
        "action_required" => "Azione necessaria!",
        "add_value" => "Aggiungi valore",
        "address" => "Indirizzo",
        "addresses" => "Indirizzi",
        "allowed" => "Permesso",
        "api_error" => "Impossibile salvare sconto. Errore durante l'accesso alle API di Mall.",
        "approved_at" => "Approvato a",
        "attachments" => "Immagini/Download",
        "brand" => "Brand",
        "brands" => "Brands",
        "cart" => "Carrello",
        "catalogue" => "Catalogo",
        "categories" => "Categorie",
        "category" => "Categoria",
        "caution" => "Attenzione",
        "checked" => "Verificato",
        "code" => "Codice",
        "code_comment" => "Questo codice può essere usato per identificare questo record a livello di programmazione",
        "color" => "Colore",
        "conditions" => "Condizioni",
        "created_at" => "Creato il",
        "custom_fields" => "Campi personalizzati",
        "customer_group" => "Gruppo cliente",
        "customer_groups" => "Gruppi cliente",
        "customers" => "Clienti",
        "data" => "Data",
        "deleted_at" => "Eliminato il",
        "discount" => "Sconto",
        "discount_percentage" => "Sconto (%)",
        "discounts" => "Sconti",
        "display_name" => "Nome visualizzato",
        "dont_group" => "-- Non raggruppare",
        "downloads" => "Download",
        "export_orders" => "Esporta ordini",
        "failed" => "Fallito",
        "feeds" => "Feed",
        "fees" => "Tasse",
        "general" => "Generali",
        "group_name" => "Nome gruppo",
        "hide_published" => "Nascondi pubblicati",
        "id" => "ID",
        "image" => "Immagine",
        "image_set" => "Set di immagini",
        "images" => "Immagini",
        "includes_tax" => "Tasse incluse",
        "invalid_quantity" => "La quantità specificata non è valida",
        "inventory" => "Inventario",
        "logo" => "Logo",
        "main_image" => "Immagine principale",
        "message" => "Messaggio",
        "meta_description" => "Meta descrizione",
        "meta_keywords" => "Meta keywords",
        "meta_title" => "Meta titolo",
        "name" => "Nome",
        "no" => "No",
        "none" => "-- Nessuno",
        "not_allowed" => "Non permesso",
        "not_in_use" => "L'opzione non è in uso",
        "notification" => "Notifica",
        "notifications" => "Notifiche",
        "old_price" => "Vecchio prezzo",
        "option" => "Opzione",
        "options" => "Opzioni",
        "order_states" => "Stati d'ordine",
        "orders" => "Ordini",
        "out_of_stock" => "Questo prodotto è esaurito.",
        "out_of_stock_short" => "Esaurito",
        "payment" => "Pagamento",
        "payment_gateway" => "Gateway di pagamento",
        "payment_method" => "Metodo di pagamento",
        "payment_methods" => "Metodi di pagamento",
        "payment_provider" => "Provider di pagamento",
        "payments" => "Pagamenti",
        "price_missing" => "Inserisci almeno un prezzo per la valuta corrente",
        "product" => "Prodotto",
        "product_or_variant" => "Prodotto/Variante",
        "products" => "Prodotti",
        "properties" => "Proprietà",
        "properties_links" => "Proprietà/Link",
        "property" => "Proprietà",
        "property_group" => "Gruppo di proprietà",
        "property_groups" => "Gruppi di proprietà",
        "rates" => "Aliquote",
        "reference" => "Riferimento",
        "reorder" => "Riordina voci",
        "review" => "Recensione",
        "review_categories" => "Categorie di recensioni",
        "review_category" => "Categoria di revisione",
        "reviews" => "recensioni",
        "saved_changes" => "Salvato con successo",
        "select_file" => "Scegli file",
        "select_image" => "Scegli immagine",
        "select_placeholder" => "-- Per favore scegli",
        "seo" => "SEO",
        "service" => "servizio",
        "services" => "Servizi",
        "session_id" => "ID sessione",
        "shipping" => "Spedizione",
        "shipping_methods" => "Metodi di spedizione",
        "shop" => "Negozio",
        "since_begin" => "Dall'inizio",
        "slug" => "URL",
        "slug_unique" => "L'URL deve essere unico",
        "sort_order" => "Ordinamento",
        "sorting_updated" => "L'ordinamento è stato aggiornato",
        "stock_limit_reached" => "Non puoi aggiungere altri oggetti di questo tipo al tuo carrello perchè il limite di provvigione è stato raggiunto.",
        "successful" => "Riuscito",
        "taxes" => "Tasse",
        "title" => "Titolo",
        "unchecked" => "Non verificato",
        "unit" => "Unità",
        "updated_at" => "Aggiornato il",
        "use_backend_defaults" => "Usa le impostazioni di default configurate nel backend",
        "value" => "Valore",
        "variant" => "Variante",
        "variants" => "Varianti",
        "version" => "Versione",
        "website" => "Sito web",
        "weekly" => "Settimanalmente",
        "yes" => "Si"
    ],
    "components" => [
        "addressForm" => [
            "details" => [
                "description" => "Mostra un form per modificare l'indirizzo di un utente",
                "name" => "Form indirizzo"
            ],
            "properties" => [
                "address" => ["title" => "Indirizzo"],
                "redirect" => ["title" => "Reindirizza (dopo il salvataggio)"],
                "set" => ["title" => "Usa questo indirizzo come"]
            ],
            "redirects" => ["checkout" => "Pagina Checkout"],
            "set" => ["billing" => "Indirizzo di fatturazione", "shipping" => "Indirizzo di spedizione"]
        ],
        "addressList" => [
            "details" => [
                "description" => "Mostra una lista di tutti gli indirizzi registrati dall'utente",
                "name" => "Lista indirizzi"
            ],
            "errors" => [
                "address_not_found" => "L'indirizzo richiesto non è stato trovato",
                "cannot_delete_last_address" => "Non puoi eliminare il tuo ultimo indirizzo"
            ],
            "messages" => [
                "address_deleted" => "Indirizzo eliminato",
                "default_billing_address_changed" => "Indirizzo di fatturazione predefinito cambiato",
                "default_shipping_address_changed" => "Indirizzo di spedizione predefinito cambiato"
            ]
        ],
        "addressSelector" => [
            "details" => [
                "description" => "Mostra una lista di tutti gli indirizzi salvati dall'utente",
                "name" => "Selettore indirizzi"
            ]
        ],
        "cart" => [
            "details" => ["description" => "Mostra il carrello", "name" => "Carrello"],
            "properties" => [
                "discountCodeLimit" => [
                    "description" => "Impostare su 0 per codici illimitati",
                    "title" => "Limite del codice sconto"
                ],
                "showDiscountApplier" => ["title" => "Mostra form per applicare gli sconti"],
                "showProceedToCheckoutButton" => ["title" => "Mostra il pulsante \"Procedi alla cassa\"."],
                "showShipping" => ["title" => "Mostra le spese di spedizione"],
                "showTaxes" => ["title" => "Mostra tasse"]
            ]
        ],
        "cartSummary" => [
            "details" => [
                "description" => "Mostra il numero di articoli e il valore complessivo del carrello",
                "name" => "Riepilogo carrello"
            ],
            "properties" => [
                "showItemCount" => [
                    "description" => "Mostra il conteggio degli articoli nel carrello",
                    "title" => "Mostra conteggio prodotti"
                ],
                "showTotalPrice" => [
                    "description" => "Mostra il valore totale degli articoli nel carrello",
                    "title" => "Mostra il valore totale"
                ]
            ]
        ],
        "categories" => [
            "by_slug" => "Usa la categoria nell'url come categoria genitore",
            "details" => ["description" => "Elenca le categorie disponibili", "name" => "Categorie"],
            "no_parent" => "Mostra tutte le categorie",
            "properties" => [
                "categoryPage" => [
                    "description" => "I link punteranno a questa pagina. Se lasciato vuoto, verranno usate le impostazioni di default del backend.",
                    "title" => "Pagina categoria"
                ],
                "categorySlug" => [
                    "description" => "Usa questo parametro per caricare la categoria figlia dall'url",
                    "title" => "Parametro slug di categoria"
                ],
                "parent" => [
                    "description" => "Mostra solo le categorie figlie di questa categoria",
                    "title" => "Inizia dalla categoria"
                ]
            ]
        ],
        "checkout" => [
            "details" => ["description" => "Gestisce il processo di checkout", "name" => "Checkout"],
            "errors" => [
                "missing_settings" => "Per favore seleziona un metodo di spedizione e pagamento."
            ],
            "properties" => ["step" => ["name" => "Passo di checkout attivo (è impostato automaticamente)"]]
        ],
        "currencyPicker" => [
            "details" => [
                "description" => "Mostra un selettore per la scelta fra le valute attualmente attive nel negozio",
                "name" => "Selettore valuta"
            ]
        ],
        "customerDashboard" => [
            "details" => [
                "description" => "Mostra al cliente un link alla pagina per effettuare il login e modificare le proprie impostazioni",
                "name" => "Dashboard cliente"
            ],
            "properties" => [
                "customerDashboardLabel" => [
                    "description" => "Testo del link per la pagina account cliente",
                    "title" => "Testo per la dashboard cliente"
                ],
                "logoutLabel" => ["description" => "Testo del link di logout", "title" => "Testo Logout"]
            ]
        ],
        "customerProfile" => [
            "details" => [
                "description" => "Mostra il form di modifica del profilo cliente.",
                "name" => "Profilo cliente"
            ]
        ],
        "dependencies" => [
            "details" => [
                "description" => "Include tutte le dipendenze necessarie al frontend",
                "name" => "Dipendenze frontend"
            ]
        ],
        "discountApplier" => [
            "details" => [
                "description" => "Mostra un campo per il codice promozionale",
                "name" => "Input per codice promozionale"
            ],
            "discount_applied" => "Sconto applicato con successo!"
        ],
        "enhancedEcommerceAnalytics" => [
            "details" => [
                "description" => "Implementa un Google Tag Manager Data Layer",
                "name" => "Componente Ecommerce migliorato (UA)"
            ]
        ],
        "myAccount" => [
            "details" => [
                "description" => "Mostra molteplici form dove un utente può vedere e modificare il suo profilo",
                "name" => "Account utente"
            ],
            "pages" => ["addresses" => "Indirizzi", "orders" => "Ordini", "profile" => "Profilo"],
            "properties" => ["page" => ["title" => "Sottopagina attiva"]]
        ],
        "ordersList" => [
            "details" => [
                "description" => "Mostra una lista di tutti gli ordini del cliente",
                "name" => "Lista ordini"
            ]
        ],
        "paymentMethodSelector" => [
            "details" => [
                "description" => "Mostra una lista di tutti i metodi di pagamento disponibili",
                "name" => "Selettore metodo di pagamento"
            ],
            "errors" => [
                "unavailable" => "Il metodo di pagamento selezionato non é disponibile per il tuo ordine."
            ]
        ],
        "product" => [
            "added_to_cart" => "Prodotto aggiunto con successo",
            "details" => [
                "description" => "Mostra i dettagli di un prodotto",
                "name" => "Dettagli prodotto"
            ],
            "properties" => [
                "redirectOnPropertyChange" => [
                    "description" => "Reindirizzare l'utente alla nuova pagina di dettaglio se una proprietà è stata cambiata",
                    "title" => "Reindirizzamento sul cambio di proprietà"
                ]
            ]
        ],
        "productReviews" => [
            "details" => [
                "description" => "Visualizza tutte le recensioni di un prodotto",
                "name" => "Recensioni sui prodotti"
            ],
            "properties" => [
                "currentVariantReviewsOnly" => [
                    "description" => "Non mostrare le recensioni di altre varianti di questo prodotto",
                    "title" => "Mostra solo le valutazioni di questa variante"
                ],
                "perPage" => ["title" => "Numero di recensioni per pagina"]
            ]
        ],
        "products" => [
            "details" => ["description" => "Mostra una lista di prodotti", "name" => "Prodotti"],
            "properties" => [
                "filter" => [
                    "description" => "Forza il filtro per questo componente",
                    "title" => "Filter string"
                ],
                "filter_component" => [
                    "description" => "Alias del componente \"ProductsFilter\" che filtra questo componente \"Products\"",
                    "title" => "Alias del componente \"Filter\""
                ],
                "include_children" => [
                    "description" => "Mostra anche tutti i prodotti delle sottocategorie",
                    "title" => "Includi sottocategorie"
                ],
                "include_variants" => [
                    "description" => "Non mostrare il singolo prodotto, ma tutte le varianti disponibili",
                    "title" => "Mostra le varianti dell'articolo"
                ],
                "no_category_filter" => "Non filtrare per categoria",
                "paginate" => [
                    "description" => "Applica la paginazione al risultato (mostra più di una pagina)",
                    "title" => "Paginazione"
                ],
                "per_page" => [
                    "description" => "Numero di prodotti visualizzati per pagina",
                    "title" => "Prodotti per pagina"
                ],
                "set_page_title" => [
                    "description" => "Usa il nome della categoria come titolo di pagina",
                    "title" => "Imposta il titolo della pagina"
                ],
                "sort" => [
                    "description" => "Questo sovrascrive l'ordine prescelto dall'utente",
                    "title" => "Ordina"
                ],
                "use_url" => "Usa lo slug della categoria dall'URL"
            ]
        ],
        "productsFilter" => [
            "details" => [
                "description" => "Filtra i prodotti a partire da una categoria",
                "name" => "Filtro Prodotti"
            ],
            "properties" => [
                "includeChildren" => [
                    "description" => "Include le proprietà e i filtri anche dai prodotti delle sottocategorie",
                    "title" => "Includi sottocategorie"
                ],
                "includeSliderAssets" => [
                    "description" => "Include via cdnjs tutte le dipendenze per lo Slider noUI",
                    "title" => "Includi \"noUI Slider\""
                ],
                "includeVariants" => [
                    "description" => "Mostra i filtri per le proprietà delle varianti",
                    "title" => "Includi varianti"
                ],
                "showBrandFilter" => ["title" => "Mostra filtro brand"],
                "showOnSaleFilter" => ["title" => "Mostra filtro in saldo"],
                "showPriceFilter" => ["title" => "Mostra filtro prezzo"],
                "sortOrder" => ["description" => "Ordinamento iniziale", "title" => "Ordinamento"]
            ],
            "sortOrder" => [
                "bestseller" => "Bestseller",
                "latest" => "Ultimi arrivi",
                "manual" => "Manuale",
                "name" => "Nome",
                "oldest" => "Da più tempo nel negozio",
                "priceHigh" => "Prezzo più alto",
                "priceLow" => "Prezzo più basso",
                "random" => "Casuale",
                "ratings" => "valutazioni"
            ]
        ],
        "quickCheckout" => [
            "details" => [
                "description" => "Processo di checkout a pagina singola",
                "name" => "Pagamento rapido"
            ],
            "errors" => ["signup_failed" => "Impossibile creare un account utente."]
        ],
        "shippingMethodSelector" => [
            "details" => [
                "description" => "Mostra una lista con tutte i metodi di spedizione disponibili",
                "name" => "Selettore spedizioni"
            ],
            "errors" => [
                "unavailable" => "Il metodo di spedizione selezionato non é disponibile per il tuo ordine."
            ]
        ],
        "signup" => [
            "details" => [
                "description" => "Mostra il form di registrazione e login",
                "name" => "Registrazione"
            ],
            "errors" => [
                "city" => ["required" => "Per favore, inserisci la tua città."],
                "country_id" => [
                    "exists" => "La nazione scelta non é valida.",
                    "required" => "Scegli una nazione."
                ],
                "email" => [
                    "email" => "Questo indirizzo email non é valido.",
                    "non_existing_user" => "Un utente con questo indirizzo email é già registrato. Usa la funzione di recupero password.",
                    "required" => "Per favore, inserisci un indirizzo email.",
                    "unique" => "Un utente con questo indirizzo email é già registrato."
                ],
                "firstname" => ["required" => "Per favore, inserisci il tuo cognome."],
                "lastname" => ["required" => "Per favore, inserisci il tuo nome."],
                "lines" => ["required" => "Per favore, inserisci il tuo indirizzo."],
                "login" => [
                    "between" => "Per favore, inserisci un indirizzo email valido.",
                    "email" => "Per favore, inserisci un indirizzo email valido.",
                    "required" => "Per favore, inserisci un indirizzo email."
                ],
                "not_activated" => "Il tuo account deve essere attivato prima di poter accedere.",
                "password" => [
                    "max" => "La password fornita é troppo lunga.",
                    "min" => "La password fornita é troppo corta. Per favore, inserisci almeno 8 caratteri.",
                    "required" => "Per favore, inserisci la tua password."
                ],
                "password_repeat" => [
                    "required" => "Per favore, ripeti la password.",
                    "same" => "La password di conferma non coincide con la password scelta."
                ],
                "state_id" => [
                    "exists" => "Il valore  selezionato non é valido.",
                    "required" => "Scegli una provincia"
                ],
                "terms_accepted" => ["required" => "Si prega di accettare i nostri termini e condizioni."],
                "unknown_user" => "Le credenziali che hai inserito non sono valide.",
                "user_is_guest" => "Stai provando ad accedere con un account ospite.",
                "zip" => ["required" => "Per favore, inserisci il tuo CAP."]
            ],
            "properties" => ["redirect" => ["name" => "Reindirizza dopo il login"]]
        ],
        "wishlistButton" => [
            "details" => [
                "description" => "Visualizza un pulsante per la lista dei desideri",
                "name" => "Pulsante Wishlist"
            ],
            "properties" => [
                "product" => ["description" => "ID del prodotto", "name" => "Prodotto"],
                "variant" => ["description" => "ID della variante", "name" => "Variante"]
            ]
        ],
        "wishlists" => [
            "details" => [
                "description" => "Visualizza il gestore della lista dei desideri",
                "name" => "Lista dei desideri"
            ],
            "properties" => [
                "showShipping" => [
                    "description" => "Mostra le spese di spedizione e il selettore",
                    "name" => "Mostra la spedizione"
                ]
            ]
        ]
    ],
    "currency_settings" => [
        "currencies" => "Inserire solo i codici di valuta ufficiali a 3 caratteri.",
        "currency_code" => "Codice di valuta",
        "currency_decimals" => "Decimali della valuta",
        "currency_format" => "Formato",
        "currency_rate" => "Tasso di cambio",
        "currency_rounding" => "Arrotondamento del totale",
        "currency_rounding_comment" => "Il totale, tasse incluse, è arrotondato a questo valore se questa valuta è attiva.",
        "currency_symbol" => "Simbolo",
        "description" => "Imposta le tue valute",
        "is_default" => "È predefinita",
        "label" => "Valute",
        "unknown" => "Valuta sconosciuta"
    ],
    "custom_field_options" => [
        "add" => "Aggiungi opzione",
        "attributes" => "Attributo",
        "checkbox" => "Checkbox",
        "color" => "Colore",
        "date" => "Data",
        "datetime" => "Data ora",
        "dropdown" => "Dropdown",
        "float" => "Numero con virgola",
        "image" => "Immagine",
        "integer" => "Numero intero",
        "name" => "Nome",
        "option" => "Opzione",
        "price" => "Prezzo",
        "richeditor" => "Editor WYSIWYG",
        "switch" => "interruttore",
        "text" => "Campo di testo",
        "textarea" => "Campo di testo multilinea"
    ],
    "custom_fields" => [
        "is_not_required" => "Non obbligatorio",
        "is_required" => "Obbligatorio",
        "name" => "Nome campo",
        "options" => "Opzioni",
        "required" => "Obbligatorio",
        "required_comment" => "Questo campo è obbligatorio per creare un ordine",
        "type" => "Tipo campo"
    ],
    "customer_group" => [
        "code_comment" => "Questo codice può essere usato per identificare questo gruppo a livello di programmazione",
        "discount_comment" => "Assegna a questo gruppo cliente uno sconto specifico in % su tutto il catalogo"
    ],
    "discounts" => [
        "amount" => "Importo fisso",
        "code" => "Codice sconto",
        "code_comment" => "Lasciare vuoto per generare un codice casuale",
        "expires" => "Scadenza",
        "max_number_of_usages" => "Numero massimo di utilizzi",
        "name" => "Nome",
        "number_of_usages" => "Numero di utilizzi",
        "rate" => "Tasso (%)",
        "section_trigger" => "Quando è applicabile questo sconto?",
        "section_type" => "Cosa fa questo sconto?",
        "shipping_description" => "Metodo di spedizione alternativo",
        "shipping_guaranteed_days_to_delivery" => "Giorni garantiti per la consegna",
        "shipping_price" => "Prezzo del metodo di spedizione alternativo",
        "total_to_reach" => "Totale d'ordine minimo affinchè lo sconto sia valido",
        "trigger" => "Valido se",
        "triggers" => [
            "code" => "Il codice sconto è stato inserito",
            "customer_group" => "Il cliente fa parte del gruppo",
            "product" => "Un prodotto specifico è presente nel carrello",
            "shipping_method" => "Il metodo di spedizione è uno dei seguenti",
            "total" => "Il totale dell'ordine è stato raggiunto"
        ],
        "type" => "Tipo di sconto",
        "types" => [
            "fixed_amount" => "Importo fisso",
            "rate" => "Tasso",
            "shipping" => "Spedizione alternativa"
        ],
        "valid_from" => "Valido da",
        "validation" => [
            "cart_limit_reached" => "Limite di codice promozionale raggiunto. Non è più possibile aggiungere codici promozionali per questo carrello.",
            "duplicate" => "Puoi usare lo stesso codice promozionale solo una volta.",
            "empty" => "Inserisci un codice di promozione.",
            "expired" => "Questo codice promozionale è scaduto.",
            "not_found" => "Questo codice promozionale non è valido.",
            "shipping" => "Puoi applicare solo un codice promozionale per abbassare le spese di spedizione.",
            "usage_limit_reached" => "Questo codice di sconto non è più valido perché usato troppe volte ."
        ]
    ],
    "feed_settings" => [
        "description" => "Configura le sorgenti dati di mall",
        "google_merchant_enabled" => "Abilita la sorgente dati per Google Merchant Center",
        "google_merchant_enabled_comment" => "Verrà generata usa sorgente dati",
        "google_merchant_url" => "URL della sorgente dati per Google Merchant",
        "google_merchant_url_locale" => "Aggiungere ?locale=xy per ottenere una sogrente dati tradotta"
    ],
    "general_settings" => [
        "account_page" => "Pagina account",
        "account_page_comment" => "In questa pagina il componente \"myAccount\" deve essere presente",
        "address_page" => "Pagina indirizzi",
        "address_page_comment" => "In questa pagina il componente \"addressForm\" deve essere presente",
        "admin_email" => "Email amministratore",
        "admin_email_comment" => "Le notifiche d'amministrazione verranno spedite a questo indirizzo",
        "base" => "Impostazioni base",
        "cart_page" => "Pagina carrello",
        "cart_page_comment" => "In questa pagina il componente \"cart\" deve essere presente",
        "category" => "Mall: Generale",
        "category_orders" => "Mall: Ordini",
        "category_page" => "Pagina categorie per l'elenco dei prodotti",
        "category_page_comment" => "Aggiungi il componente \"products\" a questa pagina.",
        "category_payments" => "Mall: Pagamenti",
        "checkout_page" => "Pagina checkout",
        "checkout_page_comment" => "In questa pagina il componente \"checkout\" deve essere presente",
        "customizations" => "Personalizzazioni",
        "customizations_comment" => "Personalizza le caratteristiche del tuo negozio",
        "description" => "Impostazioni Generali",
        "group_search_results_by_product" => "Raggruppa i risultati della ricerca per prodotto",
        "group_search_results_by_product_comment" => "Includere un prodotto solo una volta nei risultati della ricerca, non visualizzare tutte le varianti corrispondenti",
        "index_driver" => "Driver per l'indicizzazione",
        "index_driver_comment" => "Se il tuo database supporta JSON usa il driver database.",
        "index_driver_database" => "Database (solo per MySQL 5.7+ o MariaDB 10.2+)",
        "index_driver_filesystem" => "Filesystem",
        "index_driver_hint" => "Se cambi questa opzione assicurati di eseguire \"php artisan mall:reindex\" nella riga di comando per re-indicizzare i tuoi prodotti!",
        "label" => "Configurazione",
        "links" => "Pagine CMS",
        "links_comment" => "Scegli quali pagine verranno usate per visualizzare i componenti base del tuo negozio",
        "order_number_start" => "Primo numero d'ordine",
        "order_number_start_comment" => "ID iniziale del primo ordine",
        "product_page" => "Pagina dettagli del prodotto",
        "product_page_comment" => "Qui è dove vengono visualizzati i dettagli del prodotti",
        "redirect_to_cart" => "Reindirizza al carrello",
        "redirect_to_cart_comment" => "Reindirizza al carrello dopo che l'utente ha aggiunto un prodotto",
        "shipping_selection_before_payment" => "Selezionare il metodo di spedizione PRIMA del pagamento durante il checkout",
        "shipping_selection_before_payment_comment" => "Per impostazione predefinita, durante il checkout, all'utente viene chiesto di selezionare un metodo di pagamento prima di selezionare un metodo di spedizione; utilizzare questa opzione per invertire questa logica",
        "use_state" => "Usa il campo Stato/Contea/Provincia",
        "use_state_comment" => "I clienti devono selezionare uno stato/paese/provincia durante la registrazione"
    ],
    "image_sets" => [
        "create_new" => "Crea un nuovo set",
        "is_main_set" => "È il set principale",
        "is_main_set_comment" => "Usa questo set di immagini per questo prodotto"
    ],
    "menu_items" => [
        "all_categories" => "Tutte le categorie",
        "all_products" => "Tutti i prodotti",
        "all_variants" => "Tutte le varianti",
        "single_category" => "Singola categoria"
    ],
    "notification_settings" => ["description" => "Configura le notifiche del negozio", "label" => "Notifiche"],
    "notifications" => [
        "enabled" => "Abiltiata",
        "enabled_comment" => "Questa notifica é abilitata",
        "template" => "Template mail"
    ],
    "order" => [
        "adjusted_amount" => "Importo corretto",
        "billing_address" => "Indirizzo di fatturazione",
        "card_holder_name" => "Titolare della carta",
        "card_type" => "Tipo di carta",
        "change_order_status" => "Cambia stato d'ordine",
        "change_payment_status" => "Cambia stato del pagamento",
        "completion_date" => "Completato il",
        "creation_date" => "Creato il",
        "credit_card" => "Carta di credito",
        "credit_card_last4_digits" => "Ultime 4 cifre",
        "currency" => "Valuta",
        "custom_fields" => "Campi personalizzati",
        "customer" => "Cliente",
        "data" => "Data dell'ordine",
        "delete_confirm" => "Sei sicuro di voler eliminare questo ordine?",
        "deleted" => "Ordine eliminato con successo",
        "deleting" => "Eliminando l'ordine...",
        "download_invoice" => "Scaricare la fattura",
        "email" => "Email",
        "grand_total" => "Totale",
        "invalid_status" => "Lo stato selezionato non esiste.",
        "invoice_number" => "# Fattura",
        "items" => "Articoli",
        "items_total" => "Totale articoli",
        "lang" => "Lingua",
        "modal" => ["cancel" => "Annulla", "update" => "Aggiorna informazioni"],
        "modification_date" => "Modificato il",
        "not_shipped" => "Pendente",
        "notes" => "Note",
        "order_file_name" => "ordine-:order",
        "order_number" => "# Ordine",
        "payment_gateway_used" => "Gateway di pagamento",
        "payment_hash" => "Hash di pagamento",
        "payment_method" => "Metodo di pagamento",
        "payment_states" => [
            "failed_state" => "Pagamento fallito",
            "paid_state" => "Pagato",
            "pending_state" => "Pagamento in attesa",
            "refunded_state" => "Pagamento rimborsato"
        ],
        "payment_status" => "Stato del pagamento",
        "payment_transaction_id" => "ID transazione di pagamento",
        "quantity" => "Quantità",
        "rebate_amount" => "Importo del rimborso",
        "refunds_amount" => "Importo rimborsi",
        "shipped" => "Spedito",
        "shipping_address" => "Indirizzo di spedizione",
        "shipping_address_is_same_as_billing" => "L'indirizzo di spedizione é uguale a quello di fatturazione",
        "shipping_address_same_as_billing" => "L'indirizzo di spedizione è lo stesso di quello di fatturazione",
        "shipping_enabled" => "Spedizione abilitata",
        "shipping_fees" => "Spese di spedizione",
        "shipping_method" => "Metodo di spedizione",
        "shipping_pending" => "Spedizione pendente",
        "shipping_provider" => "Fornitore della spedizione",
        "status" => "Stato",
        "subtotal" => "Subtotale",
        "tax_provider" => "Provider tasse",
        "taxable_total" => "Totale imponibile",
        "taxes_total" => "Totale tasse",
        "total" => "Totale",
        "total_rebate_rate" => "Totale rimborso",
        "total_revenue" => "Entrate totali",
        "total_weight" => "Peso totale",
        "tracking_completed" => "Segna l'ordine come completato",
        "tracking_completed_comment" => "L'ordine sarà segnato come completato",
        "tracking_notification" => "Invia notifica",
        "tracking_notification_comment" => "Verrà spedita al cliente una notifica contenente le informazioni sul tracciamento",
        "tracking_number" => "Numero di tracciamento",
        "tracking_shipped" => "Segna l'ordine come spedito",
        "tracking_shipped_comment" => "L'ordine verrà segnato come spedito",
        "tracking_url" => "Url di tracciamento",
        "update_invoice_number" => "Imposta il numero della fattura",
        "update_shipping_state" => "Aggiorna stato della spedizione",
        "updated" => "Ordine aggiornato con successo",
        "virtual_product_download_hint" => "I link per il download saranno inviati separatamente dopo il pagamento.",
        "will_be_paid_later" => "Sarà pagato successivamente"
    ],
    "order_state_settings" => ["description" => "Configura gli stati d'ordine"],
    "order_states" => [
        "color" => "Colore",
        "description" => "Descrizione",
        "flag" => "Flag speciale",
        "flags" => [
            "cancelled" => "Imposta lo stato di quest'ordine come \"annullato\"",
            "complete" => "Imposta lo stato di quest'ordine come \"completato\"",
            "new" => "Imposta lo stato di quest'ordine come \"nuovo\""
        ],
        "name" => "Nome"
    ],
    "order_status" => [
        "cancelled" => "Annullato",
        "delivered" => "Consegnato",
        "disputed" => "Contestato",
        "pending" => "Pendente",
        "processed" => "Preso in carico",
        "shipped" => "Spedito"
    ],
    "payment_gateway_settings" => [
        "description" => "Configura i tuoi gateway di pagamento",
        "label" => "Gateway di pagamento",
        "paypal" => [
            "client_id" => "ID Cliente PayPal",
            "secret" => "Chiave segreta PayPal",
            "test_mode" => "Modalità test",
            "test_mode_comment" => "Esegue tutti i pagamenti all'interno della Sandbox di PayPal."
        ],
        "postfinance" => [
            "hashing_method" => "Algoritmo di hash",
            "hashing_method_comment" => "Configurazione -> Informazioni tecniche -> Parametri globali di sicurezza",
            "pspid" => "PSPID (nome utente)",
            "sha_in" => "Firma SHA-IN",
            "sha_in_comment" => "Configurazione -> Informazioni tecniche -> Verifica dei dati e dell'origine",
            "sha_out" => "Firma SHA-OUT",
            "sha_out_comment" => "Configurazione -> Informazioni tecniche -> Feedback sulle transazioni",
            "test_mode" => "Modalità di prova",
            "test_mode_comment" => "Eseguire tutti i pagamenti contro l'ambiente di prova"
        ],
        "stripe" => [
            "api_key" => "Chiave API Stripe",
            "api_key_comment" => "Puoi trovare questa chiave nella tua dashboard di Stripe",
            "publishable_key" => "Chiave pubblicabile Stripe",
            "publishable_key_comment" => "Puoi trovare questa chiave nella tua dashboard di Stripe"
        ]
    ],
    "payment_log" => [
        "code_comment" => "Questo codice è stato restituito dal provider di pagamento",
        "data_comment" => "Questi dati sono stati restituiti dal provider di pagamento",
        "failed_only" => "Solo falliti",
        "message_comment" => "Questo messaggio è stato restituito dal provider di pagamento",
        "order_data_comment" => "Questi sono tutti i dati d'ordine per questo pagamento",
        "payment_data" => "Dati di pagamento"
    ],
    "payment_method" => [
        "fee_label" => "Etichetta della tariffa",
        "fee_label_comment" => "Questo testo sarà mostrato ai clienti durante il checkout.",
        "fee_percentage" => "Tariffa in percentuale",
        "fee_percentage_comment" => "La percentuale del totale da aggiungere al totale dell'ordine",
        "instructions" => "Istruzioni di pagamento",
        "instructions_comment" => "Sintassi Twig supportata. Usa {{ order }} per accedere alle informazioni dell'ordine, se disponibili",
        "pdf_partial" => "Allegato PDF parziale",
        "pdf_partial_comment" => "Per tutti gli ordini con questo metodo di pagamento un PDF renderizzato del parziale selezionato sarà allegato alla mail di notifica",
        "pdf_partial_none" => "Nessun allegato PDF",
        "price" => "Tariffa fissa",
        "price_comment" => "Valore da aggiungere al totale dell'ordine"
    ],
    "payment_method_settings" => ["description" => "Gestisci i metodi di pagamento"],
    "payment_status" => [
        "cancelled" => "Annullato",
        "charged_back" => "Contestazione di addebito",
        "deferred" => "Rinviato",
        "expired" => "Scaduto",
        "failed" => "Fallito",
        "open" => "Aperto",
        "paid" => "Pagato",
        "paid_deferred" => "Pagamento rinviato",
        "paiddeferred" => "Pagamento rinviato",
        "paidout" => "Pagato",
        "pending" => "Pendente",
        "refunded" => "Rimborsato"
    ],
    "permissions" => [
        "manage_brands" => "Può gestire i marchi",
        "manage_categories" => "Può gestire le categorie",
        "manage_customer_addresses" => "Può gestire gli indirizzi dei clienti",
        "manage_customer_groups" => "Può gestire i gruppi di clienti",
        "manage_discounts" => "Può gestire gli sconti",
        "manage_feeds" => "Può gestire i feed",
        "manage_notifications" => "Può gestire le notifiche",
        "manage_order_states" => "Può gestire gli stati d'ordine",
        "manage_orders" => "Può gestire gli ordini",
        "manage_payment_log" => "Può gestire i log di pagamento",
        "manage_price_categories" => "Può gestire le categorie dei prezzi",
        "manage_products" => "Può gestire i prodotti",
        "manage_properties" => "Può modificare le proprietà dei prodotti",
        "manage_reviews" => "Può gestire le recensioni",
        "manage_services" => "Può gestire i servizi",
        "manage_shipping_methods" => "Può gestire i metodi di spedizione",
        "manage_taxes" => "Può gestire le tasse",
        "manage_wishlists" => "Può gestire le liste dei desideri",
        "settings" => [
            "manage_currency" => "Può cambiare le impostazioni di valuta del negozio",
            "manage_general" => "Può cambiare le impostazioni generali del negozio",
            "manage_payment_gateways" => "Può cambiare le impostazioni del gateway di pagamento",
            "manage_payment_methods" => "Può gestire i metodi di pagamento"
        ]
    ],
    "plugin" => ["description" => "Soluzione E-commerce per October CMS", "name" => "Mall"],
    "price_category_settings" => [
        "description" => "Configura categorie di prezzo aggiuntive",
        "label" => "Categorie di prezzo"
    ],
    "product" => [
        "add_currency" => "Aggiungi valuta",
        "additional_descriptions" => "Descrizioni addizionali",
        "additional_properties" => "Campi addizionali",
        "allow_out_of_stock_purchases" => "Permetti acquisti quando anche se esaurito",
        "allow_out_of_stock_purchases_comment" => "Questo prodotto può essere ordinato anche quando è esaurito",
        "currency" => "Valuta",
        "description" => "Descrizione",
        "description_short" => "Descrizione breve",
        "details" => "Dettagli",
        "duplicate_currency" => "Hai inserito molteplici prezzi per la stessa valuta",
        "embed_code" => "Incorpora il codice",
        "embed_title" => "Titolo",
        "embeds" => "incorpora",
        "filter_virtual" => "Mostra solo i prodotti virtuali",
        "general" => "Generale",
        "group_by_property" => "Attributo di raggruppamento per varianti",
        "gtin" => "Numero di articolo commerciale globale (GTIN)",
        "height" => "Altezza (mm)",
        "inventory_management_method" => "Metodo di gestione dell'inventario",
        "is_not_taxable" => "Non usare tasse",
        "is_taxable" => "Usa tassa",
        "is_virtual" => "È virtuale",
        "is_virtual_comment" => "Questo prodotto è virtuale (un file, nessuna spedizione)",
        "length" => "Lunghezza (mm)",
        "link_target" => "URL di riferimento",
        "link_title" => "Titolo",
        "links" => "Link",
        "missing_category" => "Il prodotto non ha associata una categoria. Per favore scegli una categoria qui sotto per modificare questo prodotto.",
        "mpn" => "Numero di parte del produttore (MPN)",
        "name" => "Nome prodotto",
        "not_published" => "Non pubblicato",
        "price" => "Prezzo",
        "price_includes_tax" => "Prezzo tasse incluse",
        "price_includes_tax_comment" => "Il prezzo definito include tutte le tasse",
        "price_table_modal" => [
            "currency_dropdown" => "Valuta: ",
            "label" => "Prezzo e quantità",
            "title" => "Panoramica prezzi e quantità",
            "trigger" => "Modifica quantità e prezzi"
        ],
        "product_file" => "Scheda prodotto",
        "product_file_version" => "versione del file",
        "product_files" => "File del prodotto",
        "product_files_section_comment" => "Questo è un prodotto virtuale. Puoi caricare nuove versioni di file qui sotto. L'ultima versione sarà scaricabile dai clienti.",
        "properties" => "Proprietà",
        "property_title" => "Titolo",
        "property_value" => "Valore",
        "published" => "Pubblicato",
        "published_comment" => "Questo prodotto è visibile sul sito",
        "published_short" => "Pubbl.",
        "quantity_default" => "Quantità di default",
        "quantity_max" => "Quantità massima",
        "quantity_min" => "Quantità minima",
        "shippable" => "Spedibile",
        "shippable_comment" => "Questo prodotto può essere spedito",
        "stackable" => "Quantità nel carrello",
        "stackable_comment" => "Se questo prodotto è aggiunto nel carrello più volte mostra solo un entità (incrementa quantità)",
        "stock" => "Quantità",
        "taxable" => "Tassabile",
        "taxable_comment" => "Applica le tasse su questo prodotto",
        "user_defined_id" => "ID Prodotto",
        "variant_support_header" => "Varianti non supportate",
        "variant_support_text" => "La categoria selezionata non ha varianti definite. Per favore cambia il metodo di gestione dell'inventario in \"Articolo\" o seleziona un'altra categoria.",
        "weight" => "Peso (g)",
        "width" => "Larghezza (mm)"
    ],
    "product_file" => [
        "display_name_comment" => "Questo nome sarà visibile al cliente.",
        "download_count" => "Scaricare il conteggio",
        "errors" => [
            "expired" => "Link di download scaduto",
            "invalid" => "Link di download non valido",
            "not_found" => "Impossibile trovare il file richiesto, contattaci per il supporto.",
            "too_many_attempts" => "Troppi tentativi di download"
        ],
        "expires_after_days" => "Download valido per giorni",
        "expires_after_days_comment" => "Il file può essere scaricato solo per questo numero di giorni dopo l'acquisto. Lasciare vuoto per nessun limite.",
        "file" => "file",
        "hint" => [
            "info_link" => "nella documentazione",
            "info_text" => "Puoi trovare informazioni su come farlo",
            "intro" => "Questo prodotto non ha un file allegato. Assicurati di aggiungerne uno o di gerenarlo programmaticamente durante il checkout."
        ],
        "max_download_count" => "Numero massimo di download",
        "max_download_count_comment" => "Il file può essere scaricato solo questo numero di volte. Lasciare vuoto per nessun limite.",
        "session_required" => "Accesso richiesto",
        "session_required_comment" => "Il file può essere scaricato solo quando il cliente è loggato (il link di download non è condivisibile).",
        "version_comment" => "Una versione unica aiuta il cliente a riconoscere i file aggiornati."
    ],
    "products" => ["variants_comment" => "Crea più varianti dello stesso prodotto"],
    "properties" => [
        "filter_type" => "Tipo di filtro",
        "filter_types" => ["none" => "Nessuno", "range" => "Intervallo", "set" => "Set"],
        "use_for_variants" => "Usa per le varianti",
        "use_for_variants_comment" => "Questa proprietà é differente per ogni variante di questo prodotto"
    ],
    "review_settings" => [
        "allow_anonymous" => "Permetti recensioni anonime",
        "allow_anonymous_comment" => "Utenti non registrati possono inviare recensioni",
        "description" => "Configura le recensioni",
        "enabled" => "Recensioni abilitate",
        "enabled_comment" => "I clienti possono creare recensioni",
        "moderated" => "Modera le recensioni",
        "moderated_comment" => "Le nuove recensioni possono venire pubblicate solo dall'amministratore del sito"
    ],
    "reviews" => [
        "anonymous" => "Anonimo",
        "approve" => "Approva la recensione",
        "approve_next" => "Approva e passa alla seguente",
        "approved" => "Recensione approvata",
        "cons" => "Aspetti negativi",
        "no_more" => "Non ci sono più recensioni da approvare",
        "only_unapproved" => "Mostrare solo quelle non approvate",
        "pros" => "Aspetti positivi",
        "rating" => "Valutazione",
        "review" => "Dettagli recensione",
        "title" => "titolo della tua recensione"
    ],
    "services" => [
        "option" => "opzione",
        "options" => "Opzioni",
        "required" => "Il servizio è richiesto",
        "required_comment" => "Un'opzione di questo servizio deve essere selezionata quando un prodotto viene aggiunto al carrello."
    ],
    "shipping_method" => [
        "available_above_total" => "Disponibile se il totale é maggiore o uguale a",
        "available_below_total" => "Disponibile se il totale é inferiore a",
        "countries" => "Disponibile per le spedizioni in queste nazioni",
        "countries_comment" => "Se non si seleziona alcuna nazione, questo metodo é disponibile globalmente.",
        "guaranteed_delivery_days" => "Giorni garantiti per la consegna",
        "not_required_description" => "Il carrello attuale non richiede alcuna spedizione.",
        "not_required_name" => "Nessuna spedizione richiesta"
    ],
    "shipping_method_rates" => ["from_weight" => "Da (peso in grammi)", "to_weight" => "A (peso in grammi)"],
    "shipping_method_settings" => ["description" => "Gestisci i metodi di spedizione"],
    "tax" => [
        "countries" => "Applica le tasse solo quando spedito verso queste nazioni",
        "countries_comment" => "Se nessuna nazione è selezionata le tasse si applicano globalmente.",
        "is_default" => "È predefinito",
        "is_default_comment" => "Questa tassa è usata se il paese di destinazione della spedizione non è ancora noto",
        "percentage" => "Percentuale"
    ],
    "tax_settings" => ["description" => "Gestisci le tasse"],
    "titles" => [
        "brands" => ["create" => "Crea brand", "edit" => "Modifica brand"],
        "categories" => [
            "create" => "Crea categoria",
            "preview" => "Anteprima categoria",
            "update" => "Modifica categoria"
        ],
        "custom_field_options" => ["edit" => "Modifica opzioni campo"],
        "customer_groups" => ["create" => "Crea gruppo", "update" => "Modifica gruppo"],
        "discounts" => [
            "create" => "Crea sconto",
            "preview" => "Anteprima sconto",
            "update" => "Modifica sconto"
        ],
        "notifications" => ["update" => "Aggiorna notifica"],
        "order_states" => [
            "create" => "Crea stato d'ordine",
            "edit" => "Modifica stato d'ordine",
            "reorder" => "Riordina stati d'ordine"
        ],
        "orders" => ["export" => "Esporta ordini", "show" => "Dettagli ordine"],
        "payment_methods" => [
            "create" => "Crea metodo di pagamento",
            "edit" => "Modifica metodo di pagamento",
            "reorder" => "Riordina metodi di pagamento"
        ],
        "products" => [
            "create" => "Crea prodotto",
            "preview" => "Anteprima prodotto",
            "update" => "Modifica prodotto"
        ],
        "properties" => ["create" => "Crea proprietà", "edit" => "Modifica proprietà"],
        "property_groups" => ["create" => "Crea gruppo", "edit" => "Modifica gruppo"],
        "reviews" => ["create" => "Crea recensione", "update" => "Modifica recensione"],
        "services" => ["create" => "creare un servizio", "update" => "Modifica del servizio"],
        "shipping_methods" => [
            "create" => "Crea metodo di spedizione",
            "preview" => "Anteprima metodo di spedizione",
            "update" => "Modifica metodo di spedizione"
        ],
        "taxes" => ["create" => "Crea tassa", "update" => "Modifica tassa"]
    ],
    "variant" => ["method" => ["single" => "Articolo", "variant" => "Varianti articolo"]]
];

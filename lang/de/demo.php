<?php

return [
    'brands' => [
        'cruiser_bikes' => [
            'description' => 'Cruiser Bikes ist der führende Fahrrad-Hersteller im Internet',
        ],
    ],
    'categories' => [
        'example' => [
            'name' => 'Beispiel-Kategorie',
            'slug' => 'kategorie',
            'description' => 'Eine Beispiel-Kategorie für deine Produkte.',
        ],
        'bikes' => [
            'name' => 'Fahrräder',
            'meta_title' => 'Fahrräder, Mountainbikes, City-Bikes',
            'meta_description' => 'Werfen Sie einen Blick auf unsere Fahrräder und finden Sie, was Sie suchen.',
        ],
        'mountainbikes' => [
            'name' => 'Mountainbikes',
            'meta_title' => 'Mountainbikes',
            'meta_description' => 'Werfen Sie einen Blick auf unser großes Mountainbike-Sortiment.',
        ],
        'citybikes' => [
            'name' => 'City-Bikes',
            'meta_title' => 'City-Bikes',
            'meta_description' => 'Werfen Sie einen Blick auf unser großes Angebot an City-Bikes.',
        ],
        'clothing' => [
            'name' => 'Kleidung',
            'meta_title' => 'Sportbekleidung',
            'meta_description' => 'Entdecken Sie unsere große Auswahl an Sportbekleidung.',
        ],
        'gift' => [
            'name' => 'Geschenkkarten',
            'meta_title' => 'Geschenkkarten',
            'meta_description' => 'Bestellen Sie Ihre Mall-Geschenkkarte online.',
        ],
    ],
    'customer_groups' => [
        'silver' => [
            'name' => 'Silber-Partner',
        ],
        'gold' => [
            'name' => 'Gold-Partner',
        ],
        'diamond' => [
            'name' => 'Diamant-Partner',
        ],
    ],
    'customers' => [
        'normal' => 'Normaler Kunde',
        'gold' => 'Gold Kunde',
        'diamond' => 'Diamant Kunde',
    ],
    'notifications' => [
        'admin_checkout_succeeded' => [
            'name' => 'Admin Benachrichtigung: Bestellung erfolgreich',
            'description' => 'Wird an den Shop-Administrator gesendet, wenn ein Bestellung erfolgreich aufgegeben wurde',
        ],
        'admin_checkout_failed' => [
            'name' => 'Admin Benachrichtigung: Bestellung fehlgeschlagen',
            'description' => 'Wird an den Shop-Administrator gesendet, wenn ein Bestellung fehlgeschlagen ist',
        ],
        'customer_created' => [
            'name' => 'Kunden-Registrierung',
            'description' => 'Wird versendet, wenn ein Kunde sich registriert hat',
        ],
        'checkout_succeeded' => [
            'name' => 'Bestellung erfolgreich',
            'description' => 'Wird versendet, wenn eine Bestellung erfolgreich aufgegeben wurde',
        ],
        'checkout_failed' => [
            'name' => 'Bestellung fehlgeschlagen',
            'description' => 'Wird versendet, wenn eine Bestellung fehlgeschlagen ist',
        ],
        'order_shipped' => [
            'name' => 'Bestellung versandt',
            'description' => 'Wird versendet, wenn eine Bestellung als versendet markiert wurde',
        ],
        'order_state_changed' => [
            'name' => 'Bestellstatus aktualisiert',
            'description' => 'Wird versendet, wenn der Bestellstatus aktualisiert wurde',
        ],
        'payment_paid' => [
            'name' => 'Bezahlung erhalten',
            'description' => 'Wird versendet, wenn die Zahlung eingegangen ist',
        ],
        'payment_failed' => [
            'name' => 'Bezahlung gescheitert',
            'description' => 'Wird versendet, wenn die Zahlung gescheitert ist',
        ],
        'payment_refunded' => [
            'name' => 'Bezahlung erstattet',
            'description' => 'Wird versendet, wenn eine Zahlung zurückerstattet wurde',
        ],
    ],
    'order_states' => [
        'new' => 'Neu',
        'in_progress' => 'In Bearbeitung',
        'disputed' => 'Reklamiert',
        'cancelled' => 'Storniert',
        'complete' => 'Abgeschlossen',
    ],
    'payment_methods' => [
        'invoice' => 'Auf Rechnung',
    ],
    'products' => [
        'cruiser_1000' => [
            'description' => '<p>Entdecken Sie Ihre Leidenschaft für das Radfahren in der Stadt mit dem Cruisers Modell 1000. Egal, ob Sie zum Zug fahren oder zur Arbeit pendeln wollen, dies ist das richtige Fahrrad für Sie. Der Aluminiumrahmen ist federleicht und langlebig.</p>',
            'description_short' => 'Das ideale Stadtrad',
            'meta_title' => 'Cruiser 1000 City-Bike',
            'meta_description' => "Finden Sie Ihre Leidenschaft für das Stadtradeln auf dem Cruisers' Model 1000",
            'reviews' => [
                'great_bike' => [
                    'title' => 'Großartiges Fahrred!',
                    'description' => 'Ich habe seit Jahren nach einem solchen Fahrrad gesucht!',
                    'pros_01' => 'Schicke Farbe',
                    'pros_02' => 'Guter Preis',
                    'cons_01' => 'Zusammenbau erwies sich als etwas komplizierter',
                ],
            ],
        ],
        'cruiser_1500' => [
            'description' => '<p>Entdecken Sie Ihre Leidenschaft für das Radfahren in der Stadt mit dem Cruisers Modell 1500. Egal, ob Sie zum Zug fahren oder zur Arbeit pendeln wollen, dies ist das richtige Fahrrad für Sie. Der Aluminiumrahmen ist federleicht und langlebig.</p>',
            'description_short' => 'Denke Pink',
            'meta_title' => 'Cruiser 1500 City-Bike',
            'meta_description' => "Finden Sie Ihre Leidenschaft für das Stadtradeln auf dem Cruisers' Model 1500",
            'reviews' => [
                'title' => 'Dieses Fahrrad ist für Mädchen',
                'description' => 'Also ich habe dieses Fahrrad letzte Woche gekauft und nun hat mich mein Freund darauf hingewiesen, dass es eigentlich ein Mädchenfahrrad ist. Dies sollte in der Beschreibung erwähnt werden!',
            ],
        ],
        'cruiser_3000' => [
            'description' => '<p>Entdecke deine Leidenschaft für das Mountainbiken auf dem Cruiser Modell 3000. Egal, ob du Enduro, All-Mountain oder Downhill fahren willst - nichts hält dich davon ab, alles auszuprobieren - dieses spaßige und freundliche Cruiser Bike mit Federung wird dir beim Auf- und Abstieg helfen. Mit diesem Carbon-Mountainbike ist Spaß garantiert.</p>',
            'description_short' => 'Harttail-Fahrrad',
            'meta_title' => 'Cruiser 3000 Mountainbike',
            'meta_description' => 'Egal, ob Sie Enduro, All-Mountain oder Downhill fahren wollen, dieses spaßige und freundliche Cruiser Bike hilft Ihnen beim Auf- und Abstieg.',
        ],
        'cruiser_3500' => [
            'description' => '<p>Entdecke deine Leidenschaft für das Mountainbiken auf dem Cruiser Modell 3500. Egal, ob du Enduro, All-Mountain oder Downhill fahren willst - nichts hält dich davon ab, alles auszuprobieren - dieses spaßige und freundliche Cruiser Bike mit Federung wird dir beim Auf- und Abstieg helfen. Mit diesem Carbon-Mountainbike ist Spaß garantiert.</p>',
            'description_short' => 'Harttail-Fahrrad',
            'meta_title' => 'Cruiser 3500 Mountainbike',
            'meta_description' => 'Egal, ob Sie Enduro, All-Mountain oder Downhill fahren wollen, dieses spaßige und freundliche Cruiser Bike hilft Ihnen beim Auf- und Abstieg.',
        ],
        'cruiser_5000' => [
            'description' => '<p>Entdecke deine Leidenschaft für das Mountainbiken auf dem Cruiser Modell 5000. Egal, ob du Enduro, All-Mountain oder Downhill fahren willst - nichts hält dich davon ab, alles auszuprobieren - dieses spaßige und freundliche Cruiser Bike mit Federung wird dir beim Auf- und Abstieg helfen. Mit diesem Carbon-Mountainbike ist Spaß garantiert.</p>',
            'description_short' => 'Ideal für Einsteiger',
            'meta_title' => 'Cruiser 5000 Mountainbike',
            'meta_description' => 'Egal, ob Sie Enduro, All-Mountain oder Downhill fahren wollen, dieses spaßige und freundliche Cruiser Bike hilft Ihnen beim Auf- und Abstieg.',
            'reviews' => [
                'title' => 'Sehr schlechte Verarbeitungsqualität',
                'description' => 'Das Fahrrad ist in Ordnung, aber nach ein paar Fahrten fingen Teile an, abzufallen!',
            ],
        ],
        'gift_card_50' => [
            'name' => '50 € Geschenkkarte',
            'description' => '<p>Diese Geschenkkarte können Sie zu Hause selbst ausdrucken.</p>',
            'description_short' => 'Gültig nur für dieses Geschäft',
            'meta_title' => '50 € GGeschenkkarte',
        ],
        'gift_card_100' => [
            'name' => '100 € Geschenkkarte',
            'description' => '<p>Diese Geschenkkarte können Sie zu Hause selbst ausdrucken.</p>',
            'description_short' => 'Gültig nur für dieses Geschäft',
            'meta_title' => '100 € Geschenkkarte',
        ],
        'gift_card_200' => [
            'name' => '200 € Geschenkkarte',
            'description' => '<p>Diese Geschenkkarte können Sie zu Hause selbst ausdrucken.</p>',
            'description_short' => 'Gültig nur für dieses Geschäft',
            'meta_title' => '200 € Geschenkkarte',
        ],
        'jersey' => [
            'name' => 'Stormrider Trikot (Männer)',
            'description' => '<p>Die schnell trocknenden und atmungsaktiven Materialien des Stormrider Trikots sorgen für ein perfektes Gleichgewicht zwischen Haltbarkeit, Abriebfestigkeit und Komfort.</p>',
            'description_short' => 'Polyester',
            'meta_title' => 'Stormrider Trikot (Männer)',
            'variants' => [
                'brg_xs_name' => 'Stormrider Trikot (Männer) schwarz/rot/grau XS',
                'brg_s_name' => 'Stormrider Trikot (Männer) schwarz/rot/grau S',
                'brg_m_name' => 'Stormrider Trikot (Männer) schwarz/rot/grau M',
                'brg_l_name' => 'Stormrider Trikot (Männer) schwarz/rot/grau L',
                'bbw_xs_name' => 'Stormrider Trikot (Männer) schwarz/blau/weiß XS',
                'bbw_s_name' => 'Stormrider Trikot (Männer) schwarz/blau/weiß S',
                'bbw_m_name' => 'Stormrider Trikot (Männer) schwarz/blau/weiß M',
                'bbw_l_name' => 'Stormrider Trikot (Männer) schwarz/blau/weiß L',
                'bbw_xl_name' => 'Stormrider Trikot (Männer) schwarz/blau/weiß XL',
            ],
        ],
        'red_shirt' => [
            'name' => 'Rotes T-Shirt',
            'description' => '<p>Dies ist nur ein gewöhnliches T-Shirt. Markenlos und billig.</p>',
            'description_short' => 'Unisex',
            'meta_title' => 'Rotes T-Shirt',
            'variants' => [
                's_name' => 'Rotes T-Shirt S',
                'm_name' => 'Rotes T-Shirt M',
                'l_name' => 'Rotes T-Shirt L',
            ],
        ],
        'fields' => [
            'include_bike_assembly' => 'Inklusive Fahrradmontageanleitung',
        ],
        'images' => [
            'main' => 'Haupt-Bilder',
            'gift' => 'Geschankktarte',
            'jersey_red' => 'Trikot Rot',
            'jersey_blue' => 'Trikot Blau',
        ],
        'properties' => [
            'aluminium' => 'Aluminium',
            'carbon' => 'Carbon',
            'cotton' => 'Baumwolle',
            'polyester' => 'Polyester',
            'male' => 'Männlich',
            'female' => 'Weiblich',
            'unisex' => 'Unisex',
            'brg' => 'schwarz/rot/grau',
            'bbw' => 'schwarz/blau/weiß',
            'red' => 'Rot',
            'devils_red' => 'Teufels rot',
            'darker_red' => 'Dunkles Rot',
            'heavens_blue' => 'Himmelblau',
            'dark_grey' => 'Dunkelgrau',
            'think_pink' => 'Denke Pink',
        ],
    ],
    'property_groups' => [
        'height' => 'Höhe',
        'width' => 'Breite',
        'depth' => 'Tiefe',
        'size' => 'Größe',
        'dimensions' => 'Ausmaße',
        'bike_specs' => 'Fahrrad Spez.',
        'specs' => 'Spezifikationen',
        'gender' => 'Geschlecht',
        'male' => 'Männlich',
        'female' => 'Weiblich',
        'unisex' => 'Unisex',
        'material' => 'Material',
        'color' => 'Farbe',
        'bike_size' => 'Fahrrad Größe',
        'frame_size' => 'Rahmengröße',
        'wheel_size' => 'Reifengröße',
        'suspension' => 'Federung',
        'fork_travel' => 'Gabel-Federung',
        'rear_travel' => 'Heck-Federung',
        'clothing_specs' => 'Kleidungs-Spez.',
    ],
    'price_categories' => [
        'old_price_name' => 'Alter Preis',
        'old_price_label' => 'Alter Preis',
        'msrp_price_name' => 'UVP',
        'msrp_price_label' => 'Unverbindliche Preisempfehlung',
    ],
    'review_categories' => [
        'price' => 'Preis',
        'design' => 'Design',
        'quality' => 'Qualität',
    ],
    'services' => [
        'warranty' => [
            'name' => 'Garantie',
            'description' => 'Sie können die vom Hersteller gewährte Garantie für dieses Produkt verlängern.',
            '2_years_name' => '2 Jahre erweiterte Garantie',
            '2_years_description' => 'Erhalten Sie ein zusätzliches Jahr Garantie',
            '3_years_name' => '3 Jahre erweiterte Garantie',
            '3_years_description' => 'Erhalten Sie zwei zusätzliches Jahre Garantie',
            '4_years_name' => '4 Jahre erweiterte Garantie',
            '4_years_description' => 'Erhalten Sie drei zusätzliches Jahre Garantie',
        ],
        'assembly' => [
            'name' => 'Montage',
            'description' => 'Haben Sie nicht die richtigen Werkzeuge zur Hand? Wir können dieses Produkt für Sie vormontieren.',
            'preassemble_name' => 'Produkt vormontieren',
            'preassemble_description' => 'Das komplett montierte Produkt wird zu Ihnen nach Hause geliefert.',
        ],
    ],
    'shipping_methods' => [
        'standard' => 'Standard',
        'express' => 'Express',
    ],
    'taxes' => [
        'standard' => 'Standard',
        'reduced' => 'Ermäßigt',
    ],
];

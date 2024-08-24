<?php

return [
    'brands' => [
        'cruiser_bikes' => [
            'description' => 'Cruiser Bikes is the leading bicycle manufacturer on the Internet.',
        ],
    ],
    'categories' => [
        'example' => [
            'name' => 'Example Category',
            'slug' => 'example',
            'description'  => 'Example Category for your products.',
        ],
        'bikes' => [
            'name' => 'Bikes',
            'meta_title' => 'Bikes, Mountainbikes, Citybikes',
            'meta_description' => 'Take a look at our bikes and find what you are looking for.',
        ],
        'mountainbikes' => [
            'name' => 'Mountainbikes',
            'meta_title' => 'Mountainbikes',
            'meta_description' => 'Take a look at our huge mountainbike range',
        ],
        'citybikes' => [
            'name'                      => 'Citybikes',
            'meta_title'                => 'Citybikes',
            'meta_description'          => 'Take a look at our huge citybike range',
        ],
        'clothing' => [
            'name' => 'Clothing',
            'meta_title' => 'Sports clothes',
            'meta_description' => 'Check out our huge sports clothes range',
        ],
        'gift' => [
            'name' => 'Gift cards',
            'meta_title' => 'Gift cards',
            'meta_description' => 'Order your Mall gift card online',
        ],
    ],
    'customer_groups' => [
        'silver' => [
            'name' => 'Silver-Partner',
        ],
        'gold' => [
            'name' => 'Gold-Partner',
        ],
        'diamond' => [
            'name' => 'Diamond-Partner',
        ],
    ],
    'customers' => [
        'normal' => 'Normal Customer',
        'gold' => 'Gold Customer',
        'diamond' => 'Diamond Customer',
    ],
    'notifications' => [
        'admin_checkout_succeeded' => [
            'name' => 'Admin notification: Checkout succeeded',
            'description' => 'Sent to the shop admin when a checkout succeeded',
        ],
        'admin_checkout_failed' => [
            'name' => 'Admin notification: Checkout failed',
            'description' => 'Sent to the shop admin when a checkout failed',
        ],
        'customer_created' => [
            'name' => 'Customer signed up',
            'description' => 'Sent when a customer has signed up',
        ],
        'checkout_succeeded' => [
            'name' => 'Checkout succeeded',
            'description' => 'Sent when a checkout was successful',
        ],
        'checkout_failed' => [
            'name' => 'Checkout failed',
            'description' => 'Sent when a checkout has failed',
        ],
        'order_shipped' => [
            'name' => 'Order shipped',
            'description' => 'Sent when the order has been marked as shipped',
        ],
        'order_state_changed' => [
            'name' => 'Order status changed',
            'description' => 'Sent when a order status was updated',
        ],
        'payment_paid' => [
            'name' => 'Payment received',
            'description' => 'Sent when a payment has been received',
        ],
        'payment_failed' => [
            'name' => 'Payment failed',
            'description' => 'Sent when a payment has failed',
        ],
        'payment_refunded' => [
            'name' => 'Payment refunded',
            'description' => 'Sent when a payment has been refunded',
        ],
    ],
    'order_states' => [
        'new' => 'New',
        'in_progress' => 'In Progress',
        'disputed' => 'Disputed',
        'cancelled' => 'Cancelled',
        'complete' => 'Complete',
    ],
    'payment_methods' => [
        'invoice' => 'Invoice',
    ],
    'products' => [
        'cruiser_1000' => [
            'description' => '<p>Find your passion for city biking on Cruisers’ Model 1000. Whether you want to ride to the train or commute to work this is the right bike for you. The aluminium frame is feather light and durable.</p>',
            'description_short' => 'The ideal city bike',
            'meta_title' => 'Cruiser 1000 Citybike',
            'meta_description' => 'Find your passion for city biking on Cruisers’ Model 1000',
            'reviews' => [
                'great_bike' => [
                    'title' => 'Great bike!',
                    'description' => "I've been looking for a bike like this for years!",
                    'pros_01' => 'Nice color',
                    'pros_02' => 'Great price',
                    'cons_01' => 'Assembly is quite complicated',
                ],
            ],
        ],
        'cruiser_1500' => [
            'description' => '<p>Find your passion for city biking on Cruisers’ Model 1500. Whether you want to ride to the train or commute to work this is the right bike for you. The aluminium frame is feather light and durable.</p>',
            'description_short' => 'Think pink',
            'meta_title' => 'Cruiser 1500 Citybike',
            'meta_description' => 'Find your passion for city biking on Cruisers’ Model 1500',
            'reviews' => [
                'title' => 'This bike is for girls',
                'description' => "So I've bought this bike last week and now my friend pointed out, that it is actually a girl's bike. This should be mentioned in the description!",
            ],
        ],
        'cruiser_3000' => [
            'description' => '<p>Find your passion for mountain biking on Cruisers’ Model 3000. Whether you want to ride enduro, all-mountain, or downhill - and there\'s nothing stopping you from trying them all - this fun and friendly Cruiser Suspension Bike will help you climb and descend. This carbon mountain bike comes with fun guaranteed.</p>',
            'description_short' => 'Hard tail bike',
            'meta_title' => 'Cruiser 3000 Mountainbike',
            'meta_description' => 'Whether you want to ride enduro, all-mountain, or downhill this fun and friendly Cruiser Suspension Bike will help you climb and descend.',
        ],
        'cruiser_3500' => [
            'description' => '<p>Find your passion for mountain biking on Cruisers’ Model 3500. Whether you want to ride enduro, all-mountain, or downhill - and there\'s nothing stopping you from trying them all - this fun and friendly Cruiser Suspension Bike will help you climb and descend. This carbon mountain bike comes with fun guaranteed.</p>',
            'description_short' => 'Hard tail bike',
            'meta_title' => 'Cruiser 3500 Mountainbike',
            'meta_description' => 'Whether you want to ride enduro, all-mountain, or downhill this fun and friendly Cruiser Suspension Bike will help you climb and descend.',
        ],
        'cruiser_5000' => [
            'description_short' => 'The ideal beginner bike',
            'description' => '<p>Find your passion for mountain biking on Cruisers’ Model 5000. Whether you want to ride enduro, all-mountain, or downhill - and there\'s nothing stopping you from trying them all - this fun and friendly Cruiser Suspension Bike will help you climb and descend. This carbon mountain bike comes with fun guaranteed.</p>',
            'meta_title' => 'Cruiser 5000 Mountainbike',
            'meta_description' => 'Whether you want to ride enduro, all-mountain, or downhill this fun and friendly Cruiser Suspension Bike will help you climb and descend.',
            'reviews' => [
                'title' => 'Very bad build quality',
                'description' => 'The bike is okay but after a few rides parts started to fall off!',
            ],
        ],
        'gift_card_50' => [
            'name' => '50 € Gift Card',
            'description' => '<p>This is a custom gift card for you to print at home.</p>',
            'description_short' => 'Valid for this store',
            'meta_title' => '50 € Gift Card',
        ],
        'gift_card_100' => [
            'name' => '100 € Gift Card',
            'description' => '<p>This is a custom gift card for you to print at home.</p>',
            'description_short' => 'Valid for this store',
            'meta_title' => '100 € Gift Card',
        ],
        'gift_card_200' => [
            'name' => '200 € Gift Card',
            'description' => '<p>This is a custom gift card for you to print at home.</p>',
            'description_short' => 'Valid for this store',
            'meta_title' => '200 € Gift Card',
        ],
        'jersey' => [
            'name' => 'Stormrider Jersey Men',
            'description' => '<p>The fast-drying and breathable materials of the Stormrider Jersey ensure the perfect balance between durability, abrasion resistance and comfort.</p>',
            'description_short' => 'Polyester',
            'meta_title' => 'Stormrider Jersey Men',
            'variants' => [
                'brg_xs_name' => 'Stormrider Jersey Men black/red/gray XS',
                'brg_s_name' => 'Stormrider Jersey Men black/red/gray S',
                'brg_m_name' => 'Stormrider Jersey Men black/red/gray M',
                'brg_l_name' => 'Stormrider Jersey Men black/red/gray L',
                'bbw_xs_name' => 'Stormrider Jersey Men black/blue/white XS',
                'bbw_s_name' => 'Stormrider Jersey Men black/blue/white S',
                'bbw_m_name' => 'Stormrider Jersey Men black/blue/white M',
                'bbw_l_name' => 'Stormrider Jersey Men black/blue/white L',
                'bbw_xl_name' => 'Stormrider Jersey Men black/blue/white XL',
            ],
        ],
        'red_shirt' => [
            'name' => 'Red Shirt',
            'description' => '<p>This is just a generic shirt. Brandless and cheap.</p>',
            'description_short' => 'Unisex',
            'meta_title' => 'Red Shirt',
            'variants' => [
                's_name' => 'Red Shirt S',
                'm_name' => 'Red Shirt M',
                'l_name' => 'Red Shirt L',
            ],
        ],
        'fields' => [
            'include_bike_assembly' => 'Include bike assembly guide',
        ],
        'images' => [
            'main' => 'Main Images',
            'gift' => 'Gift Card',
            'jersey_red' => 'Jersey red',
            'jersey_blue' => 'Jersey blue',
        ],
        'properties' => [
            'aluminium' => 'Aluminium',
            'carbon' => 'Carbon',
            'cotton' => 'Cotton',
            'polyester' => 'Polyester',
            'male' => 'Male',
            'female' => 'Female',
            'unisex' => 'Unisex',
            'brg' => 'black/red/gray',
            'bbw' => 'black/blue/white',
            'red' => 'Red',
            'devils_red' => "Devil's Red",
            'darker_red' => 'Darker Red',
            'heavens_blue' => "Heaven\'s Blue",
            'dark_grey' => 'Dark Grey',
            'think_pink' => 'Think Pink',
        ],
    ],
    'property_groups' => [
        'height' => 'Height',
        'width' => 'Width',
        'depth' => 'Depth',
        'size' => 'Size',
        'dimensions' => 'Dimensions',
        'bike_specs' => 'Bike Specs',
        'specs' => 'Specifications',
        'gender' => 'Gender',
        'male' => 'Male',
        'female' => 'Female',
        'unisex' => 'Unisex',
        'material' => 'Material',
        'color' => 'Color',
        'bike_size' => 'Bike Sizes',
        'frame_size' => 'Frame size',
        'wheel_size' => 'Wheel size',
        'suspension' => 'Suspension',
        'fork_travel' => 'Fork travel',
        'rear_travel' => 'Rear travel',
        'clothing_specs' => 'Bike Specs',
    ],
    'price_categories' => [
        'old_price_name'     => 'Old Price',
        'old_price_label'    => 'Original Pricing',
        'msrp_price_name'    => 'MSRP',
        'msrp_price_label'   => 'Manufacturer\'s suggested retail price',
    ],
    'review_categories' => [
        'price' => 'Price',
        'design' => 'Design',
        'quality' => 'Build quality',
    ],
    'services' => [
        'warranty' => [
            'name' => 'Warranty',
            'description' => 'You can extend the vendor supplied warranty for this product.',
            '2_years_name' => '2 years extended warranty',
            '2_years_description' => 'Get one additional year of warranty',
            '3_years_name' => '3 years extended warranty',
            '3_years_description' => 'Get two additional years of warranty',
            '4_years_name' => '4 years extended warranty',
            '4_years_description' => 'Get three additional years of warranty',
        ],
        'assembly' => [
            'name' => 'Assembly',
            'description' => "Don't have the right tools at hand? We can preassemble this product for you.",
            'preassemble_name' => 'Preassemble product',
            'preassemble_description' => 'The completely assembled product will be shipped to your doorstep.',
        ],
    ],
    'shipping_methods' => [
        'standard' => 'Standard',
        'express' => 'Express',
    ],
    'taxes' => [
        'standard' => 'Standard',
        'reduced' => 'Reduced',
    ],
];

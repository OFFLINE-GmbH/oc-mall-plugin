<?php
return [
    "category" => [
        "code" => "Code",
        "code_comment" => "This code can be used to identify this category in your frontend partials.",
        "inherit_property_groups" => "Inherit properties of parent category",
        "inherit_property_groups_comment" => "Use the property groups of this category's parent category",
        "inherit_review_categories" => "Inherit review categories of parent category",
        "inherit_review_categories_comment" => "Use the review categories of this category's parent category",
        "name" => "Name",
        "no_parent" => "No parent",
        "parent" => "Parent"
    ],
    "common" => [
        "accessories" => "Accessories",
        "accessory" => "Accessory",
        "action_required" => "Action required!",
        "add_value" => "Add value",
        "address" => "Address",
        "addresses" => "Addresses",
        "allowed" => "Allowed",
        "api_error" => "Could not save discount. Error while sending changes to the Mall API.",
        "approved_at" => "Approved at",
        "attachments" => "Images/Downloads/Embeds",
        "brand" => "Brand",
        "brands" => "Brands",
        "cart" => "Cart",
        "catalogue" => "Catalogue",
        "categories" => "Categories",
        "category" => "Category",
        "caution" => "Caution",
        "checked" => "Checked",
        "code" => "Code",
        "code_comment" => "This code can be used to identify this record programmatically",
        "color" => "Color",
        "conditions" => "Conditions",
        "created_at" => "Created at",
        "custom_fields" => "Custom fields",
        "customer_group" => "Customer group",
        "customer_groups" => "Customer groups",
        "customers" => "Customers",
        "data" => "Data",
        "deleted_at" => "Deleted at",
        "discount" => "Discount",
        "discount_percentage" => "Discount (%)",
        "discounts" => "Discounts",
        "display_name" => "Display name",
        "dont_group" => "-- Do not group",
        "downloads" => "Downloads",
        "export_orders" => "Export orders",
        "failed" => "Failed",
        "feeds" => "Feeds",
        "fees" => "Fees",
        "general" => "General",
        "group_name" => "Group name",
        "hide_published" => "Hide published",
        "id" => "ID",
        "image" => "Image",
        "image_set" => "Image set",
        "images" => "Images",
        "includes_tax" => "Including taxes",
        "invalid_quantity" => "The specified quantity is not valid",
        "inventory" => "Inventory",
        "logo" => "Logo",
        "main_image" => "Main image",
        "message" => "Message",
        "meta_description" => "Meta description",
        "meta_keywords" => "Meta keywords",
        "meta_title" => "Meta title",
        "name" => "Name",
        "no" => "No",
        "none" => "-- None",
        "not_allowed" => "Not allowed",
        "not_in_use" => "Option is not in use",
        "notification" => "Notification",
        "notifications" => "Notifications",
        "old_price" => "Old price",
        "option" => "Option",
        "options" => "Options",
        "order_states" => "Order states",
        "orders" => "Orders",
        "out_of_stock" => "This product is out of stock.",
        "out_of_stock_short" => "Out of stock",
        "payment" => "Payment",
        "payment_gateway" => "Payment gateway",
        "payment_method" => "Payment method",
        "payment_methods" => "Payment methods",
        "payment_provider" => "Payment provider",
        "payments" => "Payments",
        "price_missing" => "Enter at least a price for the default currency",
        "product" => "Product",
        "product_or_variant" => "Product/Variant",
        "products" => "Products",
        "properties" => "Properties",
        "properties_links" => "Properties/Links",
        "property" => "Property",
        "property_group" => "Property group",
        "property_groups" => "Property groups",
        "rates" => "Rates",
        "reference" => "Reference",
        "reorder" => "Reorder entries",
        "review" => "Review",
        "review_categories" => "Review categories",
        "review_category" => "Review category",
        "reviews" => "Reviews",
        "saved_changes" => "Saved changes successfully",
        "select_file" => "Choose file",
        "select_image" => "Choose image",
        "select_placeholder" => "-- Please choose",
        "seo" => "SEO",
        "service" => "Service",
        "services" => "Services",
        "session_id" => "Session ID",
        "shipping" => "Shipping",
        "shipping_methods" => "Shipping methods",
        "shop" => "Shop",
        "since_begin" => "Since begin",
        "slug" => "URL",
        "slug_unique" => "The URL has to be unique",
        "sort_order" => "Sort order",
        "sorting_updated" => "Sort order has been updated",
        "stock_limit_reached" => "You cannot add any more items of this product to your cart since the stock limit has been reached.",
        "successful" => "Successful",
        "taxes" => "Taxes",
        "title" => "Title",
        "unchecked" => "Unchecked",
        "unit" => "Unit",
        "updated_at" => "Updated at",
        "use_backend_defaults" => "Use defaults configured in backend settings",
        "value" => "Value",
        "variant" => "Variant",
        "variants" => "Variants",
        "version" => "Version",
        "website" => "Website",
        "weekly" => "Weekly",
        "yes" => "Yes"
    ],
    "components" => [
        "addressForm" => [
            "details" => [
                "description" => "Displays a form to edit a user's address",
                "name" => "Address form"
            ],
            "properties" => [
                "address" => ["title" => "Address"],
                "redirect" => ["title" => "Redirect (after save)"],
                "set" => ["title" => "Use this address as"]
            ],
            "redirects" => ["checkout" => "Checkout page"],
            "set" => ["billing" => "Billing address", "shipping" => "Shipping address"]
        ],
        "addressList" => [
            "details" => [
                "description" => "Displays a list of all registered user addresses",
                "name" => "Address list"
            ],
            "errors" => [
                "address_not_found" => "The requested address could not be found",
                "cannot_delete_last_address" => "You cannot delete your last address"
            ],
            "messages" => ["address_deleted" => "Address deleted"]
        ],
        "addressSelector" => [
            "details" => [
                "description" => "Displays a list of all existing user addresses",
                "name" => "Address selector"
            ]
        ],
        "cart" => [
            "details" => ["description" => "Displays the shopping cart", "name" => "Cart"],
            "properties" => [
                "showDiscountApplier" => ["title" => "Show discount applier"],
                "showProceedToCheckoutButton" => ["title" => "Show proceed to checkout button"],
                "showShipping" => ["title" => "Show shipping cost"],
                "showTaxes" => ["title" => "Show taxes"]
            ]
        ],
        "cartSummary" => [
            "details" => [
                "description" => "Displays the number of products in and total value of the cart",
                "name" => "Cart summary"
            ],
            "properties" => [
                "showItemCount" => [
                    "description" => "Displays the count of items in the cart",
                    "title" => "Show product count"
                ],
                "showTotalPrice" => [
                    "description" => "Displays the total value of all items in the cart",
                    "title" => "Show total value"
                ]
            ]
        ],
        "categories" => [
            "by_slug" => "Use category in url as parent",
            "details" => ["description" => "Lists available categories", "name" => "Categories"],
            "no_parent" => "Show all categories",
            "properties" => [
                "categoryPage" => [
                    "description" => "Links will point to this page. If nothing is entered the default settings from the backend settings will be used.",
                    "title" => "Category page"
                ],
                "categorySlug" => [
                    "description" => "Use this parameter to load the parent category from the url",
                    "title" => "Category slug parameter"
                ],
                "parent" => [
                    "description" => "Only show child categories of this category",
                    "title" => "Start from category"
                ]
            ]
        ],
        "category" => [
            "by_slug" => "",
            "no_parent" => "",
            "properties" => [
                "categoryPage" => ["description" => "", "title" => ""],
                "categorySlug" => ["description" => "", "title" => ""],
                "parent" => ["description" => "", "title" => ""]
            ]
        ],
        "checkout" => [
            "details" => ["description" => "Handles the checkout process", "name" => "Checkout"],
            "errors" => ["missing_settings" => "Please select a payment and shipping method."]
        ],
        "currencyPicker" => [
            "details" => [
                "description" => "Shows a picker to select the currently active shop currency",
                "name" => "Currency picker"
            ]
        ],
        "customerDashboard" => [
            "details" => [
                "description" => "Displays a link for the customer to login and change her account settings",
                "name" => "Customer dashboard"
            ],
            "properties" => [
                "customerDashboardLabel" => [
                    "description" => "Link text for the customer account page",
                    "title" => "Customer dashboard label"
                ],
                "logoutLabel" => ["description" => "Link text for the logout link", "title" => "Logout label"]
            ]
        ],
        "customerProfile" => [
            "details" => [
                "description" => "Displays a customer profile edit form.",
                "name" => "Customer profile"
            ]
        ],
        "dependencies" => [
            "details" => [
                "description" => "Includes all needed frontend dependencies",
                "name" => "Frontend dependencies"
            ]
        ],
        "discountApplier" => [
            "details" => [
                "description" => "Displays a promo code input field",
                "name" => "Promo code input"
            ],
            "discount_applied" => "Discount applied successfully!"
        ],
        "enhancedEcommerceAnalytics" => [
            "details" => [
                "description" => "Implements a Google Tag Manager Data Layer",
                "name" => "Enhanced Ecommerce (UA) Component"
            ]
        ],
        "myAccount" => [
            "details" => [
                "description" => "Displays different forms where a user can view and edit his profile",
                "name" => "User account"
            ],
            "pages" => ["addresses" => "Addresses", "orders" => "Orders", "profile" => "Profile"],
            "properties" => ["page" => ["title" => "Active subpage"]]
        ],
        "ordersList" => [
            "details" => [
                "description" => "Displays a list of all customer orders",
                "name" => "Orders list"
            ]
        ],
        "paymentMethodSelector" => [
            "details" => [
                "description" => "Displays a list of all available payment methods",
                "name" => "Payment method selector"
            ],
            "errors" => ["unavailable" => "The selected payment method is not available for your order."]
        ],
        "product" => [
            "added_to_cart" => "Added product successfully",
            "details" => ["description" => "Displays details of a product", "name" => "Product details"],
            "properties" => [
                "redirectOnPropertyChange" => [
                    "description" => "Redirect the user to the new detail page if a property was changed",
                    "title" => "Redirect on property change"
                ]
            ]
        ],
        "productReviews" => [
            "details" => ["description" => "Displays all reviews of a product", "name" => "Product reviews"],
            "properties" => [
                "currentVariantReviewsOnly" => [
                    "description" => "Don't show reviews of other Variants of this Product",
                    "title" => "Show only ratings of this Variant"
                ],
                "perPage" => ["title" => "Number of reviews per page"]
            ]
        ],
        "products" => [
            "details" => ["description" => "Displays a list of products", "name" => "Products"],
            "properties" => [
                "filter" => ["description" => "Forced filter for this component", "title" => "Filter string"],
                "filter_component" => [
                    "description" => "Alias of the ProductsFilter component that filters this Products component",
                    "title" => "Filter component alias"
                ],
                "include_children" => [
                    "description" => "Show all products of child categories as well",
                    "title" => "Include children"
                ],
                "include_variants" => [
                    "description" => "Don't show single products but all available product variants",
                    "title" => "Show article variants"
                ],
                "no_category_filter" => "Don't filter by category",
                "paginate" => [
                    "description" => "Paginate the result (show more than one page)",
                    "title" => "Paginate"
                ],
                "per_page" => ["description" => "How many products to display per page", "title" => "Per page"],
                "set_page_title" => [
                    "description" => "Use the category's name as page title",
                    "title" => "Set page title"
                ],
                "sort" => ["description" => "This overrides the user's sort preference", "title" => "Sort"],
                "use_url" => "Use category slug from URL"
            ]
        ],
        "productsFilter" => [
            "details" => [
                "description" => "Filters the products from a category",
                "name" => "Products filter"
            ],
            "properties" => [
                "includeChildren" => [
                    "description" => "Include properties and filters from products in child categories as well",
                    "title" => "Include children"
                ],
                "includeSliderAssets" => [
                    "description" => "Include all dependencies of noUI Slider via cdnjs",
                    "title" => "Include noUI Slider"
                ],
                "includeVariants" => [
                    "description" => "Show filters for variant properties",
                    "title" => "Include variants"
                ],
                "showBrandFilter" => ["title" => "Show brand filter"],
                "showOnSaleFilter" => ["title" => "Show on sale filter"],
                "showPriceFilter" => ["title" => "Show price filter"],
                "sortOrder" => ["description" => "Initial sort order", "title" => "Sort order"]
            ],
            "sortOrder" => [
                "bestseller" => "Bestseller",
                "latest" => "Latest",
                "manual" => "Manual",
                "name" => "Name",
                "oldest" => "Oldest",
                "priceHigh" => "Highest price",
                "priceLow" => "Lowest price",
                "random" => "Random",
                "ratings" => "Ratings"
            ]
        ],
        "shippingMethodSelector" => [
            "details" => [
                "description" => "Displays a list of all available shipping methods",
                "name" => "Shipping selector"
            ],
            "errors" => ["unavailable" => "The selected shipping method is not available for your order."]
        ],
        "signup" => [
            "details" => ["description" => "Displays a signup and signin form", "name" => "Signup"],
            "errors" => [
                "city" => ["required" => "Please enter a city."],
                "country_id" => [
                    "exists" => "The provided country is not valid.",
                    "required" => "Choose a country."
                ],
                "email" => [
                    "email" => "This email address is invalid.",
                    "non_existing_user" => "A user with this email address is already registered. Use the password reset function.",
                    "required" => "Please enter an email address.",
                    "unique" => "A user with this email address is already registered."
                ],
                "firstname" => ["required" => "Please enter your first name."],
                "lastname" => ["required" => "Please enter your last name."],
                "lines" => ["required" => "Please enter your address."],
                "login" => [
                    "between" => "Please enter a valid email address.",
                    "email" => "Please enter a valid email address.",
                    "required" => "Please enter an email address."
                ],
                "not_activated" => "Your account needs to be activated before you can login.",
                "password" => [
                    "max" => "The provided password is too long.",
                    "min" => "The provided password is too short. Please enter at least 8 characters.",
                    "required" => "Please enter your password."
                ],
                "password_repeat" => [
                    "required" => "Please repeat your password.",
                    "same" => "Your password confirmation does not match your entered password."
                ],
                "state_id" => ["exists" => "The selected value is not valid.", "required" => "Choose a state"],
                "terms_accepted" => ["required" => "Please accept our terms and conditions."],
                "unknown_user" => "The credentials you have entered are invalid.",
                "user_is_guest" => "You are trying to sign in with a guest account.",
                "zip" => ["required" => "Please enter your zip code."]
            ],
            "properties" => ["redirect" => ["name" => "Redirect after login"]]
        ],
        "wishlistButton" => [
            "details" => ["description" => "Displays a wishlist button", "name" => "Wishlist button"],
            "properties" => [
                "product" => ["description" => "ID of the product", "name" => "Product"],
                "variant" => ["description" => "ID of the variant", "name" => "Variant"]
            ]
        ],
        "wishlists" => [
            "details" => ["description" => "Displays the wishlist manager", "name" => "Wishlists"],
            "properties" => [
                "showShipping" => ["description" => "Show shipping cost and selector", "name" => "Show shipping"]
            ]
        ]
    ],
    "currency_settings" => [
        "currencies" => "Only enter official 3-char currency codes.",
        "currency_code" => "Currency code",
        "currency_decimals" => "Decimal places",
        "currency_format" => "Format",
        "currency_rate" => "Rate",
        "currency_symbol" => "Symbol",
        "description" => "Setup your currencies",
        "is_default" => "Is default",
        "label" => "Currencies",
        "unknown" => "Unknown Currency"
    ],
    "custom_field_options" => [
        "add" => "Add option",
        "attributes" => "Attribute",
        "checkbox" => "Checkbox",
        "color" => "Color",
        "dropdown" => "Dropdown",
        "float" => "Float",
        "image" => "Image",
        "integer" => "Integer",
        "name" => "Name",
        "option" => "Option",
        "price" => "Price",
        "richeditor" => "Richtext",
        "text" => "Textfield",
        "textarea" => "Multi-line textfield"
    ],
    "custom_fields" => [
        "is_not_required" => "Not required",
        "is_required" => "Required",
        "name" => "Field name",
        "options" => "Options",
        "required" => "Required",
        "required_comment" => "This field is required to place an order",
        "type" => "Field type"
    ],
    "customer_group" => [
        "code_comment" => "This code can be used to identify this group programmatically",
        "discount_comment" => "Give this customer group a specific discount in % on your whole catalogue"
    ],
    "discounts" => [
        "amount" => "Fixed amount",
        "code" => "Discount code",
        "code_comment" => "Leave empty to generate a random code",
        "expires" => "Expires",
        "max_number_of_usages" => "Max number of usages",
        "name" => "Name",
        "number_of_usages" => "Number of usages",
        "rate" => "Rate (%)",
        "section_trigger" => "When is this discount applicable?",
        "section_type" => "What does this discount do?",
        "shipping_description" => "Name of alternative shipping method",
        "shipping_guaranteed_days_to_delivery" => "Guaranteed days to delivery",
        "shipping_price" => "Price of alternative shipping method",
        "total_to_reach" => "Minimal order total for discount to be valid",
        "trigger" => "Valid if",
        "triggers" => [
            "code" => "Discount code is entered",
            "product" => "A specific product is present in the cart",
            "total" => "Order total is reached"
        ],
        "type" => "Discount type",
        "types" => [
            "fixed_amount" => "Fixed amount",
            "rate" => "Rate",
            "shipping" => "Alternate shipping"
        ],
        "valid_from" => "Valid from",
        "validation" => [
            "duplicate" => "You can use the same promo code only once.",
            "empty" => "Enter a promo code.",
            "expired" => "This promo code has expired.",
            "not_found" => "This promo code is not valid.",
            "shipping" => "You can only apply one promo code that lowers your shipping fees.",
            "usage_limit_reached" => "This promo code has been applied to many times and is therefore no longer valid."
        ]
    ],
    "feed_settings" => [
        "description" => "Configure mall feeds",
        "google_merchant_enabled" => "Enable Google Merchant Center Feed",
        "google_merchant_enabled_comment" => "A product feed will be generated",
        "google_merchant_url" => "Your Google Merchant Feed URL",
        "google_merchant_url_locale" => "Add ?locale=xy to get a localized feed."
    ],
    "general_settings" => [
        "account_page" => "Account page",
        "account_page_comment" => "The myAccount component has to be present on this page",
        "address_page" => "Address page",
        "address_page_comment" => "The addressForm component has to be present on this page",
        "admin_email" => "Admin email",
        "admin_email_comment" => "Admin notifications will be sent to this addres",
        "base" => "Base settings",
        "cart_page" => "Cart page",
        "cart_page_comment" => "The cart component has to be present on this page",
        "category" => "Mall: General",
        "category_orders" => "Mall: Orders",
        "category_page" => "Category page for products listing",
        "category_page_comment" => "Add the \"products\" component to this page.",
        "category_payments" => "Mall: Payments",
        "checkout_page" => "Checkout page",
        "checkout_page_comment" => "The checkout component has to be present on this page",
        "click_and_collect" => [
            "percent" => "Click and collect Rate",
            "percent_comment" => "Percentage to be paid to book products",
            "use_state" => "Enable Click and Collect",
            "use_state_comment" => "Enable or disable the click and collect"
        ],
        "customizations" => "Customizations",
        "customizations_comment" => "Customize the features of your shop",
        "description" => "General settings",
        "group_search_results_by_product" => "Group search results by product",
        "group_search_results_by_product_comment" => "Include a Product only once in the search results, don't display all matching Variants",
        "index_driver" => "Index driver",
        "index_driver_comment" => "If your database supports JSON use the database driver.",
        "index_driver_database" => "Database (only for MySQL 5.7+ or MariaDB 10.2+)",
        "index_driver_filesystem" => "Filesystem",
        "index_driver_hint" => "If you change this option make sure to run \"php artisan mall:reindex\" on the command line to re-index your products!",
        "label" => "Configuration",
        "links" => "CMS pages",
        "links_comment" => "Choose which pages are used to display your products",
        "order_number_start" => "First order number",
        "order_number_start_comment" => "Initial id of the first order",
        "product_page" => "Product details page",
        "product_page_comment" => "This is where the product details are displayed",
        "redirect_to_cart" => "Redirect to cart",
        "redirect_to_cart_comment" => "Redirect to cart after the user added a product",
        "use_state" => "Use State/County/Province field",
        "use_state_comment" => "Customers have to select a State/County/Province during signup"
    ],
    "image_sets" => [
        "create_new" => "Create new set",
        "is_main_set" => "Is main set",
        "is_main_set_comment" => "Use this image set for this product"
    ],
    "menu_items" => [
        "all_categories" => "All shop categories",
        "all_products" => "All shop products",
        "all_variants" => "All shop variants",
        "single_category" => "Single shop category"
    ],
    "notification_settings" => ["description" => "Configure store notifications", "label" => "Notifications"],
    "notifications" => [
        "enabled" => "Enabled",
        "enabled_comment" => "This notification is enabled",
        "template" => "Mail template"
    ],
    "order" => [
        "adjusted_amount" => "Adjusted amount",
        "billing_address" => "Billing address",
        "card_holder_name" => "Card holder",
        "card_type" => "Cart type",
        "change_order_status" => "Change order status",
        "change_payment_status" => "Change payment status",
        "completion_date" => "Completed at",
        "creation_date" => "Created at",
        "credit_card" => "Credit card",
        "credit_card_last4_digits" => "Last 4 digits",
        "currency" => "Currency",
        "custom_fields" => "Custom fields",
        "customer" => "Customer",
        "data" => "Order data",
        "delete_confirm" => "Do you really want to delete this order?",
        "deleted" => "Order successfully deleted",
        "deleting" => "Deleting order...",
        "download_invoice" => "Download invoice",
        "email" => "Email",
        "grand_total" => "Grand total",
        "invalid_status" => "The selected status does not exist.",
        "invoice_number" => "# Invoice",
        "items" => "Items",
        "items_total" => "Items total",
        "lang" => "Language",
        "modal" => ["cancel" => "Cancel", "update" => "Update information"],
        "modification_date" => "Modified at",
        "not_shipped" => "Pending",
        "notes" => "Notes",
        "order_number" => "# Order",
        "payment_gateway_used" => "Payment gateway",
        "payment_hash" => "Payment hash",
        "payment_method" => "Payment method",
        "payment_states" => [
            "failed_state" => "Payment failed",
            "paid_state" => "Paid",
            "pending_state" => "Payment pending",
            "refunded_state" => "Payment refunded"
        ],
        "payment_status" => "Payment status",
        "payment_transaction_id" => "Payment transaction id",
        "quantity" => "Quantity",
        "rebate_amount" => "Rebate amount",
        "refunds_amount" => "Refunds amount",
        "shipped" => "Shipped",
        "shipping_address" => "Shipping address",
        "shipping_address_is_same_as_billing" => "Shipping address is same as billing address",
        "shipping_address_same_as_billing" => "Shipping address is same as billing",
        "shipping_enabled" => "Shipping enabled",
        "shipping_fees" => "Shipping fees",
        "shipping_method" => "Shipping method",
        "shipping_pending" => "Shipping pending",
        "shipping_provider" => "Shipping provider",
        "status" => "Status",
        "subtotal" => "Subtotal",
        "tax_provider" => "Tax provider",
        "taxable_total" => "Taxable total",
        "taxes_total" => "Taxes total",
        "total" => "Total",
        "total_rebate_rate" => "Total rebate",
        "total_revenue" => "Total revenue",
        "total_weight" => "Total weight",
        "tracking_completed" => "Mark order as complete",
        "tracking_completed_comment" => "The order will be marked as complete",
        "tracking_notification" => "Send notification",
        "tracking_notification_comment" => "A notification containing the tracking information will be sent to the customer",
        "tracking_number" => "Tracking number",
        "tracking_shipped" => "Mark order as shipped",
        "tracking_shipped_comment" => "The order will be marked as shipped",
        "tracking_url" => "Tracking url",
        "update_invoice_number" => "Set invoice number",
        "update_shipping_state" => "Update shipping state",
        "updated" => "Order update successful",
        "will_be_paid_later" => "Will be paid later"
    ],
    "order_state_settings" => ["description" => "Configure order states"],
    "order_states" => [
        "color" => "Color",
        "description" => "Description",
        "flag" => "Special flag",
        "flags" => [
            "cancelled" => "Set the state of the order as \"cancelled\"",
            "complete" => "Set the state of the order as \"done\"",
            "new" => "Set the state of the order as \"new\""
        ],
        "name" => "Name"
    ],
    "order_status" => [
        "cancelled" => "Cancelled",
        "delivered" => "Delivered",
        "disputed" => "Disputed",
        "pending" => "Pending",
        "processed" => "Processed",
        "shipped" => "Shipped"
    ],
    "payment_gateway_settings" => [
        "description" => "Configure your payment gateways",
        "label" => "Payment gateways",
        "paypal" => [
            "client_id" => "PayPal Client ID",
            "secret" => "PayPal Secret",
            "test_mode" => "Test mode",
            "test_mode_comment" => "Run all payments in the PayPal Sandbox."
        ],
        "postfinance" => [
            "hashing_method" => "Hash algorithm",
            "hashing_method_comment" => "Configuration -> Technical information -> Global security parameters",
            "pspid" => "PSPID (Username)",
            "sha_in" => "SHA-IN Signature",
            "sha_in_comment" => "Configuration -> Technical information -> Data and origin verification",
            "sha_out" => "SHA-OUT Signature",
            "sha_out_comment" => "Configuration -> Technical information -> Transaction feedback",
            "test_mode" => "Test mode",
            "test_mode_comment" => "Run all payments against the test environment"
        ],
        "stripe" => [
            "api_key" => "Stripe API Key",
            "api_key_comment" => "You can find this key in your Stripe Dashboard",
            "publishable_key" => "Stripe Publishable Key",
            "publishable_key_comment" => "You can find this key in your Stripe Dashboard"
        ]
    ],
    "payment_log" => [
        "code_comment" => "This code has been returned by the payment provider",
        "data_comment" => "This data has been returned by the payment provider",
        "failed_only" => "Failed only",
        "message_comment" => "This message has been returned by the payment provider",
        "order_data_comment" => "This is all the order data for this payment",
        "payment_data" => "Payment data"
    ],
    "payment_method" => [
        "fee_label" => "Fee label",
        "fee_label_comment" => "This text will be displayed to the customer when checking out.",
        "fee_percentage" => "Percentage fee",
        "fee_percentage_comment" => "The percentage of the total to add to the order's total",
        "instructions" => "Payment instructions",
        "instructions_comment" => "Twig syntax supported. Use {{ order }} or {{ cart }} to access corresponding information if available",
        "pdf_partial" => "PDF attachment partial",
        "pdf_partial_comment" => "For all orders with this payment method a rendered PDF of the selected partial will be attached to the notification mail",
        "pdf_partial_none" => "No PDF attachment",
        "price" => "Fixed fee",
        "price_comment" => "The amount to add to the order's total"
    ],
    "payment_method_settings" => ["description" => "Manage payment methods"],
    "payment_status" => [
        "cancelled" => "Cancelled",
        "charged_back" => "Charged back",
        "deferred" => "Deferred",
        "expired" => "Expired",
        "failed" => "Failed",
        "open" => "Open",
        "paid" => "Paid",
        "paid_deferred" => "Paid deferred",
        "paiddeferred" => "Paid deferred",
        "paidout" => "Paidout",
        "pending" => "Pending",
        "refunded" => "Refunded"
    ],
    "permissions" => [
        "manage_categories" => "Can manage categories",
        "manage_customer_addresses" => "Can manage customer addresses",
        "manage_customer_groups" => "Can manage customer groups",
        "manage_discounts" => "Can manage discounts",
        "manage_feeds" => "Can manage feeds",
        "manage_notifications" => "Can manage notifications",
        "manage_order_states" => "Can manage order states",
        "manage_orders" => "Can manage orders",
        "manage_payment_log" => "Can manage payment log",
        "manage_price_categories" => "Can manage price categories",
        "manage_products" => "Can manage products",
        "manage_properties" => "Can edit product properties",
        "manage_reviews" => "Can manage reviews",
        "manage_services" => "Can manage services",
        "manage_shipping_methods" => "Can manage shipping methods",
        "manage_taxes" => "Can manage taxes",
        "manage_wishlists" => "Can manage wishlists",
        "settings" => [
            "manage_currency" => "Can change currency shop settings",
            "manage_general" => "Can change general shop settings",
            "manage_payment_gateways" => "Can change payment gateway settings",
            "manage_payment_methods" => "Can change payment methods"
        ]
    ],
    "plugin" => ["description" => "E-commerce solution for October CMS", "name" => "Mall"],
    "price_category_settings" => [
        "description" => "Configure additional price categories",
        "label" => "Price categories"
    ],
    "product" => [
        "add_currency" => "Add currency",
        "additional_descriptions" => "Additional descriptions",
        "additional_properties" => "Additional properties",
        "allow_out_of_stock_purchases" => "Allow out of stock purchases",
        "allow_out_of_stock_purchases_comment" => "This product can be ordered even if it is out of stock",
        "currency" => "Currency",
        "description" => "Description",
        "description_short" => "Short description",
        "details" => "Details",
        "duplicate_currency" => "You have entered multiple prices for the same currency",
        "embed_code" => "Embed code",
        "embed_title" => "Title",
        "embeds" => "Embeds",
        "filter_virtual" => "Show only virtual products",
        "general" => "General",
        "group_by_property" => "Attribute for variant grouping",
        "gtin" => "Global Trade Item Number (GTIN)",
        "height" => "Height (mm)",
        "inventory_management_method" => "Inventory management method",
        "is_not_taxable" => "Use no tax",
        "is_taxable" => "Use tax",
        "is_virtual" => "Is virtual",
        "is_virtual_comment" => "This product is virtual (a file, no shipping)",
        "length" => "Length (mm)",
        "link_target" => "Target URL",
        "link_title" => "Title",
        "links" => "Links",
        "missing_category" => "The product does not have a category associated with it. Please select a category below to edit this product.",
        "mpn" => "Manufacturer Part Number (MPN)",
        "name" => "Product name",
        "not_published" => "Not published",
        "price" => "Price",
        "price_includes_tax" => "Price includes taxes",
        "price_includes_tax_comment" => "The defined price includes all taxes",
        "price_table_modal" => [
            "currency_dropdown" => "Currency: ",
            "label" => "Price and stock",
            "title" => "Price and stock overview",
            "trigger" => "Edit stock and price values"
        ],
        "product_file" => "Product file",
        "product_file_version" => "file version",
        "product_files" => "Product files",
        "product_files_section_comment" => "This is a virtual product. You can upload new file versions below. The latest version will be downloadable by customers.",
        "properties" => "Properties",
        "property_title" => "Title",
        "property_value" => "Value",
        "published" => "Published",
        "published_comment" => "This product is visible on the website",
        "published_short" => "Publ.",
        "quantity_default" => "Default quantity",
        "quantity_max" => "Maximum quantity",
        "quantity_min" => "Minimum quantity",
        "shippable" => "Shippable",
        "shippable_comment" => "This product can be shipped",
        "stackable" => "Stack in cart",
        "stackable_comment" => "If this product is added to the cart multiple times only show one entry (increase quantity)",
        "stock" => "Stock",
        "taxable" => "Taxable",
        "taxable_comment" => "Calculate taxes on this product",
        "user_defined_id" => "Product ID",
        "variant_support_header" => "Variants not supported",
        "variant_support_text" => "The selected category has no Variant properties defined. Please switch the inventory management method to \"Article\" or select another category.",
        "weight" => "Weight (g)",
        "width" => "Width (mm)"
    ],
    "product_file" => [
        "display_name_comment" => "This name will be visible to the customer.",
        "download_count" => "Download count",
        "errors" => [
            "expired" => "Download link expired",
            "invalid" => "Invalid download link",
            "not_found" => "Cannot find requested file, please contact us for support.",
            "too_many_attempts" => "Too many download attempts"
        ],
        "expires_after_days" => "Download valid for days",
        "expires_after_days_comment" => "The file can only be downloaded for this many days after purchase. Leave empty for no limit.",
        "file" => "File",
        "hint" => [
            "info_link" => "in the documentation",
            "info_text" => "You can find information on how to do this",
            "intro" => "This product does not have a file attached. Please make sure to add one or programmatically gerenate it during checkout."
        ],
        "max_download_count" => "Maximum number of downloads",
        "max_download_count_comment" => "The file can only be downloaded this many times. Leave empty for no limit.",
        "session_required" => "Login required",
        "session_required_comment" => "The file can only be downloaded when the customer is logged in (download link is not shareable).",
        "version_comment" => "A unique version helps a customer to recognize updated files."
    ],
    "products" => ["variants_comment" => "Create different variants of the same product"],
    "properties" => [
        "filter_type" => "Filter type",
        "filter_types" => ["none" => "Without filter", "range" => "Range", "set" => "Set"],
        "use_for_variants" => "Use for variants",
        "use_for_variants_comment" => "This property is different for different variants of this product"
    ],
    "review_settings" => [
        "allow_anonymous" => "Allow anonymous reviews",
        "allow_anonymous_comment" => "Unregistered users can create reviews",
        "description" => "Configure reviews",
        "enabled" => "Reviews enabled",
        "enabled_comment" => "Customers can create reviews",
        "moderated" => "Moderate reviews",
        "moderated_comment" => "New reviews have to be published manually by the site admin"
    ],
    "reviews" => [
        "anonymous" => "Anonymous",
        "approve_next" => "Approve and go to next",
        "cons" => "Negative aspects",
        "no_more" => "No more unapproved reviews",
        "only_unapproved" => "Show only unapproved",
        "pros" => "Positive aspects",
        "rating" => "Rating",
        "review" => "Review details",
        "title" => "Title of your review"
    ],
    "services" => [
        "option" => "Option",
        "options" => "Options",
        "required" => "Service is required",
        "required_comment" => "One option of this service has to be selected when a product is added to the cart."
    ],
    "shipping_method" => [
        "available_above_total" => "Available if total is greater than or equals",
        "available_below_total" => "Available if total is lower than",
        "countries" => "Available for shipping to these countries",
        "countries_comment" => "If no country is selected this method is available worldwide.",
        "guaranteed_delivery_days" => "Guaranteed delivery in days",
        "not_required_description" => "The current cart does not require any shipping.",
        "not_required_name" => "No shipping required"
    ],
    "shipping_method_rates" => ["from_weight" => "From (Weight in grams)", "to_weight" => "To (Weight in grams)"],
    "shipping_method_settings" => ["description" => "Manage shipping methods"],
    "tax" => [
        "countries" => "Only apply tax when shipping to these countries",
        "countries_comment" => "If no country is selected the tax is applied worldwide.",
        "is_default" => "Is default",
        "is_default_comment" => "This tax is used if the shipping destination country is not known yet",
        "percentage" => "Percent"
    ],
    "tax_settings" => ["description" => "Manage taxes"],
    "titles" => [
        "brands" => ["create" => "Create brand", "edit" => "Edit brand"],
        "categories" => [
            "create" => "Create category",
            "preview" => "Category preview",
            "update" => "Edit category"
        ],
        "custom_field_options" => ["edit" => "Edit field options"],
        "customer_groups" => ["create" => "Create group", "update" => "Edit group"],
        "discounts" => [
            "create" => "Create discount",
            "preview" => "Preview discount",
            "update" => "Edit discount"
        ],
        "notifications" => ["update" => "Update notification"],
        "order_states" => [
            "create" => "Create status",
            "edit" => "Edit status",
            "reorder" => "Reorder status"
        ],
        "orders" => ["export" => "Export orders", "show" => "Order details"],
        "payment_methods" => [
            "create" => "Create payment method",
            "edit" => "Edit payment method",
            "reorder" => "Reorder"
        ],
        "products" => [
            "create" => "Create product",
            "preview" => "Preview product",
            "update" => "Edit product"
        ],
        "properties" => ["create" => "Create properites", "edit" => "Edit properties"],
        "property_groups" => ["create" => "Create group", "edit" => "Edit group"],
        "reviews" => ["create" => "Create review", "update" => "Edit review"],
        "services" => ["create" => "Create service", "update" => "Edit service"],
        "shipping_methods" => [
            "create" => "Create shipping method",
            "preview" => "Preview shipping method",
            "update" => "Edit shipping method"
        ],
        "taxes" => ["create" => "Create tax", "update" => "Edit tax"]
    ],
    "variant" => ["method" => ["single" => "Article", "variant" => "Article variants"]]
];

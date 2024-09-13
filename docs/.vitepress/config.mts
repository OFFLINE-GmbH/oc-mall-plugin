import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Mall OctoberCMS Plugin",
    description: "The all-inclusive e-commerce solution for OctoberCMS.",
    base: '/oc-mall-plugin/',
    vite: {
        server: {
            port: 3003
        }
    },
    // https://vitepress.dev/reference/default-theme-config
    themeConfig: {
        logo: {
            light: 'mall-logo-light.svg',
            dark: 'mall-logo-dark.svg'
        },
        siteTitle: false,
        editLink: {
            pattern: 'https://github.com/OFFLINE-GmbH/oc-mall-plugin/tree/next/docs/:path',
            text: 'Edit this page on GitHub'
        },
        lastUpdated: {
            text: 'Last updated'
        },
        externalLinkIcon: true,
        search: {
            provider: 'local'
        },
        nav: [
            { text: 'Home', link: '/' },
            { text: 'Guide', link: '/guide/', activeMatch: '/guide/' },
            { text: 'Development', link: '/development/', activeMatch: '/development/' }
        ],
        sidebar: {
            '/guide/': [
                {
                    text: 'Introduction',
                    collapsed: false,
                    items: [
                        { text: 'Getting Started', link: '/guide/' },
                        { text: 'Installation', link: '/guide/introduction/installation' },
                        { text: 'Configuration', link: '/guide/introduction/configuration' }
                    ]
                },
                {
                    text: 'Usage',
                    collapsed: false,
                    items: [
                        { text: 'Properties', link: '/guide/usage/properties' },
                        { text: 'Categories', link: '/guide/usage/categories' },
                        { text: 'Products & Variants', link: '/guide/usage/products' },
                        { text: 'Virtual Products', link: '/guide/usage/virtual-products' },
                        { text: 'Reviews', link: '/guide/usage/reviews' },
                        { text: 'Services', link: '/guide/usage/services' },
                        { text: 'Currencies', link: '/guide/usage/currencies' },
                        { text: 'Taxes', link: '/guide/usage/taxes' },
                        { text: 'Payment Methods', link: '/guide/usage/payment-methods' },
                        { text: 'Shipping Methods', link: '/guide/usage/shipping-methods' },
                        { text: 'Analytics', link: '/guide/usage/analytics' },
                        { text: 'Going Live', link: '/guide/usage/going-live' },
                    ]
                },
                {
                    text: 'Components',
                    collapsed: true,
                    items: [
                        { text: 'MallDependencies', link: '/guide/components/mall-dependencies' },
                        { text: 'Product', link: '/guide/components/product' },
                        { text: 'Products', link: '/guide/components/products' },
                        { text: 'ProductsFilter', link: '/guide/components/products-filter' },
                        { text: 'ProductReviews', link: '/guide/components/product-reviews' },
                        { text: 'Cart', link: '/guide/components/cart' },
                        { text: 'DiscountApplier', link: '/guide/components/discount-applier' },
                        { text: 'Checkout', link: '/guide/components/checkout' },
                        { text: 'QuickCheckout', link: '/guide/components/quick-checkout' },
                        { text: 'SignUp', link: '/guide/components/sign-up' },
                        { text: 'Wishlists', link: '/guide/components/wishlists' },
                        { text: 'WishlistButton', link: '/guide/components/wishlist-button' },
                        { text: 'PaymentMethodSelector', link: '/guide/components/payment-method-selector' },
                        { text: 'ShippingMethodSelector', link: '/guide/components/shipping-method-selector' },
                        { text: 'AddressSelector', link: '/guide/components/address-selector' },
                        { text: 'MyAccount', link: '/guide/components/my-account' },
                        { text: 'OrdersList', link: '/guide/components/orders-list' },
                        { text: 'AddressForm', link: '/guide/components/address-form' },
                        { text: 'CurrencyPicker', link: '/guide/components/currency-picker' },
                        { text: 'CustomerProfile', link: '/guide/components/customer-profile' },
                        { text: 'AddressList', link: '/guide/components/address-list' },
                    ]
                }
            ],
            '/development/': [
                {
                    text: 'Working with Mall',
                    collapsed: false,
                    items: [
                        { text: 'Product Model', link: '/development/core/product-model' },
                        { text: 'Variant Model', link: '/development/core/variant-model' },
                        { text: 'Order Model', link: '/development/core/order-model' },
                        { text: 'Cart Model', link: '/development/core/cart-model' },
                        { text: 'Wishlist Model', link: '/development/core/wishlist-model' },
                        { text: 'Payment Providers', link: '/development/core/payment-providers' },
                        { text: 'Pricing Information', link: '/development/core/pricing-information' },
                        { text: 'Price Bag', link: '/development/core/price-bag' },
                        { text: 'Events', link: '/development/core/events' },
                        { text: 'PDF', link: '/development/core/pdf' },
                        { text: 'Integration', link: '/development/core/integration' },
                        { text: 'Artisan Console', link: '/development/core/console-commands' },
                    ]
                },
                {
                    text: 'Extending Mall',
                    collapsed: false,
                    items: [
                        { text: 'Unit Testing', link: '/development/extend/unit-testing' },
                    ]
                },
            ],
        },
        socialLinks: [
            { 
                icon: {
                    svg: '<svg width="100%" height="100%" viewBox="0 0 9 9" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" fill="currentColor"><path d="M7.42,4.068c-0.13,-2.124 -2.444,-3.962 -3.397,-3.983c-0.953,-0.02 -2.735,2.23 -2.808,4.515c-0.067,2.144 1.762,3.614 2.549,3.8c0.022,-1.008 0.103,-4.721 0.117,-5.154c0.028,-0.885 0.072,-2.261 0.219,-2.307c0.085,-0.026 0.201,0.748 0.186,1.205c-0.003,0.087 -0.004,0.37 -0.004,0.771c0.16,-0.162 0.326,-0.331 0.428,-0.43c0.277,-0.273 0.4,-0.24 0.267,0.003c-0.082,0.149 -0.435,0.647 -0.694,1.008c0.002,0.727 0.005,1.642 0.008,2.495c0.366,-0.429 0.814,-0.942 1.05,-1.171c0.494,-0.478 0.546,-0.418 0.313,0.085c-0.143,0.307 -0.843,1.129 -1.36,1.776c0.003,0.757 0.006,1.393 0.006,1.697c1.386,-0.305 3.248,-2.224 3.12,-4.31" /></svg>'
                },
                link: 'https://octobercms.com/plugin/offline-mall'
            },
            { 
                icon: 'github', 
                link: 'https://github.com/OFFLINE-GmbH/oc-mall-plugin'
            }
        ]
    }
})

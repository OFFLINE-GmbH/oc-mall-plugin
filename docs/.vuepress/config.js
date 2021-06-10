module.exports = {
    base: '/oc-mall-plugin/',
    title: 'oc-mall',
    description: 'E-commerce solution for October CMS',
    markdown: {
        lineNumbers: true,
        anchor: {permalink: true, permalinkBefore: true, permalinkSymbol: '#'}
    },
    themeConfig: {
        sidebar: [
            {
                title: 'Installation',
                children: [
                    '/getting-started/installation',
                    '/getting-started/theme-setup',
                ]
            },
            {
                title: 'Digging deeper',
                children: [
                    '/digging-deeper/properties',
                    '/digging-deeper/categories',
                    '/digging-deeper/products',
                    '/digging-deeper/virtual-products',
                    '/digging-deeper/reviews',
                    '/digging-deeper/services',
                    '/digging-deeper/currencies',
                    '/digging-deeper/shipping-methods',
                    '/digging-deeper/payments',
                    '/digging-deeper/taxes',
                    '/digging-deeper/analytics',
                    '/digging-deeper/going-live',
                ]
            },
            {
                title: 'Components',
                children: [
                    '/components/mall-dependencies',
                    '/components/product',
                    '/components/products',
                    '/components/products-filter',
                    '/components/product-reviews',
                    '/components/cart',
                    '/components/discount-applier',
                    '/components/checkout',
                    '/components/quick-checkout',
                    '/components/signup',
                    '/components/wishlists',
                    '/components/wishlist-button',
                    '/components/payment-method-selector',
                    '/components/shipping-method-selector',
                    '/components/address-selector',
                    '/components/my-account',
                    '/components/orders-list',
                    '/components/address-form',
                    '/components/currency-picker',
                    '/components/customer-profile',
                    '/components/address-list',
                ]
            },
            {
                title: 'Development',
                children: [
                    '/development/product-model',
                    '/development/variant-model',
                    '/development/order-model',
                    '/development/cart-model',
                    '/development/wishlist-model',
                    '/development/payment-providers',
                    '/development/pricing-information',
                    '/development/events',
                    '/development/pdf',
                    '/development/integration',
                    '/development/console-commands',
                ]
            },
            {
                title: 'Changelog',
                children: [
                    '/changelog/1.14.0',
                    '/changelog/1.13.0',
                    '/changelog/1.12.0',
                    '/changelog/1.11.0',
                    '/changelog/1.10.0',
                    '/changelog/1.9.0',
                    '/changelog/1.8.0',
                    '/changelog/1.7.0',
                    '/changelog/1.6.0',
                    '/changelog/1.5.0',
                ]
            }
        ],
        repo: 'OFFLINE-GmbH/oc-mall-plugin',
        nav: [
            {text: 'Guide', link: '/'},
            {text: 'Marketplace', link: 'https://octobercms.com/plugin/offline-mall'},
        ],
        docsRepo: 'OFFLINE-GmbH/oc-mall-plugin',
        docsDir: 'docs',
        docsBranch: 'develop',
        editLinks: true,
        editLinkText: 'Help us improve this page!',
    }
}

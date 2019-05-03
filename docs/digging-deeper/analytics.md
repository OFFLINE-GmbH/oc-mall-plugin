# Analytics

## Google Merchant Center Feed

The plugin provides an easy to use Google Merchant Center integration.

You can enable a Google Merchant Center Feed that generates an XML 
feed of your shop's inventory by visiting the `Backend Settings -> Feeds` page.

You can add the generated url as a feed in your 
[Google Merchant Center](https://merchants.google.com/) and sync all 
products automatically.

## Enhanced Ecommerce (Google Tag Manager)

The plugin provides full data layer integration of the
[Universal Analytics Enhanced Ecommerce features using Google Tag Manager](https://developers.google.com/tag-manager/enhanced-ecommerce) 

To enable the integration simply, add the `[enhancedEcommerceAnalytics]` component 
to the `head` section of your layouts. It's important that you place
it before your `gtm.js` script.

```twig
description = "My Layout"

[enhancedEcommerceAnalytics]
==
<!doctype html>
<html lang="en">
<head>
    <!-- ... -->
    
    {% component 'enhancedEcommerceAnalytics' %}
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-XXXXX');</script>
    <!-- End Google Tag Manager -->
</head>
```

::: warning
The component does not include the Google Tag Manager (gtm.js).
You have to add the code yourself. 
:::


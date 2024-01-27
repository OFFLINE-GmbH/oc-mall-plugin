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

### Creating Tags in Google Tag Manager

To measure the ecommerce events in Google Analytics you have to add the following tags in Google Tag Manager:

#### Measuring Product Impressions

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Pageview`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Page View - DOM Ready`
Trigger | `Event` equals `gtm.dom`

::: tip
For measuring product impressions, views of product details, and purchases it is not necessary to implement the same tag for three times. The system distinguishes the events based on the data layer sent.
:::

#### Measuring Product Clicks

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Event`
Category | `Ecommerce`
Action | `Product Click`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Custom Event`
Event name | `productClick`
Trigger | `Event` equals `productClick`

#### Measuring Views of Product Details

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Pageview`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Page View - DOM Ready`
Trigger | `Event` equals `gtm.dom`

#### Measuring Additions or Removals from a Shopping Cart

##### Adding a Product to a Shopping Cart

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Event`
Category | `Ecommerce`
Action | `Add to Cart`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Custom Event`
Event name | `addToCart`
Trigger | `Event` equals `addToCart`

##### Removing a Product from a Shopping Cart

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Event`
Category | `Ecommerce`
Action | `Remove from Cart`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Custom Event`
Event name | `removeFromCart`
Trigger | `Event` equals `removeFromCart`

#### Measuring a Checkout

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Event`
Category | `Ecommerce`
Action | `Checkout`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Custom Event`
Trigger | `Event` equals `checkout`


**Measuring Checkout Options:** Checkout options can currently not be measured segmented.


#### Measuring Purchases

Option | Value
--- | ---
Tag type | `Universal Analytics`
Track type | `Pageview`
Enable Enhanced Ecommerce Features | `true`
Use Data Layer | `true`
Trigger Type | `Page View - DOM Ready`
Trigger | `Event` equals `gtm.dom`

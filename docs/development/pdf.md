# PDF 

Mall bundles the great [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf/)
to generate PDF files.

Refer to it's [README](https://github.com/barryvdh/laravel-dompdf/blob/master/readme.md) for usage details. 

## PDFMaker Trait

Mall provides a [`PDFMaker`](https://github.com/OFFLINE-GmbH/oc-mall-plugin/blob/develop/classes/traits/PDFMaker.php) trait that can be used to generate PDF files.

Currently, the `Order` and  `Wishlist` models implement it by default.

## Configuration

Mall exposes all of the original
 [`barryvdh/laravel-dompdf` config entries](https://github.com/OFFLINE-GmbH/oc-mall-plugin/blob/develop/config/pdf.php)
 in a separate configuration file.

If you wish to change the default PDF configuration create a `config/offline/mall/pdf.php`
file and return your custom config.

See [October's docs on overriding configuration](https://octobercms.com/docs/plugin/settings#file-configuration).
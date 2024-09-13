# Unit Testing

## 1. Install packages

The following dependencies must be installed directly in the root folder of your OctoberCMS 
installation, and __not__ inside the `plugins/offline/mall` plugins root directory.

1. [`fakerphp/faker`](https://packagist.org/packages/fakerphp/faker)
2. [`mockery/mockery`](https://packagist.org/packages/mockery/mockery)

```sh
composer require --dev "fakerphp/faker:^1.23" "mockery/mockery:^1.6"
```

## 2. Run tests

You can either use Octobers `plugin:test OFFLINE.Mall` console command, or you can visit the 
`plugins/offline/mall` plugins root directory and run the whole test suite using the composer 
command `composer test`.

You can also use the PHPUnits `--filter` option to test only specific classes or methods. Visit 
[the official documentation](https://docs.phpunit.de/en/9.6/textui.html) for more information.

Examples:

```sh
# To run a single class
composer test -- --filter 'OFFLINE\\Mall\\Tests\\TestClass'

# To run a single method
composer test -- --filter 'OFFLINE\\Mall\\Tests\\TestClass::methodName'
```
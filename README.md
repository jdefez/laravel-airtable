<h1><center>Laravel Airtable</center></h1>

<p align="center">
  <a href="https://github.com/laravel-airtable/framework/actions"><img src="https://img.shields.io/github/workflow/status/laravel-airtable/framework/Tests.svg" alt="Build Status"></img></a>
  <a href="https://packagist.org/packages/laravel-airtable/framework"><img src="https://img.shields.io/packagist/dt/laravel-airtable/framework.svg" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel-airtable/framework"><img src="https://img.shields.io/packagist/v/laravel-airtable/framework.svg?label=stable" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel-airtable/framework"><img src="https://img.shields.io/packagist/l/laravel-airtable/framework.svg" alt="License"></a>
</p>

Laravel Airtable allows you to import your Airtable data into your Laravel database.

------

## Installation

You can install the package via composer:

```bash
composer require axeldotdev/laravel-airtable
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="AxelDotDev\LaravelAirtable\LaravelAirtableServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'uri' => 'https://api.airtable.com/v0/',

    /*
    |--------------------------------------------------------------------------
    | Airtable Key
    |--------------------------------------------------------------------------
    |
    | This value can be found in your Airtable account page:
    | https://airtable.com/account
    |
     */
    'key' => env('AIRTABLE_KEY'),
];
```

## Usage

In progress 🚧

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Axel Charpentier](https://github.com/axeldotdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

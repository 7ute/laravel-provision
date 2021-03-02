# Migrate provisioning data separately

A set of commands to provision data separately from your migrations or test seeds.

Laravel comes with `Migrations`, which are perfect for data structure, but feel odd for seeding data.
It also provides `Seeders` which are suited for tests data ands fakes, but can’t track version, and can create confusion. LaravelProvision is here to fill the gap by providing a way to provision sequentially, your production data.

## Installation

1. Require the package with Composer

```bash
composer require 7ute/laravel-provision
```

2a. Use the install command (that will create the `database/provisions` folder and publish the migration and config)
```bash
php artisan provision:install
```

2b. (alternate method) you can publish the migrations and config separately by running `vendor:publish`
```bash
php artisan vendor:publish --provider="SevenUte\LaravelProvision\ProvisionServiceProvider" --tag="laravel-provision-migrations"
php artisan vendor:publish --provider="SevenUte\LaravelProvision\ProvisionServiceProvider" --tag="laravel-provision-config"
```

Once you’re fine with the config (`config/provision.php`), run the migration
```bash
php artisan migrate
```

## Usage

- `php artisan provision {--force} {--silent}` Run the provisions that haven’t been run yet (use `--force` to discard the `production` environment confirmation input)
- `php artisan provision:status` Checks the provisions ran so far
- `php artisan provision:make {name}` Creates a provision with the unique classname `name`
- `php artisan provision:rollback {name}` Removes a provision from database with the classname or the filename `name`, allowing it to run again

## Provision files

The provision files are run in their chronological order.
Only the files that have not been yet provisionned are run.
You can access the current app through `$this->container` and current console through `$this->command`.

## Q&A

### • What if I want X feature ?
Fork the repository and create a pull request
### • What if it does not run as it should ?
Check the [issues](https://github.com/7ute/laravel-provision) and create one if you don't find any suitable solution

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Spatie](https://github.com/Spatie) for everything they've done so far
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

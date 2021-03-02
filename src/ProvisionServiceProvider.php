<?php

namespace SevenUte\LaravelProvision;

use Illuminate\Support\ServiceProvider;
use SevenUte\LaravelProvision\Console\ProvisionInstall;
use SevenUte\LaravelProvision\Console\ProvisionStatus;
use SevenUte\LaravelProvision\Console\ProvisionMake;
use SevenUte\LaravelProvision\Console\Provision;
use SevenUte\LaravelProvision\Console\ProvisionRollback;
use SevenUte\LaravelProvision\ProvisionSupplier;

class ProvisionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/provision.php', 'provision');
        $this->app->bind('laravel-provision', function ($app) {
            return new ProvisionSupplier();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProvisionInstall::class,
                Provision::class,
                ProvisionStatus::class,
                ProvisionMake::class,
                ProvisionRollback::class,
            ]);

            if (!class_exists('CreateLaravelProvisionsTable')) {
                $migration_origion = __DIR__ . '/../database/migrations/create_laravel_provisions_table.php.stub';
                $migration_destination = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_laravel_provisions_table.php');
                $this->publishes(["{$migration_origion}" => $migration_destination], 'laravel-provision-migrations');
            }

            $config_origion = __DIR__ . '/../config/provision.php';
            $config_destination = config_path('provision.php');
            $this->publishes(["{$config_origion}" => $config_destination], 'laravel-provision-config');
        }
    }
}

<?php

namespace SevenUte\LaravelProvision\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use SevenUte\LaravelProvision\ProvisionServiceProvider;

use File;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ProvisionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['path.base'] = __DIR__;

        include_once __DIR__.'/../database/migrations/create_laravel_provisions_table.php.stub';
        (new \CreateLaravelProvisionsTable())->up();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}

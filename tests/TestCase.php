<?php

namespace SevenUte\LaravelProvision\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use SevenUte\LaravelProvision\ProvisionServiceProvider;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use File;

class TestCase extends Orchestra
{
    public $mockConsoleOutput = false;

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

    /**
     * test->artisan (for <L8)
     * 
     * @param string $command The artisan command to run
     * @param null|string[] $params the command parameters
     */
    public function legacyArtisan(string $command, ?array $params = [])
    {
        $result = Artisan::call($command, array_merge(['--env' => 'testing'], $params));
        return [
            'code' => $result,
            'output' => Artisan::output(),
        ];
    }

    /**
     * test->assertDatabaseCount (for <L8)
     * 
     * @param string $table The table to count entries from
     * @param int $number How many entries we expect
     */
    public function legacyAssertDatabaseCount(string $table, int $number)
    {
        if (version_compare($this->app->version(), '8.0', '<')) {
            $entries = DB::table($table)->count();
            $this->assertEquals($entries, $number);
        } else {
            $this->assertDatabaseCount($table, $number);
        }
    }
}

<?php

namespace SevenUte\LaravelProvision\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Spatie\TestTime\TestTime;
use Carbon\Carbon;
use File;

class PackageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('provision:install');
    }

    public function tearDown(): void
    {
        $folder = database_path('provisions');
        if (File::isDirectory($folder)) {
            File::deleteDirectory($folder);
        }
        parent::tearDown();
    }

    /** @test */
    public function the_status_list_should_be_empty()
    {
        $this->artisan('provision:status')
            ->expectsTable(['Provision', 'Class'], [])
            ->assertExitCode(0);
    }

    /** @test */
    public function creating_a_provision_should_work()
    {
        TestTime::freeze('Y-m-d H:i:s', '2000-01-01 00:00:00');
        $this->artisan("provision:make", ['name' => 'FirstMigration'])
            ->run();

        $path = database_path('provisions' . DIRECTORY_SEPARATOR . '2000_01_01_000000_first_migration.php');
        $this->assertFileExists($path);
    }

    /** @test */
    public function running_a_provision_should_trigger_a_confirmation()
    {
        TestTime::freeze('Y-m-d H:i:s', '2000-01-02 00:00:00');
        $this->artisan("provision:make", ['name' => 'SecondMigration'])->run();

        $this->artisan("provision", ['--confirm' => true])
            ->expectsConfirmation('Do you really wish to run this command?', 'no');
        $this->assertDatabaseMissing('provisions', [
            'provision' => '2000_01_02_000000_second_migration',
        ]);
    }

    /** @test */
    public function the_status_list_should_not_be_empty()
    {

        TestTime::freeze('Y-m-d H:i:s', '2000-01-04 00:00:00');
        $this->artisan("provision:make", ['name' => 'FourthMigration'])->run();

        TestTime::freeze('Y-m-d H:i:s', '2000-01-03 00:00:00');
        $this->artisan("provision:make", ['name' => 'ThirdMigration'])->run();

        $this->artisan("provision", ['--force' => true])
            ->assertExitCode(0);
            
        $this->artisan('provision:status')
            ->expectsTable(
                ['Provision', 'Class'],
                [
                    ['2000_01_03_000000_third_migration', 'ThirdMigration'],
                    ['2000_01_04_000000_fourth_migration', 'FourthMigration'],
                ]
            )
            ->assertExitCode(0);
    }

    /** @test */
    public function running_a_provision_should_work()
    {
        $this->loadLaravelMigrations();

        TestTime::freeze('Y-m-d H:i:s', '2000-01-05 00:00:00');
        $stub = File::copy(
            'tests/stubs/provision.php.stub',
            database_path('provisions') . DIRECTORY_SEPARATOR . '2000_01_05_000000_fifth_migration.php'
        );

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.org']);

        $this->artisan("provision", ['--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => 'test@example.org']);

        // Running it twice should not run this one
        $this->artisan("provision", ['--force' => true])
            ->expectsOutput('All provisions have already run.');

        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function removing_a_provision_should_work()
    {
        TestTime::freeze('Y-m-d H:i:s', '2000-01-06 00:00:00');
        $this->artisan("provision:make", ['name' => 'SixthMigration'])->run();
        $this->artisan("provision", ['--force' => true])->run();
        $this->assertDatabaseHas('provisions', [
            'provision' => '2000_01_06_000000_sixth_migration',
        ]);

        $this->artisan("provision:rollback", ['name' => 'SixthMigration'])->run();
        $this->assertDatabaseMissing('provisions', [
            'provision' => '2000_01_06_000000_sixth_migration',
        ]);
    }
}

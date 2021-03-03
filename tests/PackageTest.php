<?php

namespace SevenUte\LaravelProvision\Tests;

use Spatie\TestTime\TestTime;
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

    /**
     * @testdox [provision:status  ] The status list should be empty
     */
    public function testStatusListShouldBeEmpty()
    {
        $result = $this->legacyArtisan('provision:status');
        $this->assertEquals(0, $result['code']);
        $this->assertStringContainsString('Provision | Class', $result['output']);
    }

    /**
     * @testdox [provision:make    ] The provision file creation should work
     */
    public function testProvisionFileCreationShouldWork()
    {
        TestTime::freeze('Y-m-d H:i:s', '2000-01-01 00:00:00');
        $result = $this->legacyArtisan('provision:make', ['name' => 'FirstMigration']);
        $this->assertEquals(0, $result['code']);
        $this->assertStringContainsString('has been created', $result['output']);

        $path = database_path('provisions' . DIRECTORY_SEPARATOR . '2000_01_01_000000_first_migration.php');
        $this->assertFileExists($path);
    }

    /**
     * @testdox [provision -confirm] Running a provision should work trigger a confirmation
     */
    public function testProvisioningShouldWarnOnProduction()
    {

        TestTime::freeze('Y-m-d H:i:s', '2000-01-02 00:00:00');
        $result = $this->legacyArtisan('provision:make', ['name' => 'SecondMigration']);
        $this->assertEquals(0, $result['code']);

        $result = $this->legacyArtisan('provision', ['--confirm' => true, '--no-interaction' => true]);
        $this->assertStringContainsString('Application In Production!', $result['output']);
        $this->assertStringContainsString('Command Canceled!', $result['output']);

        $this->assertDatabaseMissing('provisions', [
            'provision' => '2000_01_02_000000_second_migration',
        ]);
    }

    /**
     * @testdox [provision:status  ] Status list should not be empty after a couple provisions
     */
    public function testProvisioningShouldPopulateStatusList()
    {

        TestTime::freeze('Y-m-d H:i:s', '2000-01-04 00:00:00');
        $this->legacyArtisan('provision:make', ['name' => 'FourthMigration']);

        TestTime::freeze('Y-m-d H:i:s', '2000-01-03 00:00:00');
        $this->legacyArtisan('provision:make', ['name' => 'ThirdMigration']);

        $result = $this->legacyArtisan('provision', ['--force' => true]);
        $this->assertEquals(0, $result['code']);
        $this->assertStringContainsString('FourthMigration', $result['output']);
        $this->assertStringContainsString('ThirdMigration', $result['output']);
        $this->assertStringContainsString('All provisions (2) run successfully.', $result['output']);

        $result = $this->legacyArtisan('provision:status');
        $this->assertEquals(0, $result['code']);
        $this->assertStringContainsString('2000_01_03_000000_third_migration', $result['output']);
        $this->assertStringContainsString('2000_01_04_000000_fourth_migration', $result['output']);
        $this->assertStringContainsString('ThirdMigration', $result['output']);
        $this->assertStringContainsString('FourthMigration', $result['output']);
    }

    /**
     * @testdox [provision         ] Provisioning twice should not be executed twice
     */
    public function testProvisioningTwiceShouldWork()
    {
        $this->loadLaravelMigrations();

        TestTime::freeze('Y-m-d H:i:s', '2000-01-05 00:00:00');
        $stub = File::copy(
            'tests/stubs/provision.php.stub',
            database_path('provisions') . DIRECTORY_SEPARATOR . '2000_01_05_000000_fifth_migration.php'
        );

        $this->legacyAssertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.org']);

        $result = $this->legacyArtisan('provision', ['--force' => true]);
        $this->assertEquals(0, $result['code']);

        $this->legacyAssertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => 'test@example.org']);

        // Running it twice should not run this one
        $result = $this->legacyArtisan('provision', ['--force' => true]);
        $this->assertEquals(0, $result['code']);

        $this->legacyAssertDatabaseCount('users', 1);
    }

    /**
     * @testdox [provision:rollback] Rolling back should remove the provision from DB
     */
    public function testRollingBackShouldWork()
    {
        TestTime::freeze('Y-m-d H:i:s', '2000-01-06 00:00:00');
        $this->legacyArtisan('provision:make', ['name' => 'SixthMigration']);
        $result = $this->legacyArtisan('provision', ['--force' => true]);
        $this->assertEquals(0, $result['code']);
        $this->assertDatabaseHas('provisions', [
            'provision' => '2000_01_06_000000_sixth_migration',
        ]);

        $result = $this->legacyArtisan('provision:rollback', ['name' => 'SixthMigration']);
        $this->assertEquals(0, $result['code']);
        $this->assertDatabaseMissing('provisions', [
            'provision' => '2000_01_06_000000_sixth_migration',
        ]);
    }
}

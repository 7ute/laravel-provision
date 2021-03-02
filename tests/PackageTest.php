<?php

namespace SevenUte\LaravelProvision\Tests;

use Spatie\TestTime\TestTime;
use Carbon\Carbon;

class PackageTest extends TestCase
{
    /** @test */
    public function the_status_list_should_be_empty()
    {
        $this->artisan('provision:status')
            ->expectsTable(['Provision', 'Class'], [])
            ->assertExitCode(0);
    }
}

<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use SevenUte\LaravelProvision\ProvisionFacade;

class ProvisionStatus extends Command
{
    protected $signature = 'provision:status';

    protected $description = 'Show the status of each provision';

    public function handle()
    {
        $provisions = ProvisionFacade::getAlreadyRanProvisions()
            ->map(function ($line) {
                return [$line->provision, ProvisionFacade::getClassFromName($line->provision)];
            })
            ->values()
            ->toArray();
        $this->table(
            ['Provision', 'Class'],
            $provisions
        );
    }
}

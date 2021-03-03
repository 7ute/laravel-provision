<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use SevenUte\LaravelProvision\ProvisionFacade;

class ProvisionRollback extends Command
{
    protected $signature = 'provision:rollback {name : classname or filename of the provision}';

    protected $description = 'Removes a provision from the database';

    public function handle()
    {
        $name = $this->argument('name');
        $provision = $name;
        $provisions = ProvisionFacade::getProvisionFiles()
            ->map(function ($path, $provision_name) use (&$provision, $name) {
               if (ProvisionFacade::getClassFromName($provision_name) === $name) {
                    $provision = $provision_name;
                }
            })
            ->pluck('name', 'class');

        $provisioned = ProvisionFacade::getAlreadyRanProvisions()
            ->pluck('id', 'provision');

        $id = $provisioned->get($provision);
        if (!$id) {
            return $this->warn("The provision '$provision' does not exist in the database.");
        }

        ProvisionFacade::remove($provision);
        $this->line("The provision <info>$provision</info> has been removed and can now be re-run.");
    }
}

<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use SevenUte\LaravelProvision\ProvisionFacade;

class ProvisionMake extends Command
{
    protected $signature = 'provision:make {name : classname of the provision to make}';

    protected $description = 'Creates a provision file';

    public function handle()
    {
        $name = $this->argument('name');
        
        $path = ProvisionFacade::createProvisionFile($name);

        $this->info("The provision file {$path} has been created");
    }
}

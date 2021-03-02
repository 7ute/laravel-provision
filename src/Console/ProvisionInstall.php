<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use SevenUte\LaravelProvision\ProvisionFacade;

class ProvisionInstall extends Command
{
    protected $signature = 'provision:install';

    protected $description = 'Installs the provisioning system';

    public function handle()
    {
        $this->info('Installing provisions...');
        if (!File::isDirectory(base_path(ProvisionFacade::getFolder()))) {
            File::makeDirectory(base_path(ProvisionFacade::getFolder()), 0755, true);
            Artisan::call('vendor:publish', [
                "--provider" => "SevenUte\LaravelProvision\ProvisionServiceProvider"
            ]);
            $this->line('Done');
            $this->warn('Don’t forget run the migration with `php artisan migrate` after you edited the config.');
        } else {
            $this->line('Provisions already exists in ' . ProvisionFacade::getFolder());
            $this->line('To publish the migration and config files, run:');
            $this->info('    php artisan vendor:publish --vendor="SevenUte\LaravelProvision\ProvisionServiceProvider"');
            $this->line('');
        }
    }
}

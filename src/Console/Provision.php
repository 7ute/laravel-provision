<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SevenUte\LaravelProvision\ProvisionFacade;
use Exception;

class Provision extends Command
{
    protected $signature = 'provision
        {--force : Force the operation to run when in production}
        {--silent : Hide the command output}
        {--confirm : Shows a confirmation }
        {--dry-run : Discards database modifications <fg=red>[!] ATTENTION:</> It does not prevent writing on disk! }';

    protected $description = 'Creates a provision with the classname {name}';

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $silent = $this->option('silent', false);
        if ($silent !== false) {
            $this->setVerbosity('quiet');
        }

        $dry_run = $this->option('dry-run', false);
        if ($dry_run) {
            $this->line('<fg=yellow>[!] Dry-run</> The database modifications wont be committed');
            DB::beginTransaction();
        }

        $files = ProvisionFacade::getProvisionFiles();
        $provisioned = ProvisionFacade::getAlreadyRanProvisions()->pluck('id', 'provision');
        $classesToRun = [];
        foreach ($files as $name => $path) {
            if ($provisioned->has($name)) {
                continue;
            }
            $classname = ProvisionFacade::getClassFromName($name);
            $classesToRun[] = [
                'name' => $name,
                'path' => $path,
                'class' => $classname,
            ];

            if (class_exists($classname)) {
                throw new Exception("The '{$classname}' class already exists.");
            }
            if ($silent === false) {
                $this->line("<fg=blue>[i] Provisioning</> {$classname} (<comment>$name</comment>)");
            }
            $startTime = microtime(true);

            require_once $path;

            $class = $this->laravel
                ->make($classname)
                ->setContainer($this->laravel)
                ->setCommand($this)
                ->__invoke();

            ProvisionFacade::add($name);

            $runTime = round(microtime(true) - $startTime, 2);
            if ($silent === false) {
                $this->line("<fg=green>[✓] Provisioned</> {$classname} ({$runTime} seconds)");
            }
        }

        $number_run = count($classesToRun);
        if ($number_run > 0) {
            $this->line("<fg=green>[✓]</> All provisions (<comment>{$number_run}</comment>) run <fg=green>successfully</>.");
        } else {
            $this->line('<fg=yellow>[!]</> All provisions have <fg=yellow>already run</>.');
        }
        if ($dry_run) {
            $this->line('<fg=yellow>[!] Dry-run</>: The transaction has been <fg=green>rolled back</>');
            DB::rollBack();
        }
    }

    protected function confirmToProceed()
    {
        if ($this->getLaravel()->environment() !== 'production' && !$this->option('confirm', false)) {
            return true;
        }

        if ($this->hasOption('force') && $this->option('force')) {
            return true;
        }

        $this->alert('Application In Production!');
        $confirmed = $this->confirm('Do you really wish to run this command?');

        if (!$confirmed) {
            $this->comment('Command Canceled!');
            return false;
        }

        return true;
    }
}

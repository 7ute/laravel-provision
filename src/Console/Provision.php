<?php

namespace SevenUte\LaravelProvision\Console;

use Illuminate\Console\Command;
use SevenUte\LaravelProvision\ProvisionFacade;

class Provision extends Command
{
    protected $signature = 'provision
        {--force : Force the operation to run when in production}
        {--silent : Hide the command output}';

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

        $files = ProvisionFacade::getProvisionFiles();
        $provisionned = ProvisionFacade::getAlreadyRanProvisions()->pluck('id', 'provision');
        $classesToRun = [];
        foreach ($files as $name => $path) {
            if ($provisionned->has($name)) {
                continue;
            }
            $classname = ProvisionFacade::getClassFromName($name);
            $classesToRun[] = [
                'name' => $name,
                'path' => $path,
                'class' => $classname,
            ];

            if (class_exists($classname)) {
                throw new InvalidArgumentException("A {$className} class already exists.");
            }
            if ($silent === false) {
                $this->line("<comment>Provisionning:</comment> {$classname} (<comment>$name</comment>)");
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
                $this->line("<info>Provisionned:</info>  {$classname} ({$runTime} seconds)");
            }
        }

        $number_run = count($classesToRun);
        if ($number_run > 0) {
            $this->info("All provisions (<comment>{$number_run}</comment>) run successfully.");
        } else {
            $this->warn('All provisions have already run.');
        }
    }

    protected function confirmToProceed()
    {
        if ($this->getLaravel()->environment() === 'production') {
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

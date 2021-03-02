<?php

namespace SevenUte\LaravelProvision;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SevenUte\LaravelProvision\ProvisionSupplier
 */
class ProvisionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-provision';
    }
}

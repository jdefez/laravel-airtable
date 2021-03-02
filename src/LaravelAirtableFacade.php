<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AxelDotDev\LaravelAirtable\LaravelAirtable
 */
class LaravelAirtableFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-airtable';
    }
}

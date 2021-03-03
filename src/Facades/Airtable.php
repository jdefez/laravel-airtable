<?php

namespace AxelDotDev\LaravelAirtable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AxelDotDev\LaravelAirtable\Airtable
 */
class Airtable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'airtable';
    }
}

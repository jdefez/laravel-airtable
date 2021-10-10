<?php

namespace AxelDotDev\LaravelAirtable\Facades;

use AxelDotDev\LaravelAirtable\Airtableable;
use Illuminate\Support\Facades\Facade;

class Airtable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Airtableable::class;
    }
}

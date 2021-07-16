<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\ServiceProvider;

class LaravelAirtableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-airtable.php' => config_path('laravel-airtable.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-airtable.php', 'laravel-airtable');

        $this->app->bind(Airtableable::class, function ($app) {
            return new Airtable(
                config('laravel-airtable.uri'),
                config('laravel-airtable.key'),
            );
        });
    }
}

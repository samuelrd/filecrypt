<?php

namespace Samuelrd\FileCrypt;

use Illuminate\Support\ServiceProvider;

class FileCryptServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('filecrypt.php'),
            ], 'filecrypt-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'filecrypt');

        // Register the main class to use with the facade
        $this->app->singleton('filecrypt', function () {
            return new FileCrypt;
        });
    }
}

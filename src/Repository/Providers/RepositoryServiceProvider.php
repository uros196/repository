<?php

namespace Repository\Providers;

use Illuminate\Support\ServiceProvider;
use Repository\Console\Commands\RepositoryMakeCommand;
use Repository\Console\Commands\ServiceContainerMakeCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                RepositoryMakeCommand::class,
                ServiceContainerMakeCommand::class
            ]);
        }

        $this->publish();
    }

    /**
     * Publish package configuration.
     *
     * @return void
     */
    protected function publish(): void
    {
        $this->publishes([
            __DIR__ . "/../config/repository.php" => config_path('repository.php')
        ]);
    }
}

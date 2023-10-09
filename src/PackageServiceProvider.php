<?php

namespace Shafiulnaeem\MultiAuthRolePermission;

use Illuminate\Support\ServiceProvider;
use Shafiulnaeem\MultiAuthRolePermission\Console\CreateAuth;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // load migration
        $this->migration();
        // load command
        $this->command();
    }
    private function migration()
    {
        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
    private function command(){
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateAuth::class,
            ]);
        }
    }
}
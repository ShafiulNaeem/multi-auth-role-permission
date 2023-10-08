<?php

namespace Shafiulnaeem\MultiAuthRolePermission;

use Illuminate\Support\ServiceProvider;
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
    }

    private function migration()
    {
        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
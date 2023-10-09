<?php

namespace Shafiulnaeem\MultiAuthRolePermission;

use Illuminate\Support\ServiceProvider;
use Shafiulnaeem\MultiAuthRolePermission\Console\CreateAuth;
use Illuminate\Routing\Router;
use Shafiulnaeem\MultiAuthRolePermission\Http\Middleware\Authenticate;

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
        // load middleware
        $this->middleware();
        //load route
        $this->routeRegister();
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
    private function middleware(){
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('check.auth', Authenticate::class);
    }
    private function routeRegister()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/route.php');
    }
}
<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
class CreateAuth extends Command
{
    protected $signature = 'multiauthrolepermission:auth {name} --model={model_name}';

    protected $description = 'create auth with model.';

    public function handle()
    {
        $auth = $this->argument('name');
        $model = $this->argument('model_name');
        $this->info("Creating auth $auth for model $model...");
        $this->newLine();

        $this->createController($model);

//        if (! $this->configExists('blogpackage.php')) {
//            $this->publishConfiguration();
//            $this->info('Published configuration');
//        } else {
//            if ($this->shouldOverwriteConfig()) {
//                $this->info('Overwriting configuration file...');
//                $this->publishConfiguration($force = true);
//            } else {
//                $this->info('Existing configuration was not overwritten');
//            }
//        }

//        $this->info('Installed BlogPackage');
    }

    private function createController($name)
    {
        // check config file
        if (config('package.controller', true)) {
            $this->warn('Creating Controller');

            $this->call('make:controller', [
                'name' => ucfirst($name) . 'RoleController',
                '--resource' => config('package.resource', true),
            ]);
            $this->newLine();

            $this->call('make:controller', [
                'name' => ucfirst($name) . 'RolePermissionController',
                '--resource' => config('package.resource', true),
            ]);
            $this->newLine();

            $this->call('make:controller', [
                'name' => ucfirst($name) . 'RolePermissionModifyController',
                '--resource' => config('package.resource', true),
            ]);
            $this->newLine();
        }
    }


    private function createModel($name)
    {
        // check config file
        if (config('package.model', true)) {
            $this->warn('Creating Model');

            $this->call('make:model', [
                'name' => ucfirst($name)."Role",
                '-m' => config('package.migration', true),
            ]);
            $this->newLine();

            $this->call('make:model', [
                'name' => ucfirst($name)."RolePermission",
                '-m' => config('package.migration', true),
            ]);
            $this->newLine();

            $this->call('make:model', [
                'name' => ucfirst($name)."RolePermissionModify",
                '-m' => config('package.migration', true),
            ]);
            $this->newLine();
        }
    }


    private function createSeeder($name)
    {
        // check config file
        if (config('modulePermission.seeder', true)) {
            $this->warn('Creating Seeder');

            $this->call('make:seeder', [
                'name' => ucfirst($name) . 'Seeder',
            ]);
            $this->newLine();
        }
    }
    private function createView($name)
    {
        if ( config('modulePermission.view', true) ){
            $this->warn('Creating View');
            Artisan::call('mpermission:view ' . $name);
            $this->info('View created successfully');
            $this->newLine();
        }
    }

    private function createRoute($name)
    {
        if ( config('modulePermission.route', true) ){
            $this->warn('Creating Route');
            Artisan::call('mpermission:route ' . $name);
            $this->info('Route created successfully');
            $this->newLine();
        }
    }

    private function createRequest($name)
    {
        if ( config('modulePermission.formRequest', true) ){
            $this->warn('Creating Request');
            Artisan::call('make:request ' . ucfirst($name) . 'Request');
            $this->info('Request created successfully');
            $this->newLine();
        }
    }

//    private function configExists($fileName)
//    {
//        return File::exists(config_path($fileName));
//    }
//
//    private function shouldOverwriteConfig()
//    {
//        return $this->confirm(
//            'Config file already exists. Do you want to overwrite it?',
//            false
//        );
//    }
//
//    private function publishConfiguration($forcePublish = false)
//    {
//        $params = [
//            '--provider' => "JohnDoe\BlogPackage\BlogPackageServiceProvider",
//            '--tag' => "config"
//        ];
//
//        if ($forcePublish === true) {
//            $params['--force'] = true;
//        }
//
//        $this->call('vendor:publish', $params);
//    }

}
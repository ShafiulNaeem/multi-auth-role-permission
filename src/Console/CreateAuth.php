<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;

class CreateAuth extends Command
{
    protected $signature = 'createauthguard:authguard {name}';

    protected $description = 'create auth guard.';

    public function handle()
    {
        $auth = $this->argument('name');
        $this->info("Creating auth guard $auth ...");
        $this->newLine();

        if ($this->createAuthGuard($auth)){
            $this->info('Auth guard created successfully.');
        }else{
            $this->warn('Auth guard already exists.Please use different guard.');
        }

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

    private function createAuthGuard($name)
    {
        if (config('package.authGuard', true)) {
            $this->warn('Creating auth guard');
            // check auth guard exists
            if (! AuthGuard::where('name',$name)->exists()){
                //create auth guard
                $auth_guard = AuthGuard::create([
                    'name' => $name,
                    'model' => null,
                ]);
                //create role
                Role::create([
                    'auth_guard_id' => $auth_guard->id,
                    'name' => 'Admin',
                    'is_admin'=> true,
                    'note'=> null
                ]);
                $this->newLine();
                return true;
            }else{
                $this->newLine();
                return false;
            }
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
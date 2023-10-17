<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;

class CommandTest extends \Shafiulnaeem\MultiAuthRolePermission\Tests\TestCase
{
    use RefreshDatabase;

    public function getEnvironmentSetUp($app)
    {
        $this->SQLiteDatabase($app);

        $this->runMigration($app);
    }


    private function runMigration($app)
    {
        // auth guard
        if( ! \Illuminate\Support\Facades\Schema::hasTable('auth_guards') ){
            // import the CreatePostsTable class from the migration
            include_once __DIR__ . '/../../database/migrations/2023_10_08_193840_create_auth_guards_table.php';

            // run the up() method of that migration class
            (new \CreateAuthGuardsTable)->up();
        }
        // check role
        if( ! \Illuminate\Support\Facades\Schema::hasTable('roles') ){
            // import the CreatePostsTable class from the migration
            include_once __DIR__ . '/../../database/migrations/2023_10_08_193841_create_roles_table.php';

            // run the up() method of that migration class
            (new \CreateRolesTable)->up();
        }
        // check role permission
        if( ! \Illuminate\Support\Facades\Schema::hasTable('role_permissions') ){
            // import the CreatePostsTable class from the migration
            include_once __DIR__ . '/../../database/migrations/2023_10_08_193842_create_role_permissions_table.php';

            // run the up() method of that migration class
            (new \CreateRolePermissionsTable)->up();
        }
        // check role permission modification
        if( ! \Illuminate\Support\Facades\Schema::hasTable('role_permission_modifications') ){
            // import the CreatePostsTable class from the migration
            include_once __DIR__ . '/../../database/migrations/2023_10_08_193843_create_role_permission_modifications_table.php';

            // run the up() method of that migration class
            (new \CreateRolePermissionModificationsTable)->up();
        }
    }

    private function SQLiteDatabase($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function test_command()
    {
        $this->artisan('add:auth backend')->assertSuccessful();
        dd(AuthGuard::all(),Role::all());
    }
}
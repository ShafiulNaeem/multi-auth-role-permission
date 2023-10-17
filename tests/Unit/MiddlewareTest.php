<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Customer;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermission;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermissionModification;

class MiddlewareTest extends \Shafiulnaeem\MultiAuthRolePermission\Tests\TestCase
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
//        // check customers
//        if( ! \Illuminate\Support\Facades\Schema::hasTable('customers') ){
//            // import the CreatePostsTable class from the migration
//            include_once __DIR__ . '/../../database/migrations/2023_10_08_193844_create_customers_table.php';
//
//            // run the up() method of that migration class
//            (new \CreateCustomersTable)->up();
//        }
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
    public function test_middleware_auth()
    {
        $guard = AuthGuard::factory()->create();
        $this->assertTrue($guard->save());

        $role = Role::factory()->create();
        $this->assertTrue($role->save());

        // check role permission
        $role_permission = RolePermission::factory()->create();
        $this->assertTrue($role_permission->save());

        // check role permission modification
        $role_permission_modify = RolePermissionModification::factory()->create();
        $this->assertTrue($role_permission_modify->save());

//        $user = Customer::factory()->create();
//        $this->assertTrue($user->save());

        // check role permission modification
        $role_permission_modify = RolePermissionModification::factory()->create();
        $this->assertTrue($role_permission_modify->save());

        // Try to access the page
        $response = $this->get('/test1');
//        $response = $this->actingAs($user)
//            ->get('/test');

        // Assert the expected response status
        $response->assertStatus(200);

    }

}
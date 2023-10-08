<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Tests\Unit;

use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AuthGuardTest extends TestCase
{
    use RefreshDatabase;

    public function getEnvironmentSetUp($app)
    {
        $this->SQLiteDatabase($app);

        $this->runMigration($app);
    }

    private function runMigration($app)
    {
        if( ! \Illuminate\Support\Facades\Schema::hasTable('auth_guards') ){
            // import the CreatePostsTable class from the migration
            include_once __DIR__ . '/../../database/migrations/2023_10_08_193840_create_auth_guards_table.php';

            // run the up() method of that migration class
            (new \CreateAuthGuardsTable)->up();
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
    public function test_auth_guard_name()
    {
        $guard = AuthGuard::factory()->create();
        $this->assertTrue($guard->save());
    }


}
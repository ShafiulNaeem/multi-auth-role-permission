<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Database\Factories;

use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermission;

class RolePermissionFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = RolePermission::class;
    public function definition()
    {
        return [
            'auth_guard_id' => AuthGuard::first() ? AuthGuard::first()->id : null,
            'role_id' => Role::first() ? Role::first()->id : null,
            'module' => 'Test',
            'operation' => 'list',
            'route' => 'test',
            'url' => 'test',
            'is_permit' => 1
        ];
    }
}
<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Database\Factories;

use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermissionModification;

class RolePermissionModificationFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = RolePermissionModification::class;
    public function definition()
    {
        return [
            'auth_guard_id' => AuthGuard::first() ? AuthGuard::first()->id : null,
            'role_id' => Role::first() ? Role::first()->id : null,
            'auth_user_id' => 1,
            'module' => 'Dashboard',
            'operation' => 'list',
            'route' => 'admin/dashboard',
            'url' => 'admin/dashboard',
            'is_permit' => 1,
        ];
    }
}
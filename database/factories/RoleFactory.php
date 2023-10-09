<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
class RoleFactory extends Factory
{
    protected $model = Role::class;
    public function definition()
    {
        return [
            'auth_guard_id' => AuthGuard::first() ? AuthGuard::first()->id : null,
            'name' => 'Admin',
            'is_admin' => false,
            'note' => 'test'
        ];
    }
}
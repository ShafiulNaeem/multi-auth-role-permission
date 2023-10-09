<?php

namespace  Shafiulnaeem\MultiAuthRolePermission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;

class AuthGuardFactory extends Factory
{
    protected $model = AuthGuard::class;
    public function definition()
    {
        return [
            'name' => 'web',
            'model' => 'Customer',
        ];
    }
}
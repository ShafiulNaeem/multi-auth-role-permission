<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Database\Factories;

use Shafiulnaeem\MultiAuthRolePermission\Models\Customer;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;
    public function definition()
    {
        return [
            'role_id'=> Role::first() ? Role::first()->id : null,
            'name'=> 'Rifat',
            'email'=> 'email@g.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' // password
        ];
    }
}
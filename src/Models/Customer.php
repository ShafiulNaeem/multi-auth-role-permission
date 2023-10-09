<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Shafiulnaeem\MultiAuthRolePermission\Database\Factories\CustomerFactory;

class Customer extends Authenticatable
{
    use HasFactory;
    protected $table = 'customers';
    protected $guarded = [];
    protected static function newFactory()
    {
        return CustomerFactory::new();
    }
}
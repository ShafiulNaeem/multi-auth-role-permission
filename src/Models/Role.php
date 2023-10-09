<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Shafiulnaeem\MultiAuthRolePermission\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
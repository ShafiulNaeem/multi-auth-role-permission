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
    protected $appends = ['role_permissions'];
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class,'role_id');
    }
    public function authGuard()
    {
        return $this->belongsTo(AuthGuard::class);
    }
    public function getRolePermissionsAttribute()
    {
        return $this->attributes['role_permissions'] = permission_db_data_format(
            $this->rolePermissions()->get()->toArray(),
            $this->authGuard()->first()->name
        );
    }
}
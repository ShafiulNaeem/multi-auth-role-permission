<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Shafiulnaeem\MultiAuthRolePermission\Models\UserRole;
class Permission
{
    public function handle($request, Closure $next, $guard)
    {
        if (paymentVerify()){
            $user_id = auth()->guard($guard)->user()->id;
            $user_role = UserRole::join('auth_guards','auth_guards.id','=','user_roles.auth_guard_id')
                ->where(['auth_guards.name' => $guard , 'user_roles.auth_user_id' => $user_id])
                ->orderBy('user_roles.id','desc')
                ->first();

            if ($user_role){
                $role_id = $user_role->role_id;
                $role_auth = DB::table('roles')
                    ->join('auth_guards','auth_guards.id','=','roles.auth_guard_id')
                    ->select('roles.name as role_name','roles.is_admin','auth_guards.name as auth_guard','roles.auth_guard_id')
                    ->where('roles.id',$role_id)->first();

                if ($role_auth){
                    // check user guard  wise route access
                    if ($role_auth->auth_guard == $guard){
                        // check user role
                        if ($role_auth->is_admin){
                            return $next($request);
                        }else{
                            // check permission
                            $current_url = url()->current();
                            $permission = routePermission($current_url,$role_id,$user_id);

                            if ($permission){
                                return $next($request);
                            }else{
                                return sendError('You are not authorized to access this page',[],403);
                            }
                        }
                    }else{
                        return sendError('Access denied,Guard mismatch.', [], 403);
                    }
                }else{
                    return sendError('Role or Guard not defined.', [], 404);
                }
            }else{
                return sendError('Role or Guard not defined.', [], 404);
            }
        }else{
            return true;
        }
    }
}

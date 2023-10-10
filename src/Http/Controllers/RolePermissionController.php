<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermission;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    public function index()
    {
        $params = request()->all();
        $data = Role::join('auth_guards','auth_guards.id','=','roles.auth_guard_id')
            ->select('roles.*','auth_guards.name as auth_guard')
            ->orderBy('roles.id','desc');

        if ( $query['search'] ?? false ) {
            $data = $data->where('roles.name', 'like', '%' . $query['search'] . '%')
                ->orWhere('auth_guards.name', 'like', '%' . $query['search'] . '%');
        }
        $data = $data->paginate(page_limit($params));

        return sendResponse(
            'Data fetch successfully.',
            $data,
            Response::HTTP_CREATED
        );
    }
    public function store(Request $request)
    {
        $request->validate([
            'auth_guard_id' => 'required|exists:auth_guards,id',
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('roles')->where(function ($query) use ($request) {
                    $query->where('auth_guard_id', $request->auth_guard_id);
                }),
            ],
            'is_admin' => 'required|boolean',
            'note' => 'nullable',
            'role_permissions' => 'nullable|array'
        ]);

        $role = Role::create([
            'auth_guard_id' => $request->auth_guard_id,
            'name' => $request->name,
            'is_admin' => $request->is_admin,
            'note' => $request->note
        ]);

        // create permission
        $permissions = $request->role_permissions;
        $permissions = permission_data_format($permissions,$request->auth_guard_id,$role->id,0);
        RolePermission::insert($permissions);

        return sendResponse(
            'Data inserted successfully.',
            $role,
            Response::HTTP_CREATED
        );
    }
    public function show($id)
    {
        $data = Role::with('rolePermissions')
            ->join('auth_guards','auth_guards.id','=','roles.auth_guard_id')
            ->select('roles.*','auth_guards.name as auth_guard')
            ->where('id',$id)->first();
        if (!$data){
            return sendError(
                'Data not found.',
                [],
                Response::HTTP_NOT_FOUND
            );
        }
        $data = $data->toArray();
        $data['role_permissions'] = permission_db_data_format($data['role_permissions'],$data['auth_guard']);
        return sendResponse(
            'Data fetch successfully.',
            $data,
            Response::HTTP_OK
        );

    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'auth_guard_id' => 'required|exists:auth_guards,id',
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('roles')->where(function ($query) use ($request,$id) {
                    $query->where('auth_guard_id', $request->auth_guard_id);
                    $query->where('id','!=',$id);
                }),
            ],
            'is_admin' => 'required|boolean',
            'note' => 'nullable',
            'role_permissions' => 'nullable|array'
        ]);

        $role = Role::find($id);
        if (!$role){
            return sendError(
                'Data not found.',
                [],
                Response::HTTP_NOT_FOUND
            );
        }


        $role->update([
            'auth_guard_id'=> $request->auth_guard_id,
            'name'=> $request->name,
            'is_admin'=> $request->is_admin,
            'note'=> $request->note
        ]);
        $role_id = $id;

        // current permissions
        $permissions = $request->role_permissions;
        $permissions = permission_data_format($permissions,$request->auth_guard_id,$role_id,0);
        // previous permissions
        $previous_permissions = $role->load('rolePermissions')->toArray()['role_permissions'];
        // difference between previous and current permissions
        $permissions_difference= $this->permissions_difference($previous_permissions,$permissions,$request->auth_guard_id,$id);

        // create permission
        DB::table('role_permissions')->where('role_id',$id)->delete();
        DB::table('role_permissions')->insert($permissions);

        $permission_staff = DB::table('role_permission_modifications')
            ->select('auth_guard_id','role_id','auth_user_id','module','operation','route','is_permit','url')
            ->where('role_id',$id)->get()->toArray();

        if (count($permission_staff) > 0){
            $staff_permission_data = $this->updateStaffpermission($permissions_difference,$permission_staff,$request->auth_guard_id,$id);
            if (count($staff_permission_data) > 0){
                DB::table('role_permission_modifications')->where('role_id',$id)->delete();
                DB::table('role_permission_modifications')->insert($staff_permission_data);
            }
        }

        return sendResponse(
            'Data updated successfully.',
            $role,
            Response::HTTP_OK
        );
    }
    public function permissions_difference($previous_permissions, $current_permissions,$auth_guard_id,$role_id)
    {
        $result = array();
        foreach ($current_permissions as $per=>$current_permission){
            $search = array_search($current_permission['route'], array_column($previous_permissions, 'route'));
            if (!is_bool($search)){
                $is_permit = $previous_permissions[$search]['is_permit'];
                if ($is_permit != $current_permission['is_permit']){
                    $result[] = [
                        'auth_guard_id'=> (int) $auth_guard_id,
                        'role_id'=> (int) $role_id,
                        'auth_user_id'=> (int) $current_permission['auth_user_id'],
                        'module'=> $current_permission['module'],
                        'operation'=> $current_permission['operation'],
                        'route'=> $current_permission['route'],
                        'is_permit'=> (int) $current_permission['is_permit']
                    ];
                }
            }
        }
        return $result;
    }
    public function updateStaffpermission($permissions_difference,$permission_staff,$auth_guard_id,$role_id){
        $result = array();
        if (count($permissions_difference) > 0){
            foreach ($permission_staff as $per=>$datum){
                $is_permit = (int) $datum->is_permit;
                $search = array_search($datum->route, array_column($permissions_difference, 'route'));
                if (!is_bool($search)){
                    $is_permit = (int) $permissions_difference[$search]['is_permit'];
                }
                $result[] = [
                    'auth_guard_id'=> (int) $auth_guard_id,
                    'role_id'=> (int) $role_id,
                    'auth_user_id'=> (int) $datum->auth_user_id,
                    'module'=> $datum->module,
                    'operation'=> $datum->operation,
                    'route'=> $datum->route,
                    'is_permit'=>  $is_permit
                ];
            }
        }
        return $result;
    }
    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role){
            return sendError(
                'Data not found.',
                [],
                Response::HTTP_NOT_FOUND
            );
        }

        if (DB::table('role_permissions')->where('role_id',$id)->count() > 0){
            DB::table('role_permissions')->where('role_id',$id)->delete();
        }
        if (DB::table('role_permission_modifications')->where('role_id',$id)->count() > 0){
            DB::table('role_permission_modifications')->where('role_id',$id)->delete();
        }

        $role->delete();
        return sendResponse(
            'Data deleted successfully',
            [],
            Response::HTTP_OK
        );
    }
    public function module_permission($guard)
    {
        if (! AuthGuard::where('name',$guard)->exists()){
            return sendError('Guard not exists.', [], Response::HTTP_NOT_FOUND);
        }
        return sendResponse(
            'Data fetch successfully.',
            permission_data($guard),
            Response::HTTP_OK
        );
    }
}
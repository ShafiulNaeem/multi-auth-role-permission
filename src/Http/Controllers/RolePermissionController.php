<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard;
use Shafiulnaeem\MultiAuthRolePermission\Models\Role;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermission;
use Shafiulnaeem\MultiAuthRolePermission\Models\RolePermissionModification;
use Shafiulnaeem\MultiAuthRolePermission\Models\UserRole;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    private function mainQuery()
    {
        return Role::join('auth_guards','auth_guards.id','=','roles.auth_guard_id')
            ->select('roles.*','auth_guards.name as auth_guard');
    }
    public function index()
    {
        $params = request()->all();
        $data = $this->mainQuery()->orderBy('roles.id','desc');

        if ( $params['search'] ?? false ) {
            $data = $data->where('roles.name', 'like', '%' . $params['search'] . '%');
        }

        if ( $params['guard'] ?? false ) {
            $data = $data->where('auth_guards.name',$params['guard']);
        }
        $data = $data->paginate(page_limit($params));

        return sendResponse(
            'Data fetch successfully.',
            $data,
            Response::HTTP_OK
        );
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auth_guard_id' => 'required|exists:auth_guards,id',
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('roles')->where(function ($query) use ($request) {
                    $query->where('auth_guard_id', $request->auth_guard_id);
                }),
            ],
            'is_admin' => 'required|boolean',
            'note' => 'nullable',
            'role_permissions' => 'required|array'
            ],
            [
                'auth_guard_id.required' => 'Auth guard field is required.',
                'auth_guard_id.exists' => 'Selected auth guard is invalid.',
                'name.required' => 'Role name field is required.',
                'name.unique' => 'Role name has already been taken for the selected auth guard.',
                'is_admin.required' => 'Is admin  field is required.',
                'is_admin.boolean' => 'Is admin field must be a boolean value.',
                'note.nullable' => 'Note field must be null or a valid value.',
                'role_permissions.required' => 'Role permissions field is required.',
                'role_permissions.array' => 'Role permissions must be an array.'
            ]
        );

        if ($validator->fails()) {
            return sendError('Validation error',$validator->errors()->toArray(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

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

            DB::commit();

            return sendResponse(
                'Data inserted successfully.',
                $this->mainQuery()->where('roles.id',$role->id)->first(),
                Response::HTTP_CREATED
            );

        } catch (\Exception $exception) {
            DB::rollBack();
            return sendError(
                'something went wrong',
                [
                    'error' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    public function show($id)
    {
        $data =  $this->mainQuery()->with('rolePermissions')->where('roles.id',$id)->first();
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
        $validator = Validator::make($request->all(), [
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
            'role_permissions' => 'required|array'
        ],
            [
                'auth_guard_id.required' => 'Auth guard field is required.',
                'auth_guard_id.exists' => 'Selected auth guard is invalid.',
                'name.required' => 'Role name field is required.',
                'name.unique' => 'Role name has already been taken for the selected auth guard.',
                'is_admin.required' => 'Is admin  field is required.',
                'is_admin.boolean' => 'Is admin field must be a boolean value.',
                'note.nullable' => 'Note field must be null or a valid value.',
                'role_permissions.required' => 'Role permissions field is required.',
                'role_permissions.array' => 'Role permissions must be an array.'
            ]
        );

        if ($validator->fails()) {
            return sendError('Validation error',$validator->errors()->toArray(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $role = Role::find($id);
        if (!$role){
            return sendError(
                'Data not found.',
                [],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            DB::beginTransaction();
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
                ->select('auth_guard_id','role_id','auth_user_id','module','operation','route','is_permit','url', 'route_name', 'method')
                ->where('role_id',$id)->get()->toArray();

            if (count($permission_staff) > 0){
                $staff_permission_data = $this->updateStaffpermission($permissions_difference,$permission_staff,$request->auth_guard_id,$id);
                if (count($staff_permission_data) > 0){
                    DB::table('role_permission_modifications')->where('role_id',$id)->delete();
                    DB::table('role_permission_modifications')->insert($staff_permission_data);
                }
            }

            DB::commit();

            return sendResponse(
                'Data updated successfully.',
                $this->mainQuery()->where('roles.id',$id)->first(),
                Response::HTTP_OK
            );

        } catch (\Exception $exception) {
            DB::rollBack();
            return sendError(
                'something went wrong',
                [
                    'error' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
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
                        'is_permit'=> (int) $current_permission['is_permit'],
                        'route_name' => $current_permission['route_name'],
                        'method' => $current_permission['method']
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
                    'is_permit'=>  $is_permit,
                    'route_name' => $datum->route_name,
                    'method' => $datum->method
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

        $rolePermission = DB::table('role_permissions')->where('role_id',$id);

        if ($rolePermission->count() > 0) $rolePermission->delete();

        $rolePermissionModification =  DB::table('role_permission_modifications')->where('role_id',$id);

        if ($rolePermissionModification->count() > 0) $rolePermissionModification->delete();

        $role->delete();
        return sendResponse(
            'Data deleted successfully.',
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

    public function user_permission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'role_id' => 'required|exists:roles,id',
            'role_permissions' => 'required|array'
        ],
            [
                'auth_user_id.required' => 'The auth user field is required.',
                'auth_user_id.exists' => 'The selected auth user is invalid.',
                'role_id.required' => 'The role field is required.',
                'role_id.exists' => 'The selected role is invalid.',
                'role_permissions.required' => 'The role permissions field is required.',
                'role_permissions.array' => 'The role permissions must be an array.',
            ]
        );

        if ($validator->fails()) {
            return sendError('Validation error',$validator->errors()->toArray(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $auth_guard_id = $this->get_auth_guard_id($request->role_id)->auth_guard_id;

            // delete previous role
            DB::table('user_roles')->where([
                'auth_user_id'=>$request->auth_user_id,
                'auth_guard_id'=>$auth_guard_id
            ])->delete();
            // insert role
            UserRole::create([
                'role_id'=>$request->role_id,
                'auth_user_id'=>$request->auth_user_id,
                'auth_guard_id'=>$auth_guard_id
            ]);

            $permissions = $request->role_permissions;
            $permissions = permission_data_format($permissions,$auth_guard_id,$request->role_id,$request->auth_user_id);
            DB::table('role_permission_modifications')->where([
               // 'role_id'=>$request->role_id,
                'auth_user_id'=>$request->auth_user_id,
                'auth_guard_id'=>$auth_guard_id
            ])->delete();
            DB::table('role_permission_modifications')->insert($permissions);

            DB::commit();

            return sendResponse(
                'Permission added successfully.',
                [],
                Response::HTTP_OK
            );

        } catch (\Exception $exception) {
            DB::rollBack();
            return sendError(
                'something went wrong',
                [
                    'error' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    public function get_auth_guard_id($role_id)
    {
        return DB::table('roles')
            ->join('auth_guards','auth_guards.id','=','roles.auth_guard_id')
            ->where('roles.id',$role_id)
            ->select('auth_guards.name as auth_guard','roles.auth_guard_id')
            ->first();
    }
    public function get_user_permission_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'role_id' => 'required|exists:roles,id'
        ],
            [
                'auth_user_id.required' => 'The auth user field is required.',
                'auth_user_id.exists' => 'The selected auth user is invalid.',
                'role_id.required' => 'The role field is required.',
                'role_id.exists' => 'The selected role is invalid.'
            ]
        );

        if ($validator->fails()) {
            return sendError('Validation error',$validator->errors()->toArray(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $auth_guard = $this->get_auth_guard_id($request->role_id);

        $permissions = [];

        if (DB::table('role_permission_modifications')->where([
            'role_id'=>$request->role_id,
            'auth_user_id'=>$request->auth_user_id,
            'auth_guard_id'=>$auth_guard->auth_guard_id
        ])->count() > 0){
            $permissions = permission_db_data_format(
                RolePermissionModification::where([
                    'role_id'=>$request->role_id,
                    'auth_user_id'=>$request->auth_user_id,
                    'auth_guard_id'=>$auth_guard->auth_guard_id
                ])->get()->toArray(),
                $auth_guard->auth_guard);
        }else{
            $permissions = permission_db_data_format(
                RolePermission::where([
                    'role_id'=>$request->role_id,
                    'auth_guard_id'=>$auth_guard->auth_guard_id
                ])->get()->toArray(),
                $auth_guard->auth_guard);
        }

        return sendResponse(
            'Permission data fetch successfully.',
            $permissions,
            Response::HTTP_OK
        );

    }
}

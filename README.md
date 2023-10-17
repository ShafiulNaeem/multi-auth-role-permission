# multi-auth-role-permission

## Table of Contents
* [Description](#description)
* [Installation](#installation)

## Description
Multi-Auth Role Permission Package is a versatile solution . With this package, you can establish various user roles, each with specific permissions, and empower users to customize their permissions beyond their assigned roles. It's a user-friendly way to create a secure and flexible access control system within your Laravel project.

## Installation
👉 To install this package, you'll need to have Laravel version 9 or higher and PHP version 8.0.0 or higher installed on your machine. You can download the latest version of PHP from the official PHP resource: https://www.php.net/downloads.php. Ensure your environment meets these requirements before proceeding with the installation.

- Install auth package in laravel project (passport,JWT etc).
- Install package:
  ``` composer require shafiulnaeem/multi-auth-role-permission ```
- Add auth guards:
  ``` php artisan add:auth your-guard ```

- After add auth guard, hit  ``` applicatoin_url/guards ``` get api for guard list.
  example:
   ```
   {
    "code": 200,
    "success": true,
    "message": "Data fetch successfully.",
    "data": [
            {
                "id": 1
                "name": "admin",
                "created_at": "2023-10-12T08:27:57.000000Z",
                "updated_at": "2023-10-12T08:27:57.000000Z",
            },
            {
                "id": 2,
                "name": "customer",
                "created_at": "2023-10-12T08:27:51.000000Z",
                "updated_at": "2023-10-12T08:27:51.000000Z",
            }
     ],
    "errors": []
    }
   ```
- Follow bellow route pattern for permissions

  ```shell
  Route::middleware('check.auth:your guard')->group(function () {
  // users can access this block routes after login  
    Route::middleware('permission:your guard')->group(function () {
  // user can access this block routes if user loggedin and if user have permission 
  // demo routes
        Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/a', function () { return 'ok'; })->name('a');
            Route::get('/b', function () { return 'ok'; })->name('b');
        });
    });
  });
  
  // example: if you have two guards like admin and customer then pattern like bellow
  // for admin
  Route::middleware('check.auth:admin')->group(function () {
  // users can access this block routes after login  
    Route::middleware('permission:admin')->group(function () {
  // user can access this block routes if user loggedin and if user have permission 
  // demo routes
        Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/a', function () { return 'ok'; })->name('a');
            Route::get('/b', function () { return 'ok'; })->name('b');
        });
    });
  });
  // for customer
  Route::middleware('check.auth:customer')->group(function () {
  // users can access this block routes after login  
    Route::middleware('permission:customer')->group(function () {
  // user can access this block routes if user loggedin and if user have permission 
  // demo routes
        Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/a', function () { return 'ok'; })->name('a');
            Route::get('/b', function () { return 'ok'; })->name('b');
        });
    });
  });
  ```
- For guard wise route permission list use bellow route..
  ```shell
  use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
  Route::get('permission/{guard}', [RolePermissionController::class, 'module_permission'])->name('permission.list');

  // example: response for admin guard
  {
    "code": 200,
    "success": true,
    "message": "Data fetch successfully.",
    "data": [
         {
         "module": "test",
         "permission": [
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " a",
                    "route": "test/a",
                    "is_permit": 0,
                    "route_name": "test.a",
                    "method": "GET"
                },
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " b",
                    "route": "test/b",
                    "is_permit": 0,
                    "route_name": "test.b",
                    "method": "GET"
                }
            ]
        },
     ],
    "errors": []
    }
  ```
  - Role CRUD route
  ```shell
  use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
  Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('/list', [RolePermissionController::class, 'index'])->name('list');
    Route::post('/create', [RolePermissionController::class, 'store'])->name('create');
    Route::get('/show/{id}', [RolePermissionController::class, 'show'])->name('show');
    Route::put('/update/{id}', [RolePermissionController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RolePermissionController::class, 'destroy'])->name('delete');
    Route::post('/user/permission/add', [RolePermissionController::class, 'user_permission'])->name('user.permission.add');
    Route::post('/user/permission/list', [RolePermissionController::class, 'get_user_permission_list'])->name('user.permission.list');
  });

  // example: Request data for role create and update 
  {
    "auth_guard_id" : 1, // from '/gurds'  api.
    "role_id" : 2,
    "name" : "admin",
    "is_admin" : 0,
    "role_permissions" : [
        {
         "module": "test",
         "permission": [
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " a",
                    "route": "api/test/a",
                    "is_permit": 0,
                    "route_name": "test.a",
                    "method": "GET"
                },
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " b",
                    "route": "api/test/b",
                    "is_permit": 0,
                    "route_name": "test.b",
                    "method": "GET"
                }
            ]
        },
        {
         "module": "test",
         "permission": [
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " a",
                    "route": "api/test/a",
                    "is_permit": 0,
                    "route_name": "test.a",
                    "method": "GET"
                },
                {
                    "auth_guard_id": 0,
                    "role_id": 0,
                    "auth_user_id": 0,
                    "module": "test",
                    "operation": " b",
                    "route": "api/test/b",
                    "is_permit": 0,
                    "route_name": "test.b",
                    "method": "GET"
                }
            ]
        }
    ]
  }
  ```

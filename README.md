# multi-auth-role-permission

## Table of Contents
* [Description](#description)
* [Installation](#installation)


> [!IMPORTANT]
> To us this package User must be login for access the routes.

## Description
**Multi-Auth Role Permission** is a versatile Laravel package designed to create a robust and flexible access control system. This package enables you to define user roles with specific permissions and allows users to customize their permissions beyond their assigned roles. It's a user-friendly solution to enhance the security and control of your Laravel project.

## Installation
👉 To install this package, you'll need to have Laravel version 9 or higher and PHP version 8.0.0 or higher installed on your machine. You can download the latest version of PHP from the official PHP resource: https://www.php.net/downloads.php. Ensure your environment meets these requirements before proceeding with the installation.

- Install auth package in laravel project (passport,JWT etc).
- Install package:
  ``` composer require shafiulnaeem/multi-auth-role-permission ```
- Run migrate command: ``` php artisan migrate ```
- Add auth guards:
  ``` php artisan add:auth {your-guard-name} ```

- After add auth guard, hit  ``` applicatoin_url/guards ``` get api for guard list.
  ` GET ` [http://localhost:8000/guards]( http://localhost:8000/guards )
  example:
   ```json
   {
    "code": 200,
    "success": true,
    "message": "Data fetch successfully.",
    "data": [
        {
            "id": 1,
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
- Follow bellow route middleware pattern for permissions. User must be login for access the routes. 

  ```php
  // users can access this block routes after login  
  Route::middleware('check.auth:{your-guard}')->group(function () {
    // user can access this block routes if user logged in and if user have permission to access this page 
    Route::middleware('permission:{your-guard}')->group(function () {
        // demo route
        Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/index', function () { 
              return response()->json([
                 'code' => 200,
                 'success' => true,
                 'message' => 'Permission testing',
                 'data' => [],
                 'errors' => []
              ]);  
            })->name('index');
          });
    });
  });
  ```


- Suppose you have two ```auth guards``` like ```admin``` and ```customer```. Then you can use bellow route pattern for permission.

   
  - For admin.
  
  ```php
    // example: if you have two guards like admin and customer then pattern like bellow
    // for admin
    // users can access this block routes after login  
    Route::middleware('check.auth:admin')->group(function () {
        // user can access this block routes if user loggedin and if user have permission 
        Route::middleware('permission:admin')->group(function () {
          // demo routes
          Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/admin/dashboard', function () { 
              return response()->json([
                 'code' => 200,
                 'success' => true,
                 'message' => 'Admin Permission testing',
                 'data' => [],
                 'errors' => []
              ]);  
            })->name('admin.dashboard');
          });
      });
    });
  ```
  
  - For customer
  
  ```php
    // for customer
    // users can access this block routes after login  
    Route::middleware('check.auth:customer')->group(function () {
      // user can access this block routes if user loggedin and if user have permission 
      Route::middleware('permission:customer')->group(function () {
          // demo routes
          Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
            Route::get('/profile', function () {
              return response()->json([
                 'code' => 200,
                 'success' => true,
                 'message' => 'Customer Permission testing',
                 'data' => [],
                 'errors' => []
              ]);  
            })->name('customer.profile');
          });
      });
    });
  ```

- For guard wise route permission list use bellow route. <br/>
   ```php
     use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
     Route::get('permission/module/{guard}', [RolePermissionController::class, 'module_permission'])->name('permission.route.list');
   ```
  ```json
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
        }
     ],
    "errors": []
    }
  ```

- Role CRUD route
  ```php
  // example: Request data for role create and update
  use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
  Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('/list', [RolePermissionController::class, 'index'])->name('list');
    Route::post('/create', [RolePermissionController::class, 'store'])->name('create');
    Route::get('/show/{id}', [RolePermissionController::class, 'show'])->name('show');
    Route::put('/update/{id}', [RolePermissionController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RolePermissionController::class, 'destroy'])->name('delete');
  });
   ```
  ```json
  // example: Request data for role create and update
  {
    "auth_guard_id" : 1, // from '{base_url}/gurds'  api.
    "role_id" : 2,  // defined role id from '{base_url}/role/list' api.
    "name" : "admin",
    "is_admin" : 0, // 1 means admin role and 0 means other role. If admin then there is no permission for this role.
    "role_permissions" : [ // permission data from '{base_url}/permission/{guard}' api.
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
                    "is_permit": 1, // 1 means add permission for test.a route
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
                    "is_permit": 0, // 0 means permission not added
                    "route_name": "test.b",
                    "method": "GET"
                }
            ]
        }
    ]
  }
  ```
- user permission list from bellow route <br />
  ```php
   use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
   Route::post('/user/permission/list', [RolePermissionController::class, 'get_user_permission_list'])->name('user.permission.list');
  ```
  ```json
  // example: Request data
  {
    "auth_user_id" : 1, // from '/gurds'  api.
    "role_id" : 1
  }
  ```

- Assaign user permission using bellow route
  ```php
   use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
   Route::post('/user/permission/add', [RolePermissionController::class, 'user_permission'])->name('user.permission.add');
  ```
  ```json
  // example: Request data
  {
    "auth_user_id" : 1, // from '/gurds'  api.
    "role_id" : 2,
    "role_permissions" : [ // permission data from 'user/permission/list' api.
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
                    "is_permit": 1, // 1 means permission added
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
                    "is_permit": 0, // 0 means permission not added
                    "route_name": "test.b",
                    "method": "GET"
                }
            ]
        }
    ]
  }
  ```

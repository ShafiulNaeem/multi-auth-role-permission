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

- After add auth guard, hit  ``` applicatoin_url/guards ```  url for guard list.
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
  ```

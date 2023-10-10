<?php
use Illuminate\Support\Facades\Route;
use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;

Route::get('/test1', function () {
    return 'ok';
});

/*
 * guard wise route list
*/
Route::get('role/permission/module/{guard}', [RolePermissionController::class, 'module_permission'])->name('module.route.list');

/*
* role route
*/
Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('/list', [RolePermissionController::class, 'index'])->name('list');
    Route::post('/create', [RolePermissionController::class, 'store'])->name('create');
    Route::get('/show/{id}', [RolePermissionController::class, 'show'])->name('show');
    Route::put('/update/{id}', [RolePermissionController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RolePermissionController::class, 'destroy'])->name('delete');
});

Route::middleware('check.auth:web')->group(function () {
    Route::middleware('permission:web')->group(function () {
        // test route
        Route::get('/test', function () {
            return 'ok';
        });
    });
});